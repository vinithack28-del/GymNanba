<x-layouts.admin
    title="{{ __('staff.attendance_page.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('staff.attendance_page.title') }}"
    subheading="{{ __('staff.attendance_page.subheading') }}"
>

{{-- Summary stat cards --}}
<div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3">
    @foreach ([
        ['label' => __('staff.attendance_page.days_present'),  'value' => $summary['days_present']],
        ['label' => __('staff.attendance_page.hours_worked'),  'value' => $summary['hours_worked']],
        ['label' => __('staff.attendance_page.leaves_marked'), 'value' => $summary['leaves_marked']],
    ] as $card)
        <div class="app-panel rounded-2xl border p-4">
            <p class="app-muted text-xs font-semibold uppercase tracking-[0.22em]">{{ $card['label'] }}</p>
            <p class="mt-2 text-2xl font-semibold">{{ $card['value'] }}</p>
        </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="app-panel rounded-[2rem] border p-4">
    <form method="GET" action="{{ route('tenant.staff.attendance') }}"
          class="flex flex-wrap items-center gap-2">
        <input type="date" name="from" value="{{ $from }}"
               class="rounded-2xl border px-4 py-3 text-sm outline-none">
        <input type="date" name="to" value="{{ $to }}"
               class="rounded-2xl border px-4 py-3 text-sm outline-none">

        <select name="branch_id" onchange="this.form.submit()" class="rounded-2xl border px-4 py-3 text-sm outline-none">
            <option value="">{{ __('staff.attendance_page.all_branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>

        <select name="staff_id" onchange="this.form.submit()" class="rounded-2xl border px-4 py-3 text-sm outline-none">
            <option value="">{{ __('staff.attendance_page.all_staff') }}</option>
            @foreach ($staffOptions as $staffOption)
                <option value="{{ $staffOption->id }}" @selected((string) request('staff_id') === (string) $staffOption->id)>
                    {{ $staffOption->name }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="rounded-2xl border px-4 py-3 text-sm font-semibold hover:opacity-80">
            {{ __('staff.attendance_page.apply') }}
        </button>

        <a href="{{ route('tenant.staff.attendance', array_merge(request()->query(), ['export' => 'csv'])) }}"
           class="rounded-2xl border px-4 py-3 text-sm font-semibold hover:opacity-80">
            {{ __('staff.filters.export') }}
        </a>
    </form>
</div>

{{-- Manual attendance entry form --}}
<div class="app-panel mt-5 rounded-[2rem] border p-6">
    <h3 class="mb-5 text-base font-semibold">{{ __('staff.actions.add_attendance') }}</h3>
    <form method="POST" action="{{ route('tenant.staff.attendance.store') }}"
          class="grid gap-4 md:grid-cols-2">
        @csrf

        @if ($errors->any())
            <div class="md:col-span-2 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <div>
            <label class="mb-2 block text-sm font-medium">{{ __('staff.table.name') }}</label>
            <select name="staff_id" class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>
                <option value="">{{ __('staff.attendance_page.select_staff') }}</option>
                @foreach ($staffOptions as $staffOption)
                    <option value="{{ $staffOption->id }}" @selected(old('staff_id') == $staffOption->id)>
                        {{ $staffOption->name }} · {{ $staffOption->role_label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">{{ __('staff.attendance_page.date') }}</label>
            <input type="date" name="attendance_date"
                   value="{{ old('attendance_date', now()->toDateString()) }}"
                   class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">{{ __('staff.attendance_page.check_in') }}</label>
            <input type="time" name="checked_in_at" value="{{ old('checked_in_at') }}"
                   class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">{{ __('staff.attendance_page.check_out') }}</label>
            <input type="time" name="checked_out_at" value="{{ old('checked_out_at') }}"
                   class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium">Reason</label>
            <textarea name="reason" rows="2"
                      class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>{{ old('reason') }}</textarea>
        </div>

        <div class="md:col-span-2">
            <button type="submit"
                    class="rounded-2xl bg-orange-500 px-6 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                {{ __('staff.attendance_page.save') }}
            </button>
        </div>
    </form>
</div>

{{-- Attendance log table --}}
<div class="app-panel mt-5 w-full overflow-hidden rounded-[2rem] border">
    <div class="w-full overflow-x-auto">
        <table class="w-full min-w-full text-sm">
            <thead class="app-table-head">
                <tr>
                    @foreach ([
                        __('staff.attendance_page.date'),
                        __('staff.attendance_page.staff_name'),
                        __('staff.attendance_page.role'),
                        __('staff.attendance_page.check_in'),
                        __('staff.attendance_page.check_out'),
                        __('staff.attendance_page.hours'),
                        __('staff.attendance_page.source'),
                    ] as $head)
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em]">{{ $head }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t border-[var(--app-border)] hover:bg-white/[0.02]">
                        <td class="px-4 py-4 tabular-nums">{{ $log->attendance_date?->format('d M Y') }}</td>
                        <td class="px-4 py-4 font-medium">{{ $log->staff?->name }}</td>
                        <td class="px-4 py-4">
                            <span class="rounded-full bg-[var(--app-brand-soft)] px-2.5 py-1 text-xs font-semibold text-[var(--app-brand)]">
                                {{ $log->staff?->role_label }}
                            </span>
                        </td>
                        <td class="px-4 py-4 tabular-nums">{{ $log->checked_in_at?->format('H:i') ?? '—' }}</td>
                        <td class="px-4 py-4 tabular-nums">{{ $log->checked_out_at?->format('H:i') ?? '—' }}</td>
                        <td class="px-4 py-4 tabular-nums">{{ round($log->hours_worked_minutes / 60, 2) }}</td>
                        <td class="px-4 py-4">
                            <span class="rounded-full border px-2.5 py-1 text-xs uppercase tracking-wider">
                                {{ $log->source }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-sm opacity-50">
                            {{ __('staff.attendance_page.no_records') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-[var(--app-border)] px-4 py-3">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>

</x-layouts.admin>
