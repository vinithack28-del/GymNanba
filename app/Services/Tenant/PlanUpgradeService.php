<?php

namespace App\Services\Tenant;

use App\Models\Member;
use App\Models\GymMembershipPlan;
use App\Models\MembershipPlanUpgrade;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\OwnerAuditLog;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlanUpgradeService
{
    public function initiateUpgrade(array $data, int $tenantId): MembershipPlanUpgrade
    {
        $user = Auth::user();
        $member = Member::where('tenant_id', $tenantId)->findOrFail($data['member_id']);
        $oldPlan = GymMembershipPlan::where('tenant_id', $tenantId)->findOrFail($member->plan_id);
        $newPlan = GymMembershipPlan::where('tenant_id', $tenantId)->findOrFail($data['new_plan_id']);

        // Validation
        $this->validateUpgradeEligibility($member, $oldPlan, $newPlan);

        $upgradeDate = now()->toDateString();
        $upgradeAmount = $this->calculateUpgradeAmount($oldPlan, $newPlan, $data['upgrade_charge_type'] ?? $newPlan->upgrade_charge_type);

        // If upgrade charge is required, create pending payment
        $paymentId = null;
        $invoiceId = null;
        if ($upgradeAmount > 0) {
            $payment = $this->createUpgradePayment($member, $oldPlan, $newPlan, $upgradeAmount, $tenantId, $user);
            $paymentId = $payment->id;

            // Create invoice
            $tenant = \App\Models\Tenant::find($tenantId);
            if ($tenant) {
                $invoice = (new \App\Services\Tenant\InvoiceService())->createFromPayment($payment, $tenant);
                $invoiceId = $invoice->id;
            }
        }

        return DB::transaction(function () use (
            $tenantId,
            $member,
            $oldPlan,
            $newPlan,
            $upgradeDate,
            $upgradeAmount,
            $paymentId,
            $invoiceId,
            $user,
            $data
        ) {
            // Create upgrade record
            $upgrade = MembershipPlanUpgrade::create([
                'tenant_id' => $tenantId,
                'member_id' => $member->id,
                'old_member_plan_id' => $oldPlan->id,
                'new_member_plan_id' => $newPlan->id,
                'upgrade_date' => $upgradeDate,
                'old_plan_price_paise' => $oldPlan->price_paise,
                'new_plan_price_paise' => $newPlan->price_paise,
                'upgrade_amount_paise' => $upgradeAmount,
                'invoice_id' => $invoiceId,
                'payment_id' => $paymentId,
                'status' => $upgradeAmount > 0 ? 'pending_payment' : 'completed',
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            // If no charge required, complete the upgrade immediately
            if ($upgradeAmount === 0) {
                $this->completeUpgrade($upgrade);
            }

            // Audit log
            OwnerAuditLog::create([
                'tenant_id' => $tenantId,
                'actor_user_id' => $user->id,
                'action_type' => 'plan_upgrade_initiated',
                'target_type' => 'membership_plan_upgrade',
                'target_id' => $upgrade->id,
                'target_name' => "Plan Upgrade: {$member->name} from {$oldPlan->name} to {$newPlan->name}",
                'payload' => [
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'old_plan_id' => $oldPlan->id,
                    'old_plan_name' => $oldPlan->name,
                    'new_plan_id' => $newPlan->id,
                    'new_plan_name' => $newPlan->name,
                    'upgrade_amount_paise' => $upgradeAmount,
                    'status' => $upgrade->status,
                ],
            ]);

            return $upgrade;
        });
    }

    public function completeUpgrade(MembershipPlanUpgrade $upgrade): void
    {
        DB::transaction(function () use ($upgrade) {
            $member = $upgrade->member;
            $newPlan = $upgrade->newPlan;

            // Update member with new plan
            $member->update([
                'plan_id' => $newPlan->id,
                'plan_name' => $newPlan->name,
                'start_date' => $upgrade->upgrade_date,
                'expiry_date' => $newPlan->computeExpiryDate($upgrade->upgrade_date),
                'status' => 'active',
            ]);

            // Update upgrade status
            $upgrade->update([
                'status' => 'completed',
            ]);

            // Audit log
            OwnerAuditLog::create([
                'tenant_id' => $upgrade->tenant_id,
                'actor_user_id' => $upgrade->created_by,
                'action_type' => 'plan_upgrade_completed',
                'target_type' => 'membership_plan_upgrade',
                'target_id' => $upgrade->id,
                'target_name' => "Plan Upgrade Completed",
                'payload' => [
                    'member_id' => $member->id,
                    'new_plan_id' => $newPlan->id,
                ],
            ]);
        });
    }

    public function cancelUpgrade(MembershipPlanUpgrade $upgrade, string $reason): void
    {
        abort_if($upgrade->status === 'completed', 422, 'Cannot cancel a completed upgrade');

        DB::transaction(function () use ($upgrade, $reason) {
            // Void payment if exists
            if ($upgrade->payment) {
                $upgrade->payment->update([
                    'status' => 'voided',
                    'voided_at' => now(),
                    'void_reason' => $reason,
                ]);
            }

            // Void invoice if exists
            if ($upgrade->invoice) {
                $upgrade->invoice->update([
                    'status' => 'void',
                    'voided_at' => now(),
                    'void_reason' => $reason,
                ]);
            }

            $upgrade->update([
                'status' => 'cancelled',
            ]);

            // Audit log
            OwnerAuditLog::create([
                'tenant_id' => $upgrade->tenant_id,
                'actor_user_id' => Auth::id(),
                'action_type' => 'plan_upgrade_cancelled',
                'target_type' => 'membership_plan_upgrade',
                'target_id' => $upgrade->id,
                'target_name' => "Plan Upgrade Cancelled",
                'payload' => [
                    'reason' => $reason,
                ],
            ]);
        });
    }

    private function validateUpgradeEligibility(Member $member, GymMembershipPlan $oldPlan, GymMembershipPlan $newPlan): void
    {
        // Check if old plan is upgradable
        if (!$oldPlan->is_upgradable) {
            throw ValidationException::withMessages([
                'member_id' => 'Current plan is not upgradable.',
            ]);
        }

        // Check member has active plan
        if ($member->effective_status !== 'active') {
            throw ValidationException::withMessages([
                'member_id' => 'Member must have an active plan.',
            ]);
        }

        // Check member plan is not expired
        if ($member->expiry_date && $member->expiry_date->isPast()) {
            throw ValidationException::withMessages([
                'member_id' => 'Member plan has expired.',
            ]);
        }

        // Check old and new plans are different
        if ($oldPlan->id === $newPlan->id) {
            throw ValidationException::withMessages([
                'new_plan_id' => 'New plan must be different from current plan.',
            ]);
        }

        // Check new plan is active
        if ($newPlan->status !== 'active') {
            throw ValidationException::withMessages([
                'new_plan_id' => 'New plan must be active.',
            ]);
        }

        // Check for pending upgrades
        $pendingUpgrade = MembershipPlanUpgrade::where('member_id', $member->id)
            ->where('status', 'pending_payment')
            ->exists();

        if ($pendingUpgrade) {
            throw ValidationException::withMessages([
                'member_id' => 'Member has a pending upgrade awaiting payment.',
            ]);
        }
    }

    private function calculateUpgradeAmount(GymMembershipPlan $oldPlan, GymMembershipPlan $newPlan, ?string $chargeType = null): int
    {
        $chargeType = $chargeType ?? $newPlan->upgrade_charge_type;

        return match ($chargeType) {
            'full_new_plan' => $newPlan->total_price_paise,
            'difference_amount' => max(0, $newPlan->total_price_paise - $oldPlan->total_price_paise),
            'custom_amount' => $newPlan->upgrade_custom_amount ?? 0,
            default => 0,
        };
    }

    private function createUpgradePayment(Member $member, GymMembershipPlan $oldPlan, GymMembershipPlan $newPlan, int $amount, int $tenantId, $user): Payment
    {
        $staff = Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();
        $receipt = $this->generateReceiptNumber($tenantId);

        $gstPaise = 0;
        if ($newPlan->gst_applicable && $newPlan->gst_rate > 0) {
            $gstPaise = (int) round($amount * ($newPlan->gst_rate / 100));
        }

        $totalPaise = $amount + $gstPaise;

        return Payment::create([
            'tenant_id' => $tenantId,
            'member_id' => $member->id,
            'branch_id' => $member->branch_id,
            'plan_id' => $newPlan->id,
            'receipt_number' => $receipt,
            'amount_paise' => $amount,
            'gst_paise' => $gstPaise,
            'total_paise' => $totalPaise,
            'paid_paise' => 0,
            'is_partial' => true,
            'due_paise' => $totalPaise,
            'due_date' => now()->addDays(7)->toDateString(),
            'method' => 'cash',
            'reference' => null,
            'payment_date' => now()->toDateString(),
            'notes' => "Upgrade fee: {$oldPlan->name} to {$newPlan->name}",
            'status' => 'active',
            'collected_by' => $staff?->id,
        ]);
    }

    private function generateReceiptNumber(int $tenantId): string
    {
        $max = Payment::where('tenant_id', $tenantId)->max('id') ?? 0;
        return 'UPG-' . str_pad($max + 1, 5, '0', STR_PAD_LEFT);
    }
}
