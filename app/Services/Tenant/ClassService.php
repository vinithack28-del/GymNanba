<?php

namespace App\Services\Tenant;

use App\Models\Branch;
use App\Models\ClassBooking;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\Staff;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ClassService
{
    // â”€â”€ Timetable â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function timetable(object $user, Request $request): array
    {
        $weekDate = $request->get('week')
            ? Carbon::parse($request->get('week'))
            : Carbon::now();

        $weekStart = $weekDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = $weekDate->copy()->endOfWeek(Carbon::SUNDAY);
        $branchId  = $this->resolveBranch($user, $request->get('branch_id'));

        $query = GymClass::query()
            ->forTenant($user->tenant_id)
            ->forWeek($weekStart->toDateString(), $weekEnd->toDateString())
            ->with(['trainer', 'branch', 'bookings'])
            ->orderBy('start_time');

        if ($branchId) {
            $query->forBranch($branchId);
        }

        $classes = $query->get();

        // Group by ISO day of week (1=Mon â€¦ 7=Sun)
        $byDay = collect(range(1, 7))->mapWithKeys(fn ($d) => [$d => collect()]);
        foreach ($classes as $class) {
            $dow = $class->class_date->isoWeekday(); // 1â€“7
            $byDay[$dow]->push($class);
        }

        return [
            'byDay'       => $byDay,
            'weekStart'   => $weekStart,
            'weekEnd'     => $weekEnd,
            'prevWeek'    => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeek'    => $weekStart->copy()->addWeek()->toDateString(),
            'branches'    => Branch::forTenant($user->tenant_id)->active()->orderBy('name')->get(),
            'branchId'    => $branchId,
            'canManage'   => $this->canManage($user),
            'view'        => $request->get('view', 'calendar'),
        ];
    }

    // â”€â”€ Create / Update â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function createClasses(object $user, array $validated): array
    {
        $dates = $this->buildDates($validated);
        $ids   = [];

        $parentId = null;
        foreach ($dates as $i => $date) {
            $class = GymClass::query()->create([
                'tenant_id'    => $user->tenant_id,
                'branch_id'    => $validated['branch_id'],
                'name'         => $validated['name'],
                'type'         => $validated['type'],
                'room'         => $validated['room'] ?? null,
                'trainer_id'   => $validated['trainer_id'] ?? null,
                'start_time'   => $validated['start_time'],
                'end_time'     => $validated['end_time'],
                'class_date'   => $date,
                'max_capacity' => $validated['max_capacity'],
                'allow_waitlist'=> $validated['allow_waitlist'] ?? true,
                'visible'      => $validated['visible'] ?? true,
                'description'  => $validated['description'] ?? null,
                'parent_id'    => $parentId,
                'status'       => 'scheduled',
            ]);

            if ($i === 0 && count($dates) > 1) {
                $parentId = $class->id;
            }

            $ids[] = $class->id;
        }

        return $ids;
    }

    public function updateClass(object $user, GymClass $class, array $validated, string $scope): int
    {
        $fields = collect($validated)->only([
            'name','type','room','trainer_id','start_time','end_time',
            'max_capacity','allow_waitlist','visible','description',
        ])->toArray();

        $query = $this->scopeQuery($class, $scope, $user);

        // Cannot reduce capacity below current booking count for each class
        // (simplified: update each row, skip if new capacity < booked count)
        $updated = 0;
        foreach ($query->get() as $c) {
            $booked = $c->bookings()->whereIn('status', ['booked','attended','absent'])->count();
            if (isset($fields['max_capacity']) && $fields['max_capacity'] < $booked) {
                $fields['max_capacity'] = $booked; // clamp to minimum
            }
            $c->update($fields);
            $updated++;
        }

        return $updated;
    }

    public function cancelClass(object $user, GymClass $class, string $reason, string $scope): int
    {
        $query   = $this->scopeQuery($class, $scope, $user);
        $classes = $query->where('status', 'scheduled')->get();

        foreach ($classes as $c) {
            $c->update(['status' => 'cancelled', 'cancel_reason' => $reason]);
            // Cancel all active bookings for this class
            $c->bookings()->whereIn('status', ['booked','waitlisted'])->update(['status' => 'cancelled']);
        }

        return $classes->count();
    }

    // â”€â”€ Show class â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function showClass(object $user, GymClass $class): array
    {
        abort_unless($class->tenant_id === $user->tenant_id, 403);

        $class->load(['trainer', 'branch', 'bookings.member']);

        $booked    = $class->bookings->whereIn('status', ['booked','attended','absent'])->values();
        $waitlisted = $class->bookings->where('status', 'waitlisted')->sortBy('waitlist_pos')->values();

        return [
            'class'      => $class,
            'booked'     => $booked,
            'waitlisted' => $waitlisted,
            'canManage'  => $this->canManage($user),
        ];
    }

    // â”€â”€ Booking â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function book(object $user, GymClass $class, int $memberId): ClassBooking
    {
        abort_unless($class->tenant_id === $user->tenant_id, 403);
        abort_if($class->status !== 'scheduled', 422, 'Class is not available for booking.');

        $member = Member::query()->forTenant($user->tenant_id)->findOrFail($memberId);

        $existing = ClassBooking::query()
            ->where('class_id', $class->id)
            ->where('member_id', $member->id)
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existing) {
            abort(409, 'Member already has an active booking for this class.');
        }

        $class->load('bookings');
        $booked = $class->booking_count;

        if ($booked < $class->max_capacity) {
            $status      = 'booked';
            $waitlistPos = null;
        } elseif ($class->allow_waitlist) {
            $status      = 'waitlisted';
            $waitlistPos = $class->waitlist_count + 1;
        } else {
            abort(422, 'Class is full and does not allow waitlisting.');
        }

        return ClassBooking::query()->create([
            'class_id'    => $class->id,
            'member_id'   => $member->id,
            'tenant_id'   => $user->tenant_id,
            'status'      => $status,
            'waitlist_pos'=> $waitlistPos,
            'booked_by'   => null,
        ]);
    }

    public function cancelBooking(object $user, ClassBooking $booking): void
    {
        abort_unless($booking->tenant_id === $user->tenant_id, 403);

        $wasBooked = $booking->status === 'booked';
        $booking->update(['status' => 'cancelled']);

        if ($wasBooked) {
            $this->promoteFirstWaitlisted($user, $booking->class_id);
        }
    }

    // â”€â”€ Attendance marking â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function markAttendance(object $user, GymClass $class, array $attendances): void
    {
        abort_unless($class->tenant_id === $user->tenant_id, 403);

        foreach ($attendances as $row) {
            ClassBooking::query()
                ->where('class_id', $class->id)
                ->where('member_id', $row['member_id'])
                ->whereIn('status', ['booked','attended','absent'])
                ->update(['status' => $row['status']]);
        }

        if ($class->status === 'scheduled') {
            $class->update(['status' => 'completed']);
        }
    }

    // â”€â”€ Booking page â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function bookingPage(object $user, Request $request): array
    {
        $branchId = $this->resolveBranch($user, $request->get('branch_id'));

        $query = GymClass::query()
            ->forTenant($user->tenant_id)
            ->upcoming()
            ->with(['trainer', 'branch', 'bookings'])
            ->where('visible', true);

        if ($branchId) {
            $query->forBranch($branchId);
        }

        return [
            'classes'   => $query->paginate(20)->withQueryString(),
            'branches'  => Branch::forTenant($user->tenant_id)->active()->orderBy('name')->get(),
            'branchId'  => $branchId,
            'canManage' => $this->canManage($user),
        ];
    }

    public function memberSearch(object $user, string $q): Collection
    {
        if (strlen($q) < 2) {
            return collect();
        }

        return Member::query()
            ->forTenant($user->tenant_id)
            ->where('status', '!=', 'inactive')
            ->where(fn ($query) => $query
                ->where('name', 'ilike', "%{$q}%")
                ->orWhere('phone', 'ilike', "%{$q}%")
                ->orWhere('member_code', 'ilike', "%{$q}%")
            )
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'member_code', 'plan_name', 'status']);
    }

    // â”€â”€ Trainers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function trainers(object $user, Request $request): array
    {
        $branchId = $this->resolveBranch($user, $request->get('branch_id'));

        $query = Staff::query()
            ->forTenant($user->tenant_id)
            ->where('role', 'trainer')
            ->orderBy('name');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $trainers = $query->get();

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd   = Carbon::now()->endOfWeek(Carbon::SUNDAY)->toDateString();

        $classCounts = GymClass::query()
            ->forTenant($user->tenant_id)
            ->forWeek($weekStart, $weekEnd)
            ->whereNotNull('trainer_id')
            ->where('status', 'scheduled')
            ->selectRaw('trainer_id, COUNT(*) as total')
            ->groupBy('trainer_id')
            ->pluck('total', 'trainer_id');

        return [
            'trainers'   => $trainers,
            'classCounts'=> $classCounts,
            'branches'   => Branch::forTenant($user->tenant_id)->active()->orderBy('name')->get(),
            'branchId'   => $branchId,
            'canManage'  => $this->canManage($user),
        ];
    }

    // â”€â”€ Form data â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function formData(object $user, ?GymClass $class = null): array
    {
        return [
            'class'    => $class,
            'branches' => Branch::forTenant($user->tenant_id)->active()->orderBy('name')->get(),
            'trainers' => Staff::query()->forTenant($user->tenant_id)->where('role', 'trainer')->where('status', 'active')->orderBy('name')->get(),
            'types'    => GymClass::TYPES,
        ];
    }

    // â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function buildDates(array $validated): array
    {
        $repeat = $validated['repeat'] ?? 'none';
        $start  = Carbon::parse($validated['start_date']);

        if ($repeat === 'none') {
            return [$start->toDateString()];
        }

        $end = Carbon::parse($validated['end_date'] ?? $start->copy()->addMonths(3));

        if ($repeat === 'daily') {
            return array_map(
                fn ($d) => $d->toDateString(),
                CarbonPeriod::create($start, $end)->toArray()
            );
        }

        // Weekly on selected days
        $days = array_map('intval', $validated['days_of_week'] ?? []);
        $dates = [];
        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            if (in_array($date->isoWeekday(), $days, true)) {
                $dates[] = $date->toDateString();
            }
        }

        return $dates ?: [$start->toDateString()];
    }

    private function scopeQuery(GymClass $class, string $scope, object $user)
    {
        $rootId = $class->parent_id ?? $class->id;

        if ($scope === 'future') {
            return GymClass::query()
                ->forTenant($user->tenant_id)
                ->where(fn ($q) => $q
                    ->where('id', $class->id)
                    ->orWhere(fn ($q2) => $q2
                        ->where('parent_id', $rootId)
                        ->where('class_date', '>=', $class->class_date->toDateString())
                    )
                );
        }

        // 'this' â€” single occurrence
        return GymClass::query()->where('id', $class->id);
    }

    private function promoteFirstWaitlisted(object $user, int $classId): void
    {
        $first = ClassBooking::query()
            ->where('class_id', $classId)
            ->where('status', 'waitlisted')
            ->orderBy('waitlist_pos')
            ->first();

        if ($first) {
            $first->update(['status' => 'booked', 'waitlist_pos' => null]);
        }
    }

    public function canManage(object $user): bool
    {
        return in_array($user->role, ['tenant_owner', 'branch_manager'], true);
    }

    private function resolveBranch(object $user, mixed $branchId): ?int
    {
        if (in_array($user->role, ['branch_manager', 'branch_admin'], true)) {
            return (int) $user->branch_id;
        }

        return filled($branchId) ? (int) $branchId : null;
    }
}

