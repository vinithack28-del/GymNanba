<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationLinkMail;
use App\Models\GymMembershipPlan;
use App\Models\Member;
use App\Models\MemberRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class MemberRegistrationController extends Controller
{
    public function index(Request $request){
        $tenant = $request->user()->tenant;
        $tenant->ensureRegistrationToken();

        $status        = $request->get('status', 'pending');
        $registrations = MemberRegistration::forTenant($tenant->id)
            ->where('status', $status)
            ->with('confirmedBy')
            ->latest()
            ->paginate(20);

        $counts = [
            'pending'   => MemberRegistration::forTenant($tenant->id)->where('status', 'pending')->count(),
            'confirmed' => MemberRegistration::forTenant($tenant->id)->where('status', 'confirmed')->count(),
            'rejected'  => MemberRegistration::forTenant($tenant->id)->where('status', 'rejected')->count(),
        ];

        $plans           = GymMembershipPlan::forTenant($tenant->id)->active()->orderBy('name')->get();
        $registrationUrl = $tenant->registration_url;

        return Inertia::render('Tenant/Members/Registrations', compact(
            'registrations', 'counts', 'status', 'registrationUrl', 'plans', 'tenant'
        ));
    }

    public function confirm(Request $request, MemberRegistration $registration): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($registration->tenant_id === $tenant->id, 403);
        abort_unless($registration->status === 'pending', 422, 'Registration is already processed.');

        $validated = $request->validate([
            'plan_id'    => 'required|exists:gym_membership_plans,id',
            'start_date' => 'required|date',
        ]);

        $plan = GymMembershipPlan::forTenant($tenant->id)->findOrFail($validated['plan_id']);

        // Check for phone uniqueness within the tenant
        if (Member::forTenant($tenant->id)->where('phone', $registration->phone)->exists()) {
            return back()->withErrors(['phone' => "A member with phone {$registration->phone} already exists."]);
        }

        $member = Member::create([
            'tenant_id'   => $tenant->id,
            'name'        => $registration->name,
            'phone'       => $registration->phone,
            'email'       => $registration->email,
            'gender'      => $registration->gender,
            'dob'         => $registration->dob,
            'address'     => $registration->address,
            'member_code' => Member::generateCode($tenant->id),
            'plan_id'     => $plan->id,
            'plan_name'   => $plan->name,
            'start_date'  => $validated['start_date'],
            'expiry_date' => $plan->computeExpiryDate($validated['start_date']),
            'status'      => 'active',
            'balance_paise' => 0,
            'created_by'  => $request->user()->id,
        ]);

        $registration->update([
            'status'       => 'confirmed',
            'member_id'    => $member->id,
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);

        return redirect()
            ->route('tenant.members.registrations.index')
            ->with('status', "{$registration->name} confirmed and added as member {$member->member_code}.");
    }

    public function reject(Request $request, MemberRegistration $registration): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($registration->tenant_id === $tenant->id, 403);
        abort_unless($registration->status === 'pending', 422, 'Registration is already processed.');

        $request->validate(['reason' => 'nullable|string|max:500']);

        $registration->update([
            'status'          => 'rejected',
            'rejected_reason' => $request->input('reason'),
        ]);

        return redirect()
            ->route('tenant.members.registrations.index')
            ->with('status', "Registration from {$registration->name} rejected.");
    }

    public function sendEmail(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        $tenant->ensureRegistrationToken();

        $request->validate(['email' => 'required|email|max:255']);

        Mail::to($request->email)->send(new RegistrationLinkMail(
            gymName:         $tenant->gym_name,
            registrationUrl: $tenant->registration_url,
            city:            $tenant->city ?? '',
        ));

        return back()->with('email_sent', "Registration link sent to {$request->email}.");
    }
}
