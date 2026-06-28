<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ClassBooking;
use App\Models\GymClass;
use App\Services\Tenant\ClassService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassController extends Controller
{
    public function __construct(private readonly ClassService $service) {}

    // ── Timetable ─────────────────────────────────────────────────────────────

    public function timetable(Request $request){
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }
        return Inertia::render('Tenant/Classes/Timetable', $this->service->timetable($request->user(), $request));
    }

    // ── Create / Edit ─────────────────────────────────────────────────────────

    public function create(Request $request){
        $data = $this->service->formData($request->user());
        $data['selectedBranchId'] = session('gymos_selected_branch_id');
        return Inertia::render('Tenant/Classes/Form'$data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'min:2', 'max:80'],
            'type'            => ['required', 'in:'.implode(',', GymClass::TYPES)],
            'branch_id'       => ['required', 'integer'],
            'room'            => ['nullable', 'string', 'max:80'],
            'trainer_id'      => ['nullable', 'integer'],
            'start_time'      => ['required', 'date_format:H:i'],
            'end_time'        => ['required', 'date_format:H:i', 'after:start_time'],
            'repeat'          => ['required', 'in:none,daily,weekly'],
            'days_of_week'    => ['required_if:repeat,weekly', 'array'],
            'days_of_week.*'  => ['integer', 'between:1,7'],
            'start_date'      => ['required', 'date', 'after_or_equal:today'],
            'end_date'        => ['required_if:repeat,daily,weekly', 'nullable', 'date', 'after:start_date'],
            'max_capacity'    => ['required', 'integer', 'min:1', 'max:500'],
            'allow_waitlist'  => ['boolean'],
            'visible'         => ['boolean'],
            'description'     => ['nullable', 'string', 'max:500'],
        ]);

        $validated['allow_waitlist'] = $request->boolean('allow_waitlist', true);
        $validated['visible']        = $request->boolean('visible', true);

        $this->service->createClasses($request->user(), $validated);

        return redirect()->route('tenant.classes.timetable')
                         ->with('status', __('classes.flash.created'));
    }

    public function edit(Request $request, GymClass $class){
        return Inertia::render('Tenant/Classes/Form', array_merge(
            $this->service->formData($request->user(), $class),
            ['editing' => true]
        ));
    }

    public function update(Request $request, GymClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'min:2', 'max:80'],
            'type'            => ['required', 'in:'.implode(',', GymClass::TYPES)],
            'room'            => ['nullable', 'string', 'max:80'],
            'trainer_id'      => ['nullable', 'integer'],
            'start_time'      => ['required', 'date_format:H:i'],
            'end_time'        => ['required', 'date_format:H:i', 'after:start_time'],
            'max_capacity'    => ['required', 'integer', 'min:1', 'max:500'],
            'allow_waitlist'  => ['boolean'],
            'visible'         => ['boolean'],
            'description'     => ['nullable', 'string', 'max:500'],
            'scope'           => ['required', 'in:this,future'],
        ]);

        $validated['allow_waitlist'] = $request->boolean('allow_waitlist', true);
        $validated['visible']        = $request->boolean('visible', true);

        $this->service->updateClass($request->user(), $class, $validated, $validated['scope']);

        return redirect()->route('tenant.classes.timetable')
                         ->with('status', __('classes.flash.updated'));
    }

    // ── Cancel ────────────────────────────────────────────────────────────────

    public function cancel(Request $request, GymClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'scope'  => ['required', 'in:this,future'],
        ]);

        $this->service->cancelClass($request->user(), $class, $validated['reason'], $validated['scope']);

        return back()->with('status', __('classes.flash.cancelled'));
    }

    // ── Show class ────────────────────────────────────────────────────────────

    public function show(Request $request, GymClass $class){
        return Inertia::render('Tenant/Classes/Show', $this->service->showClass($request->user(), $class));
    }

    // ── Booking ───────────────────────────────────────────────────────────────

    public function book(Request $request){
        return Inertia::render('Tenant/Classes/Book', $this->service->bookingPage($request->user(), $request));
    }

    public function storeBooking(Request $request, GymClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
        ]);

        $booking = $this->service->book($request->user(), $class, $validated['member_id']);

        $flash = $booking->status === 'waitlisted'
            ? __('classes.flash.waitlisted')
            : __('classes.flash.booked');

        return back()->with('status', $flash);
    }

    public function cancelBooking(Request $request, GymClass $class, ClassBooking $booking): RedirectResponse
    {
        $this->service->cancelBooking($request->user(), $booking);

        return back()->with('status', __('classes.flash.booking_cancelled'));
    }

    // ── Attendance ────────────────────────────────────────────────────────────

    public function attendance(Request $request, GymClass $class){
        $data = $this->service->showClass($request->user(), $class);

        return Inertia::render('Tenant/Classes/Attendance'$data);
    }

    public function storeAttendance(Request $request, GymClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'attendances'          => ['required', 'array'],
            'attendances.*.member_id' => ['required', 'integer'],
            'attendances.*.status'    => ['required', 'in:attended,absent,late_cancel'],
        ]);

        $this->service->markAttendance($request->user(), $class, $validated['attendances']);

        return redirect()->route('tenant.classes.timetable')
                         ->with('status', __('classes.flash.attendance_saved'));
    }

    // ── Trainers ──────────────────────────────────────────────────────────────

    public function trainers(Request $request){
        return Inertia::render('Tenant/Classes/Trainers', $this->service->trainers($request->user(), $request));
    }

    // ── AJAX member search ────────────────────────────────────────────────────

    public function memberSearch(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q'));

        return response()->json($this->service->memberSearch($request->user(), $q));
    }
}
