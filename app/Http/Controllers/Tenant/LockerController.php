<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Locker;
use App\Models\LockerAssignment;
use App\Models\Member;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LockerController extends Controller
{
    public function index(Request $request){
        abort_unless($request->user()->canAccess('locker.view|locker.assign|locker.add|locker.edit|locker.delete'), 403);

        $tenantId = $request->user()->tenant->id;
        $branchId = $request->user()->effectiveBranchId();
        $branches = Branch::forTenant($tenantId)->active()->orderByRaw('is_primary DESC, name ASC')->get();

        $query = Locker::query()
            ->with(['branch', 'currentAssignment.member'])
            ->forTenant($tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $search = trim((string) $request->get('search', ''));
        $availability = (string) $request->get('availability', '');
        $status = (string) $request->get('status', '');

        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term): void {
                $q->where('locker_number', 'ilike', $term)
                    ->orWhere('location', 'ilike', $term)
                    ->orWhereHas('currentAssignment.member', function ($memberQuery) use ($term): void {
                        $memberQuery->where('name', 'ilike', $term)
                            ->orWhere('phone', 'ilike', $term)
                            ->orWhere('member_code', 'ilike', $term);
                    });
            });
        }

        if ($availability !== '') {
            $query->where('availability', $availability);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->get('per_page')
            : 25;

        $lockers = $query->orderByRaw('LOWER(locker_number) asc')->paginate($perPage)->withQueryString();

        $summaryBase = Locker::query()
            ->forTenant($tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $summary = [
            'total' => (clone $summaryBase)->count(),
            'available' => (clone $summaryBase)->where('availability', 'available')->where('status', 'active')->count(),
            'occupied' => (clone $summaryBase)->where('availability', 'occupied')->count(),
            'inactive' => (clone $summaryBase)->where('status', 'inactive')->count(),
        ];

        return Inertia::render('Tenant/Lockers/Index', [
            'lockers' => $lockers,
            'summary' => $summary,
            'canAdd' => $request->user()->canAccess('locker.add'),
            'canAssign' => $request->user()->canAccess('locker.assign'),
            'canEdit' => $request->user()->canAccess('locker.edit'),
            'canDelete' => $request->user()->canAccess('locker.delete'),
            'branches' => $branches,
            'selectedBranchId' => $branchId,
            'filters' => [
                'search' => $search,
                'availability' => $availability,
                'status' => $status,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show(Request $request, Locker $locker){
        $this->authorizeLocker($request, $locker);
        abort_unless($request->user()->canAccess('locker.view|locker.assign|locker.edit|locker.delete'), 403);

        $locker->load([
            'branch',
            'currentAssignment.member',
            'assignments.member',
        ]);

        return Inertia::render('Tenant/Lockers/Assignment', [
            'locker' => $locker,
            'lockerData' => $this->lockerPayload($request, $locker),
            'canAssign' => $request->user()->canAccess('locker.assign'),
            'canEdit' => $request->user()->canAccess('locker.edit'),
            'canDelete' => $request->user()->canAccess('locker.delete'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canAccess('locker.add'), 403);

        $validated = $this->validateLocker($request);
        $tenantId = $request->user()->tenant->id;
        $staffId = $this->staffIdFor($request);
        $branchId = $validated['branch_id'] ?? $request->user()->effectiveBranchId();

        if (! $branchId) {
            throw ValidationException::withMessages([
                'branch_id' => 'Please choose a branch before adding a locker.',
            ]);
        }

        Locker::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'locker_number' => $validated['locker_number'],
            'location' => $validated['location'] ?? null,
            'availability' => 'available',
            'status' => $validated['status'] ?? 'active',
            'notes' => $validated['notes'] ?? null,
            'created_by' => $staffId,
        ]);

        return redirect()->route('tenant.lockers.index')->with('status', 'Locker added successfully.');
    }

    public function details(Request $request, Locker $locker): JsonResponse
    {
        $this->authorizeLocker($request, $locker);
        abort_unless($request->user()->canAccess('locker.view|locker.assign|locker.edit|locker.delete'), 403);

        $locker->load([
            'branch',
            'currentAssignment.member',
            'assignments.member',
        ]);

        return response()->json($this->lockerPayload($request, $locker));
    }

    public function update(Request $request, Locker $locker): JsonResponse
    {
        $this->authorizeLocker($request, $locker);
        abort_unless($request->user()->canAccess('locker.edit'), 403);

        $validated = $this->validateLocker($request, $locker);

        if (($validated['status'] ?? $locker->status) === 'inactive' && $locker->currentAssignment()->exists()) {
            $memberName = $locker->currentAssignment?->member?->name ?? 'this member';
            throw ValidationException::withMessages([
                'status' => "This locker is currently assigned to {$memberName}. Release the locker before marking it inactive.",
            ]);
        }

        $locker->update([
            'locker_number' => $validated['locker_number'],
            'location' => $validated['location'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $locker->refresh()->load(['branch', 'currentAssignment.member', 'assignments.member']);

        return response()->json($this->lockerPayload($request, $locker));
    }

    public function destroy(Request $request, Locker $locker): JsonResponse
    {
        $this->authorizeLocker($request, $locker);
        abort_unless($request->user()->canAccess('locker.delete'), 403);

        if ($locker->currentAssignment()->exists()) {
            throw ValidationException::withMessages([
                'locker' => 'Release this locker before deleting.',
            ]);
        }

        $locker->delete();

        return response()->json(['deleted' => true]);
    }

    public function assign(Request $request, Locker $locker): JsonResponse
    {
        $this->authorizeLocker($request, $locker);
        abort_unless($request->user()->canAccess('locker.assign'), 403);

        if ($locker->status !== 'active') {
            throw ValidationException::withMessages([
                'locker' => 'Inactive lockers cannot be assigned.',
            ]);
        }

        if ($locker->currentAssignment()->exists() || $locker->availability !== 'available') {
            throw ValidationException::withMessages([
                'locker' => 'This locker is not available right now.',
            ]);
        }

        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'from_date' => ['required', 'date', 'after_or_equal:today'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $member = $this->eligibleMembersQuery($request->user())->findOrFail($validated['member_id']);
        $existingAssignment = LockerAssignment::query()
            ->active()
            ->where('member_id', $member->id)
            ->with('locker')
            ->first();

        if ($existingAssignment) {
            throw ValidationException::withMessages([
                'member_id' => "This member already has locker {$existingAssignment->locker->locker_number} assigned. Reassign that locker first or choose a different member.",
            ]);
        }

        LockerAssignment::create([
            'locker_id' => $locker->id,
            'member_id' => $member->id,
            'tenant_id' => $request->user()->tenant->id,
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'assigned_by' => $this->staffIdFor($request),
        ]);

        $locker->update(['availability' => 'occupied']);
        $locker->refresh()->load(['branch', 'currentAssignment.member', 'assignments.member']);

        return response()->json($this->lockerPayload($request, $locker), 201);
    }

    public function reassign(Request $request, Locker $locker): JsonResponse
    {
        $this->authorizeLocker($request, $locker);
        abort_unless($request->user()->canAccess('locker.assign'), 403);

        $currentAssignment = $locker->currentAssignment()->with('member')->first();
        if (! $currentAssignment) {
            throw ValidationException::withMessages([
                'locker' => 'This locker is not occupied right now.',
            ]);
        }

        $validated = $request->validate([
            'new_member_id' => ['required', 'integer'],
            'from_date' => ['required', 'date', 'after_or_equal:today'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ((int) $validated['new_member_id'] === (int) $currentAssignment->member_id) {
            throw ValidationException::withMessages([
                'new_member_id' => 'Cannot reassign to the same member.',
            ]);
        }

        $member = $this->eligibleMembersQuery($request->user())->findOrFail($validated['new_member_id']);
        $existingAssignment = LockerAssignment::query()
            ->active()
            ->where('member_id', $member->id)
            ->with('locker')
            ->first();

        if ($existingAssignment) {
            throw ValidationException::withMessages([
                'new_member_id' => "This member already has locker {$existingAssignment->locker->locker_number} assigned. Reassign that locker first or choose a different member.",
            ]);
        }

        $today = Carbon::today();
        $currentAssignment->update([
            'to_date' => Carbon::parse($validated['from_date'])->toDateString(),
            'released_by' => $this->staffIdFor($request),
            'released_at' => $today->toDateTimeString(),
        ]);

        LockerAssignment::create([
            'locker_id' => $locker->id,
            'member_id' => $member->id,
            'tenant_id' => $request->user()->tenant->id,
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'assigned_by' => $this->staffIdFor($request),
        ]);

        $locker->update(['availability' => 'occupied']);
        $locker->refresh()->load(['branch', 'currentAssignment.member', 'assignments.member']);

        return response()->json($this->lockerPayload($request, $locker));
    }

    public function release(Request $request, Locker $locker): JsonResponse
    {
        $this->authorizeLocker($request, $locker);
        abort_unless($request->user()->canAccess('locker.assign'), 403);

        $currentAssignment = $locker->currentAssignment()->first();
        if (! $currentAssignment) {
            throw ValidationException::withMessages([
                'locker' => 'This locker is already available.',
            ]);
        }

        $currentAssignment->update([
            'to_date' => today()->toDateString(),
            'released_by' => $this->staffIdFor($request),
            'released_at' => now(),
        ]);

        $locker->update(['availability' => 'available']);
        $locker->refresh()->load(['branch', 'currentAssignment.member', 'assignments.member']);

        return response()->json([
            'released' => true,
            'locker' => $this->lockerPayload($request, $locker),
        ]);
    }

    public function memberSearch(Request $request): JsonResponse
    {
        abort_unless($request->user()->canAccess('locker.assign|locker.view'), 403);

        $term = trim((string) $request->input('q', ''));
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $digits = preg_replace('/\D+/', '', $term);

        $members = $this->eligibleMembersQuery($request->user())
            ->where(function ($q) use ($term, $digits): void {
                $like = '%' . $term . '%';
                $q->where('name', 'ilike', $like)
                    ->orWhere('member_code', 'ilike', $like);

                if ($digits !== '') {
                    $q->orWhere('phone', 'ilike', '%' . $digits . '%');
                }
            })
            ->limit(20)
            ->get(['id', 'name', 'phone', 'member_code']);

        return response()->json(
            $members->map(fn (Member $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone,
                'member_code' => $member->member_code,
            ])->values()
        );
    }

    private function authorizeLocker(Request $request, Locker $locker): void
    {
        abort_unless($locker->tenant_id === $request->user()->tenant->id, 404);

        if ($branchId = $request->user()->effectiveBranchId()) {
            abort_unless((int) $locker->branch_id === (int) $branchId, 403);
        }
    }

    private function eligibleMembersQuery($user)
    {
        return Member::query()
            ->forTenant($user->tenant_id)
            ->withStatus('active')
            ->when($user->effectiveBranchId(), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->orderBy('name');
    }

    private function validateLocker(Request $request, ?Locker $locker = null): array
    {
        $tenantId = $request->user()->tenant->id;
        $branchId = $request->user()->effectiveBranchId();

        return $request->validate([
            'branch_id' => [
                Rule::requiredIf(! $branchId),
                'nullable',
                'integer',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)->where('status', 'active')),
            ],
            'locker_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('lockers', 'locker_number')
                    ->ignore($locker?->id)
                    ->where(function ($q) use ($tenantId, $request, $branchId, $locker) {
                        $resolvedBranchId = $branchId ?: $request->input('branch_id') ?: $locker?->branch_id;
                        return $q->where('tenant_id', $tenantId)->where('branch_id', $resolvedBranchId);
                    }),
            ],
            'location' => ['nullable', 'string', 'max:200'],
            'status' => [$locker ? 'required' : 'nullable', Rule::in(array_keys(Locker::STATUSES))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'locker_number.required' => 'Please enter a locker number',
            'locker_number.unique' => 'Locker number already exists. Please use a different number.',
        ]);
    }

    private function staffIdFor(Request $request): ?int
    {
        return Staff::query()
            ->where('tenant_id', $request->user()->tenant->id)
            ->where('user_id', $request->user()->id)
            ->value('id');
    }

    private function lockerPayload(Request $request, Locker $locker): array
    {
        $currentAssignment = $locker->currentAssignment;

        return [
            'id' => $locker->id,
            'locker_number' => $locker->locker_number,
            'location' => $locker->location,
            'availability' => $locker->availability,
            'availability_label' => Locker::AVAILABILITIES[$locker->availability] ?? ucfirst($locker->availability),
            'status' => $locker->status,
            'status_label' => Locker::STATUSES[$locker->status] ?? ucfirst($locker->status),
            'notes' => $locker->notes,
            'branch_name' => $locker->branch?->name,
            'created_at' => $locker->created_at?->toDateString(),
            'current_assignment' => $currentAssignment ? [
                'id' => $currentAssignment->id,
                'member_id' => $currentAssignment->member_id,
                'member_name' => $currentAssignment->member?->name,
                'member_code' => $currentAssignment->member?->member_code,
                'member_phone' => $currentAssignment->member?->phone,
                'member_url' => $currentAssignment->member ? route('tenant.members.show', $currentAssignment->member) : null,
                'from_date' => $currentAssignment->from_date?->format('d-m-Y'),
                'to_date' => $currentAssignment->to_date?->format('d-m-Y'),
                'days_so_far' => $this->daysHeld($currentAssignment),
                'notes' => $currentAssignment->notes,
            ] : null,
            'history' => $locker->assignments->map(function (LockerAssignment $assignment) {
                return [
                    'member_name' => $assignment->member?->name,
                    'member_code' => $assignment->member?->member_code,
                    'member_url' => $assignment->member ? route('tenant.members.show', $assignment->member) : null,
                    'from_date' => $assignment->from_date?->format('d-m-Y'),
                    'to_date' => $assignment->released_at
                        ? $assignment->to_date?->format('d-m-Y')
                        : ($assignment->to_date?->format('d-m-Y') ?? 'â€”'),
                    'days' => $this->daysHeld($assignment),
                    'is_current' => $assignment->released_at === null,
                ];
            })->values()->all(),
            'permissions' => [
                'assign' => $request->user()->canAccess('locker.assign'),
                'edit' => $request->user()->canAccess('locker.edit'),
                'delete' => $request->user()->canAccess('locker.delete'),
            ],
        ];
    }

    private function daysHeld(LockerAssignment $assignment): int
    {
        $from = $assignment->from_date?->copy()->startOfDay();
        if (! $from) {
            return 0;
        }

        $end = $assignment->released_at
            ? $assignment->released_at->copy()->startOfDay()
            : now()->startOfDay();

        return $from->diffInDays($end) + 1;
    }
}

