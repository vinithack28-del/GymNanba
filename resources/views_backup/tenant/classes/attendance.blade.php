<x-layouts.admin
    title="{{ __('classes.attendance.title') }}"
    eyebrow="{{ $class->class_date->format('d M Y') }}"
    heading="{{ $class->name }} — {{ __('classes.attendance.title') }}"
    subheading="{{ __('classes.attendance.subtitle') }}"
>

<style>
.att-card { border:1px solid var(--app-border); border-radius:1.5rem; overflow:hidden; }
.att-head  { padding:1rem 1.5rem; border-bottom:1px solid var(--app-border); background:var(--app-panel-strong); font-size:.9rem; font-weight:700; }
.att-warning { margin-bottom:1rem; padding:.75rem 1rem; border:1px solid #f59e0b; border-radius:.75rem; background:#fef3c7; color:#92400e; font-size:.85rem; display:flex; align-items:center; gap:.5rem; }

.att-member-row { display:flex; align-items:center; gap:.75rem; padding:.875rem 1.25rem; border-bottom:1px solid var(--app-border); }
.att-member-row:last-child { border-bottom:none; }
.att-avatar { width:2.5rem; height:2.5rem; border-radius:999px; object-fit:cover; background:var(--app-brand-soft); display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:700; color:var(--app-brand); flex-shrink:0; }
.att-member-info { flex:1; }
.att-member-name { font-size:.875rem; font-weight:600; }
.att-member-meta { font-size:.75rem; color:var(--app-text-muted); }

/* Status radio group */
.att-radios { display:flex; gap:.4rem; }
.att-radio { display:none; }
.att-radio-label { border:1px solid var(--app-border); border-radius:.5rem; padding:.3rem .65rem; font-size:.75rem; font-weight:600; cursor:pointer; color:var(--app-text-muted); user-select:none; }
.att-radio:checked + .att-radio-label { border-color:var(--app-brand); color:var(--app-brand); background:color-mix(in srgb, var(--app-brand) 10%, transparent); }
.att-radio-attended:checked + .att-radio-label { border-color:#059669; color:#059669; background:#d1fae5; }
.att-radio-absent:checked + .att-radio-label    { border-color:#ef4444; color:#ef4444; background:#fee2e2; }
.att-radio-late:checked + .att-radio-label      { border-color:#f59e0b; color:#92400e; background:#fef3c7; }

.att-btn-primary { border:none; background:var(--app-brand); color:#fff; border-radius:.75rem; padding:.7rem 2rem; font-size:.875rem; font-weight:700; cursor:pointer; }
.att-btn-ghost   { border:1px solid var(--app-border); background:transparent; color:var(--app-text-muted); border-radius:.75rem; padding:.7rem 1.5rem; font-size:.875rem; font-weight:600; text-decoration:none; }
</style>

@if($class->status === 'completed')
    <div class="att-warning">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        Attendance has already been submitted for this class and cannot be reopened.
    </div>
@endif

@if($booked->isEmpty())
    <div class="app-panel rounded-[2rem] border flex flex-col items-center py-16 text-center px-4">
        <p class="font-bold text-lg">{{ __('classes.attendance.no_members') }}</p>
    </div>
@else
    <form method="POST" action="{{ route('tenant.classes.attendance.store', $class) }}" id="att-form">
        @csrf

        <div class="app-panel att-card mb-4">
            <div class="att-head">
                {{ $class->class_date->format('D, d M Y') }} · {{ substr($class->start_time,0,5) }}–{{ substr($class->end_time,0,5) }} · {{ $booked->count() }} members
            </div>

            @foreach($booked as $i => $booking)
                <input type="hidden" name="attendances[{{ $i }}][member_id]" value="{{ $booking->member_id }}">
                <div class="att-member-row">
                    @if($booking->member?->photo_url)
                        <img src="{{ $booking->member->photo_url }}" alt="" class="att-avatar">
                    @else
                        <span class="att-avatar">{{ strtoupper(substr($booking->member?->name ?? '?', 0, 1)) }}</span>
                    @endif
                    <div class="att-member-info">
                        <p class="att-member-name">{{ $booking->member?->name }}</p>
                        <p class="att-member-meta">{{ $booking->member?->member_code }} · {{ $booking->member?->plan_name }}</p>
                    </div>
                    <div class="att-radios">
                        @php $cur = in_array($booking->status, ['attended','absent','late_cancel']) ? $booking->status : null; @endphp
                        <input type="radio" id="att-p-{{ $i }}" name="attendances[{{ $i }}][status]" value="attended"
                               class="att-radio att-radio-attended" @checked($cur === 'attended')>
                        <label for="att-p-{{ $i }}" class="att-radio-label">{{ __('classes.attendance.present') }}</label>

                        <input type="radio" id="att-a-{{ $i }}" name="attendances[{{ $i }}][status]" value="absent"
                               class="att-radio att-radio-absent" @checked($cur === 'absent')>
                        <label for="att-a-{{ $i }}" class="att-radio-label">{{ __('classes.attendance.absent') }}</label>

                        <input type="radio" id="att-l-{{ $i }}" name="attendances[{{ $i }}][status]" value="late_cancel"
                               class="att-radio att-radio-late" @checked($cur === 'late_cancel')>
                        <label for="att-l-{{ $i }}" class="att-radio-label">{{ __('classes.attendance.late_cancel') }}</label>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Quick-mark all --}}
        <div class="mb-3 flex gap-2">
            <button type="button" onclick="attMarkAll('attended')" class="text-xs border border-green-300 text-green-700 rounded-lg px-3 py-1.5 font-semibold hover:bg-green-50">All present</button>
            <button type="button" onclick="attMarkAll('absent')" class="text-xs border border-red-300 text-red-600 rounded-lg px-3 py-1.5 font-semibold hover:bg-red-50">All absent</button>
        </div>

        @if($class->status !== 'completed')
        <div class="flex gap-3 items-center">
            <button type="submit" class="att-btn-primary">{{ __('classes.attendance.submit') }}</button>
            <a href="{{ route('tenant.classes.show', $class) }}" class="att-btn-ghost">Back</a>
        </div>
        <p class="mt-2 text-xs text-[var(--app-text-muted)]">{{ __('classes.attendance.warning') }}</p>
        @endif
    </form>

    <script>
        function attMarkAll(status) {
            document.querySelectorAll(`#att-form input[value="${status}"]`).forEach(r => r.checked = true);
        }
    </script>
@endif

</x-layouts.admin>
