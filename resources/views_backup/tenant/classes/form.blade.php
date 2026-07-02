<x-layouts.admin
    title="{{ isset($editing) ? __('classes.form.edit_title') : __('classes.form.create_title') }}"
    eyebrow="Gym Workspace"
    heading="{{ isset($editing) ? __('classes.form.edit_title') : __('classes.form.create_title') }}"
>

<style>
.clf-card  { border:1px solid var(--app-border); border-radius:1.5rem; overflow:hidden; margin-bottom:1.5rem; }
.clf-head  { padding:1rem 1.5rem; border-bottom:1px solid var(--app-border); background:var(--app-panel-strong); }
.clf-head h3 { font-size:.9rem; font-weight:700; }
.clf-body  { padding:1.25rem 1.5rem; display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
@media(max-width:640px){ .clf-body { grid-template-columns:1fr; } }
.clf-field { display:flex; flex-direction:column; gap:.3rem; }
.clf-field.full { grid-column:1/-1; }
.clf-label { font-size:.8rem; font-weight:600; color:var(--app-text-muted); }
.clf-input, .clf-select, .clf-textarea {
    border:1px solid var(--app-border); border-radius:.6rem; padding:.5rem .75rem;
    font-size:.875rem; background:transparent; color:var(--app-text); outline:none; width:100%;
}
.clf-input:focus, .clf-select:focus, .clf-textarea:focus { border-color:var(--app-brand); }
.clf-select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='4 6 8 10 12 6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right .6rem center; padding-right:2rem; }
.clf-error { font-size:.75rem; color:#ef4444; }
.clf-toggle { display:flex; align-items:center; gap:.6rem; cursor:pointer; }
.clf-toggle input[type=checkbox] { width:1.1rem; height:1.1rem; accent-color:var(--app-brand); }

/* Day checkboxes */
.clf-days { display:flex; flex-wrap:wrap; gap:.4rem; }
.clf-day-cb { display:none; }
.clf-day-label { border:1px solid var(--app-border); border-radius:.5rem; padding:.3rem .65rem; font-size:.8rem; font-weight:600; cursor:pointer; color:var(--app-text-muted); user-select:none; }
.clf-day-cb:checked + .clf-day-label { background:var(--app-brand); border-color:var(--app-brand); color:#fff; }

.clf-actions { display:flex; gap:.75rem; align-items:center; }
.clf-btn-primary { border:none; background:var(--app-brand); color:#fff; border-radius:.75rem; padding:.65rem 1.5rem; font-size:.875rem; font-weight:700; cursor:pointer; }
.clf-btn-ghost   { border:1px solid var(--app-border); background:transparent; color:var(--app-text-muted); border-radius:.75rem; padding:.65rem 1.5rem; font-size:.875rem; font-weight:600; text-decoration:none; cursor:pointer; }
</style>

<form method="POST"
      action="{{ isset($editing) ? route('tenant.classes.update', $class) : route('tenant.classes.store') }}">
    @csrf
    @if(isset($editing)) @method('PUT') @endif

    {{-- Section 1: Details --}}
    <div class="clf-card app-panel">
        <div class="clf-head"><h3>Class details</h3></div>
        <div class="clf-body">
            {{-- Name --}}
            <div class="clf-field full">
                <label class="clf-label">{{ __('classes.form.name') }} <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $class?->name) }}"
                       placeholder="{{ __('classes.form.name_ph') }}" class="clf-input" maxlength="80" required>
                @error('name')<p class="clf-error">{{ $message }}</p>@enderror
            </div>

            {{-- Type --}}
            <div class="clf-field">
                <label class="clf-label">{{ __('classes.form.type') }} <span class="text-red-500">*</span></label>
                <select name="type" class="clf-select" required>
                    @foreach ($types as $t)
                        <option value="{{ $t }}" @selected(old('type', $class?->type) === $t)>{{ __('classes.types.'.$t) }}</option>
                    @endforeach
                </select>
                @error('type')<p class="clf-error">{{ $message }}</p>@enderror
            </div>

            {{-- Branch --}}
            <div class="clf-field">
                <label class="clf-label">{{ __('classes.form.branch') }} <span class="text-red-500">*</span></label>
                <select name="branch_id" class="clf-select" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $class?->branch_id ?? $selectedBranchId ?? null) == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id')<p class="clf-error">{{ $message }}</p>@enderror
            </div>

            {{-- Room --}}
            <div class="clf-field">
                <label class="clf-label">{{ __('classes.form.room') }}</label>
                <input type="text" name="room" value="{{ old('room', $class?->room) }}"
                       placeholder="{{ __('classes.form.room_ph') }}" class="clf-input" maxlength="80">
                @error('room')<p class="clf-error">{{ $message }}</p>@enderror
            </div>

            {{-- Trainer --}}
            <div class="clf-field">
                <label class="clf-label">{{ __('classes.form.trainer') }}</label>
                <select name="trainer_id" class="clf-select">
                    <option value="">{{ __('classes.form.no_trainer') }}</option>
                    @foreach ($trainers as $trainer)
                        <option value="{{ $trainer->id }}" @selected(old('trainer_id', $class?->trainer_id) == $trainer->id)>{{ $trainer->name }}</option>
                    @endforeach
                </select>
                @error('trainer_id')<p class="clf-error">{{ $message }}</p>@enderror
            </div>

            {{-- Description --}}
            <div class="clf-field full">
                <label class="clf-label">{{ __('classes.form.description') }}</label>
                <textarea name="description" rows="2" maxlength="500"
                          placeholder="{{ __('classes.form.description_ph') }}" class="clf-textarea">{{ old('description', $class?->description) }}</textarea>
                @error('description')<p class="clf-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Section 2: Schedule --}}
    <div class="clf-card app-panel">
        <div class="clf-head"><h3>Schedule</h3></div>
        <div class="clf-body">
            {{-- Start time / End time --}}
            <div class="clf-field">
                <label class="clf-label">{{ __('classes.form.start_time') }} <span class="text-red-500">*</span></label>
                <input type="time" name="start_time" value="{{ old('start_time', $class?->start_time ? substr($class->start_time,0,5) : '') }}" class="clf-input" required>
                @error('start_time')<p class="clf-error">{{ $message }}</p>@enderror
            </div>
            <div class="clf-field">
                <label class="clf-label">{{ __('classes.form.end_time') }} <span class="text-red-500">*</span></label>
                <input type="time" name="end_time" value="{{ old('end_time', $class?->end_time ? substr($class->end_time,0,5) : '') }}" class="clf-input" required>
                @error('end_time')<p class="clf-error">{{ $message }}</p>@enderror
            </div>

            @if(!isset($editing))
            {{-- Repeat --}}
            <div class="clf-field">
                <label class="clf-label">{{ __('classes.form.repeat') }} <span class="text-red-500">*</span></label>
                <select name="repeat" id="clf-repeat" class="clf-select" onchange="clfRepeatChange()" required>
                    <option value="none" @selected(old('repeat','none') === 'none')>{{ __('classes.form.repeat_none') }}</option>
                    <option value="daily" @selected(old('repeat') === 'daily')>{{ __('classes.form.repeat_daily') }}</option>
                    <option value="weekly" @selected(old('repeat') === 'weekly')>{{ __('classes.form.repeat_weekly') }}</option>
                </select>
                @error('repeat')<p class="clf-error">{{ $message }}</p>@enderror
            </div>

            {{-- Start date --}}
            <div class="clf-field">
                <label class="clf-label">{{ __('classes.form.start_date') }} <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" class="clf-input" required min="{{ now()->toDateString() }}">
                @error('start_date')<p class="clf-error">{{ $message }}</p>@enderror
            </div>

            {{-- Days of week (weekly only) --}}
            <div class="clf-field full" id="clf-days-row" style="display:none">
                <label class="clf-label">{{ __('classes.form.days_of_week') }} <span class="text-red-500">*</span></label>
                <div class="clf-days">
                    @foreach(__('classes.days') as $num => $label)
                        <input type="checkbox" id="clf-day-{{ $num }}" name="days_of_week[]" value="{{ $num }}"
                               class="clf-day-cb" @checked(in_array($num, old('days_of_week', [])))>
                        <label for="clf-day-{{ $num }}" class="clf-day-label">{{ $label }}</label>
                    @endforeach
                </div>
                @error('days_of_week')<p class="clf-error">{{ $message }}</p>@enderror
            </div>

            {{-- End date (recurring only) --}}
            <div class="clf-field" id="clf-enddate-row" style="display:none">
                <label class="clf-label">{{ __('classes.form.end_date') }} <span class="text-red-500">*</span></label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" class="clf-input">
                @error('end_date')<p class="clf-error">{{ $message }}</p>@enderror
            </div>
            @endif

            {{-- Capacity --}}
            <div class="clf-field">
                <label class="clf-label">{{ __('classes.form.max_capacity') }} <span class="text-red-500">*</span></label>
                <input type="number" name="max_capacity" value="{{ old('max_capacity', $class?->max_capacity ?? 20) }}"
                       min="1" max="500" class="clf-input" required>
                @error('max_capacity')<p class="clf-error">{{ $message }}</p>@enderror
            </div>

            {{-- Toggles --}}
            <div class="clf-field flex flex-col gap-2 justify-center">
                <label class="clf-toggle">
                    <input type="checkbox" name="allow_waitlist" value="1" @checked(old('allow_waitlist', $class?->allow_waitlist ?? true))>
                    <span class="clf-label" style="margin:0">{{ __('classes.form.allow_waitlist') }}</span>
                </label>
                <label class="clf-toggle">
                    <input type="checkbox" name="visible" value="1" @checked(old('visible', $class?->visible ?? true))>
                    <span class="clf-label" style="margin:0">{{ __('classes.form.visible') }}</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Edit scope (edit mode only) --}}
    @if(isset($editing) && $class->parent_id)
    <div class="clf-card app-panel">
        <div class="clf-head"><h3>{{ __('classes.form.scope') }}</h3></div>
        <div class="clf-body" style="grid-template-columns:1fr">
            @foreach(['this' => __('classes.form.scope_this'), 'future' => __('classes.form.scope_future')] as $val => $lbl)
                <label class="clf-toggle">
                    <input type="radio" name="scope" value="{{ $val }}" @checked($val === 'this')>
                    <span class="clf-label" style="margin:0">{{ $lbl }}</span>
                </label>
            @endforeach
            @if(!isset($editing))
                <input type="hidden" name="scope" value="this">
            @endif
        </div>
    </div>
    @else
        <input type="hidden" name="scope" value="this">
    @endif

    {{-- Actions --}}
    <div class="clf-actions">
        <button type="submit" class="clf-btn-primary">
            {{ isset($editing) ? __('classes.form.update_btn') : __('classes.form.create_btn') }}
        </button>
        <a href="{{ route('tenant.classes.timetable') }}" class="clf-btn-ghost">{{ __('classes.form.cancel_btn') }}</a>
    </div>
</form>

<script>
    function clfRepeatChange() {
        const val = document.getElementById('clf-repeat').value;
        document.getElementById('clf-days-row').style.display    = val === 'weekly' ? 'flex' : 'none';
        document.getElementById('clf-enddate-row').style.display = val !== 'none'   ? 'block' : 'none';
    }
    // Init on load (handles old() values after validation failure)
    clfRepeatChange();
</script>

</x-layouts.admin>
