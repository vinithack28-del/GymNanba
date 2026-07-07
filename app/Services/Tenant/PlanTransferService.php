<?php

namespace App\Services\Tenant;

use App\Models\Member;
use App\Models\GymMembershipPlan;
use App\Models\MembershipPlanTransfer;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\OwnerAuditLog;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlanTransferService
{
    public function initiateTransfer(array $data, int $tenantId): MembershipPlanTransfer
    {
        $user = Auth::user();
        $sourceMember = Member::where('tenant_id', $tenantId)->findOrFail($data['source_member_id']);
        $targetMember = Member::where('tenant_id', $tenantId)->findOrFail($data['target_member_id']);
        $plan = GymMembershipPlan::where('tenant_id', $tenantId)->findOrFail($sourceMember->plan_id);

        // Validation
        $this->validateTransferEligibility($sourceMember, $targetMember, $plan);

        $transferDate = now()->toDateString();
        $remainingDays = $this->calculateRemainingDays($sourceMember);
        $transferFeeAmount = $plan->has_transfer_fee ? $plan->transfer_fee_amount : 0;

        // If transfer fee is required, create pending payment
        $paymentId = null;
        $invoiceId = null;
        if ($transferFeeAmount > 0) {
            $payment = $this->createTransferPayment($sourceMember, $plan, $transferFeeAmount, $tenantId, $user);
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
            $sourceMember,
            $targetMember,
            $plan,
            $transferDate,
            $remainingDays,
            $transferFeeAmount,
            $paymentId,
            $invoiceId,
            $user
        ) {
            // Create transfer record
            $transfer = MembershipPlanTransfer::create([
                'tenant_id' => $tenantId,
                'source_member_id' => $sourceMember->id,
                'target_member_id' => $targetMember->id,
                'membership_plan_id' => $plan->id,
                'transfer_date' => $transferDate,
                'old_start_date' => $sourceMember->start_date,
                'old_expiry_date' => $sourceMember->expiry_date,
                'new_start_date' => $transferDate,
                'new_expiry_date' => $sourceMember->expiry_date,
                'remaining_days' => $remainingDays,
                'transfer_fee_amount' => $transferFeeAmount,
                'invoice_id' => $invoiceId,
                'payment_id' => $paymentId,
                'status' => $transferFeeAmount > 0 ? 'pending_payment' : 'completed',
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            // If no fee required, complete the transfer immediately
            if ($transferFeeAmount === 0) {
                $this->completeTransfer($transfer);
            }

            // Audit log
            OwnerAuditLog::create([
                'tenant_id' => $tenantId,
                'actor_user_id' => $user->id,
                'action_type' => 'plan_transfer_initiated',
                'target_type' => 'membership_plan_transfer',
                'target_id' => $transfer->id,
                'target_name' => "Plan Transfer: {$sourceMember->name} to {$targetMember->name}",
                'payload' => [
                    'source_member_id' => $sourceMember->id,
                    'source_member_name' => $sourceMember->name,
                    'target_member_id' => $targetMember->id,
                    'target_member_name' => $targetMember->name,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'transfer_fee_amount' => $transferFeeAmount,
                    'status' => $transfer->status,
                ],
            ]);

            return $transfer;
        });
    }

    public function completeTransfer(MembershipPlanTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $sourceMember = $transfer->sourceMember;
            $targetMember = $transfer->targetMember;
            $plan = $transfer->membershipPlan;

            // Update source member status
            $sourceMember->update([
                'status' => 'transferred',
                'plan_id' => null,
                'plan_name' => null,
                'start_date' => null,
                'expiry_date' => null,
            ]);

            // Assign plan to target member
            $targetMember->update([
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'start_date' => $transfer->new_start_date,
                'expiry_date' => $transfer->new_expiry_date,
                'status' => 'active',
            ]);

            // Update transfer status
            $transfer->update([
                'status' => 'completed',
            ]);

            // Audit log
            OwnerAuditLog::create([
                'tenant_id' => $transfer->tenant_id,
                'actor_user_id' => $transfer->created_by,
                'action_type' => 'plan_transfer_completed',
                'target_type' => 'membership_plan_transfer',
                'target_id' => $transfer->id,
                'target_name' => "Plan Transfer Completed: {$sourceMember->name} to {$targetMember->name}",
                'payload' => [
                    'source_member_id' => $sourceMember->id,
                    'target_member_id' => $targetMember->id,
                    'plan_id' => $plan->id,
                ],
            ]);
        });
    }

    public function cancelTransfer(MembershipPlanTransfer $transfer, string $reason): void
    {
        abort_if($transfer->status === 'completed', 422, 'Cannot cancel a completed transfer');

        DB::transaction(function () use ($transfer, $reason) {
            // Void payment if exists
            if ($transfer->payment) {
                $transfer->payment->update([
                    'status' => 'voided',
                    'voided_at' => now(),
                    'void_reason' => $reason,
                ]);
            }

            // Void invoice if exists
            if ($transfer->invoice) {
                $transfer->invoice->update([
                    'status' => 'void',
                    'voided_at' => now(),
                    'void_reason' => $reason,
                ]);
            }

            $transfer->update([
                'status' => 'cancelled',
            ]);

            // Audit log
            OwnerAuditLog::create([
                'tenant_id' => $transfer->tenant_id,
                'actor_user_id' => Auth::id(),
                'action_type' => 'plan_transfer_cancelled',
                'target_type' => 'membership_plan_transfer',
                'target_id' => $transfer->id,
                'target_name' => "Plan Transfer Cancelled",
                'payload' => [
                    'reason' => $reason,
                ],
            ]);
        });
    }

    private function validateTransferEligibility(Member $sourceMember, Member $targetMember, GymMembershipPlan $plan): void
    {
        // Check if plan is transferable
        if (!$plan->is_transferable) {
            throw ValidationException::withMessages([
                'plan_id' => 'This plan is not transferable.',
            ]);
        }

        // Check source member has active plan
        if ($sourceMember->effective_status !== 'active') {
            throw ValidationException::withMessages([
                'source_member_id' => 'Source member must have an active plan.',
            ]);
        }

        // Check source member plan is not expired
        if ($sourceMember->expiry_date && $sourceMember->expiry_date->isPast()) {
            throw ValidationException::withMessages([
                'source_member_id' => 'Source member plan has expired.',
            ]);
        }

        // Check source member is not frozen
        if ($sourceMember->isFrozen()) {
            throw ValidationException::withMessages([
                'source_member_id' => 'Source member plan is frozen.',
            ]);
        }

        // Check source and target are different
        if ($sourceMember->id === $targetMember->id) {
            throw ValidationException::withMessages([
                'target_member_id' => 'Source and target member cannot be the same.',
            ]);
        }

        // Check target member is active
        if ($targetMember->status !== 'active') {
            throw ValidationException::withMessages([
                'target_member_id' => 'Target member must be active.',
            ]);
        }

        // Check target member doesn't already have an active plan
        if ($targetMember->plan_id && $targetMember->effective_status === 'active') {
            throw ValidationException::withMessages([
                'target_member_id' => 'Target member already has an active plan.',
            ]);
        }

        // Check for pending transfers
        $pendingTransfer = MembershipPlanTransfer::where('source_member_id', $sourceMember->id)
            ->where('status', 'pending_payment')
            ->exists();

        if ($pendingTransfer) {
            throw ValidationException::withMessages([
                'source_member_id' => 'Source member has a pending transfer awaiting payment.',
            ]);
        }
    }

    private function calculateRemainingDays(Member $member): int
    {
        if (!$member->expiry_date) {
            return 0;
        }

        return max(0, Carbon::parse($member->expiry_date)->diffInDays(now()));
    }

    private function createTransferPayment(Member $member, GymMembershipPlan $plan, int $feeAmount, int $tenantId, $user): Payment
    {
        $staff = Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();
        $receipt = $this->generateReceiptNumber($tenantId);

        $gstPaise = 0;
        if ($plan->transfer_fee_gst_applicable && $plan->gst_rate > 0) {
            $gstPaise = (int) round($feeAmount * ($plan->gst_rate / 100));
        }

        $totalPaise = $feeAmount + $gstPaise;

        return Payment::create([
            'tenant_id' => $tenantId,
            'member_id' => $member->id,
            'branch_id' => $member->branch_id,
            'plan_id' => $plan->id,
            'receipt_number' => $receipt,
            'amount_paise' => $feeAmount,
            'gst_paise' => $gstPaise,
            'total_paise' => $totalPaise,
            'paid_paise' => 0,
            'is_partial' => true,
            'due_paise' => $totalPaise,
            'due_date' => now()->addDays(7)->toDateString(),
            'method' => 'cash',
            'reference' => null,
            'payment_date' => now()->toDateString(),
            'notes' => 'Transfer fee for plan transfer',
            'status' => 'active',
            'collected_by' => $staff?->id,
        ]);
    }

    private function generateReceiptNumber(int $tenantId): string
    {
        $max = Payment::where('tenant_id', $tenantId)->max('id') ?? 0;
        return 'TRF-' . str_pad($max + 1, 5, '0', STR_PAD_LEFT);
    }
}
