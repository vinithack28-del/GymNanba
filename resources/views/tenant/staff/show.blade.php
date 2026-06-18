@php
    $tab = request('tab', 'details');
    $roleColors = [
        'receptionist'   => 'bg-cyan-500/10 text-cyan-300 border-cyan-400/20',
        'trainer'        => 'bg-purple-500/10 text-purple-300 border-purple-400/20',
        'accountant'     => 'bg-amber-500/10 text-amber-300 border-amber-400/20',
        'pos'            => 'bg-blue-500/10 text-blue-300 border-blue-400/20',
        'branch_manager' => 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20',
    ];
    $roleCls = ($roleColors[$staff->role] ?? 'bg-[var(--app-brand-soft)] text-[var(--app-brand)] border-[var(--app-brand)]/20');
@endphp

<x-layouts.admin
    title="{{ $staff->name }}"
    eyebrow="Gym Workspace"
    heading="{{ $staff->name }}"
    subheading="{{ $staff->role_label }} · {{ $staff->branch?->name ?? '—' }}"
>
    <x-slot:headerAction>
        <div class="flex gap-3">
            @if ($canManage)
                <a href="{{ route('tenant.staff.edit', $staff) }}"
                   class="rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    {{ __('staff.actions.edit') }}
                </a>
            @endif
            <a href="{{ route('tenant.staff.index') }}"
               class="rounded-2xl border px-5 py-2.5 text-sm font-semibold hover:opacity-80">
                {{ __('staff.actions.back') }}
            </a>
        </div>
    </x-slot:headerAction>

    {{-- Profile header card --}}
    <div class="app-panel mb-5 flex flex-wrap items-center gap-5 rounded-[2rem] border p-6">
        @if ($staff->photo_url)
            <img src="{{ asset('storage/'.$staff->photo_url) }}"
                 alt="{{ $staff->name }}"
                 class="h-20 w-20 rounded-full object-cover ring-4 ring-[var(--app-border)]">
        @else
            <span class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-[var(--app-brand-soft)] text-2xl font-bold text-[var(--app-brand)]">
                {{ $staff->initials }}
            </span>
        @endif
        <div class="flex-1">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-xl font-semibold">{{ $staff->name }}</h2>
                <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $roleCls }}">
                    {{ str($staff->role)->replace('_', ' ')->title() }}
                </span>
                <span class="rounded-full px-3 py-1 text-xs font-semibold
                    {{ $staff->status === 'active'
                        ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-400/20'
                        : 'bg-red-500/10 text-red-300 border border-red-400/20' }}">
                    {{ __('staff.statuses.'.$staff->status) }}
                </span>
            </div>
            <p class="app-muted mt-1 text-sm">{{ $staff->email }} · {{ $staff->phone }}</p>
            <p class="app-muted text-sm">{{ $staff->branch?->name ?? '—' }} · Joined {{ $staff->join_date?->format('d M Y') }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="mb-5 flex flex-wrap gap-2">
        @foreach ([
            'details'    => __('staff.tabs.details'),
            'logins'     => __('staff.tabs.logins'),
            'attendance' => __('staff.tabs.attendance'),
            'documents'  => __('staff.tabs.documents'),
        ] as $key => $label)
            <a href="{{ route('tenant.staff.show', ['staff' => $staff, 'tab' => $key]) }}"
               class="rounded-full px-5 py-2 text-sm font-semibold transition-colors
                      {{ $tab === $key
                          ? 'bg-orange-500 text-slate-950'
                          : 'border border-[var(--app-border)] hover:opacity-80' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- DETAILS TAB --}}
    @if ($tab === 'details')
        <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">

            <section class="app-panel rounded-[2rem] border p-6">
                <div class="grid gap-5 md:grid-cols-2">
                    @foreach ([
                        __('staff.show.role')      => $staff->role_label,
                        __('staff.show.branch')    => $staff->branch?->name ?? '—',
                        __('staff.show.phone')     => $staff->phone,
                        __('staff.show.email')     => $staff->email,
                        __('staff.show.join_date') => $staff->join_date?->format('d M Y'),
                        __('staff.show.status')    => __('staff.statuses.'.$staff->status),
                        __('staff.show.salary')    => $staff->salary_paise
                                                       ? '₹'.number_format($staff->salary_paise / 100, 2)
                                                       : '—',
                        __('staff.show.last_login')=> $staff->user?->last_login_at?->diffForHumans() ?? __('staff.show.never'),
                    ] as $fieldLabel => $fieldValue)
                        <div>
                            <p class="app-muted text-xs font-semibold uppercase tracking-[0.22em]">{{ $fieldLabel }}</p>
                            <p class="mt-1.5 font-semibold">{{ $fieldValue }}</p>
                        </div>
                    @endforeach

                    <div class="md:col-span-2">
                        <p class="app-muted text-xs font-semibold uppercase tracking-[0.22em]">{{ __('staff.show.notes') }}</p>
                        <p class="mt-1.5 font-semibold">{{ $staff->notes ?: __('staff.show.no_notes') }}</p>
                    </div>
                </div>
            </section>

            <section class="app-panel rounded-[2rem] border p-6">
                <h3 class="mb-4 text-base font-semibold">{{ __('staff.show.account') }}</h3>
                <div class="space-y-4">
                    @foreach ([
                        __('staff.show.login_email')             => $staff->user?->email ?? $staff->email,
                        __('staff.show.password_reset_required') => $staff->user?->must_change_password
                                                                     ? __('staff.show.yes')
                                                                     : __('staff.show.no'),
                        __('staff.show.created') => $staff->created_at?->format('d M Y, h:i A'),
                    ] as $fieldLabel => $fieldValue)
                        <div>
                            <p class="app-muted text-xs font-semibold uppercase tracking-[0.22em]">{{ $fieldLabel }}</p>
                            <p class="mt-1.5 text-sm font-semibold">{{ $fieldValue }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

    {{-- LOGIN ACTIVITY TAB --}}
    @elseif ($tab === 'logins')
        <section class="app-panel rounded-[2rem] border p-6">
            <h3 class="mb-5 text-base font-semibold">{{ __('staff.show.last_logins') }}</h3>
            <div class="space-y-3">
                @forelse ($loginActivities as $activity)
                    <div class="app-panel-strong flex items-start justify-between gap-4 rounded-2xl border p-4">
                        <div>
                            <p class="font-semibold text-sm">{{ $activity->logged_in_at?->format('d M Y, h:i A') }}</p>
                            <p class="app-muted mt-0.5 text-xs">
                                {{ $activity->ip_address ?: __('staff.show.unknown_ip') }}
                                &nbsp;·&nbsp;
                                {{ $activity->device ?: __('staff.show.unknown_device') }}
                            </p>
                        </div>
                        <span class="app-muted text-xs tabular-nums">{{ $activity->logged_in_at?->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="app-muted text-sm">{{ __('staff.show.no_logins') }}</p>
                @endforelse
            </div>
        </section>

    {{-- ATTENDANCE TAB --}}
    @elseif ($tab === 'attendance')
        <section class="app-panel rounded-[2rem] border p-6">
            <div class="mb-6 grid gap-4 md:grid-cols-3">
                @foreach ([
                    __('staff.show.days_present') => $attendanceSummary['days_present'],
                    __('staff.show.days_absent')  => $attendanceSummary['days_absent'],
                    __('staff.show.hours_worked') => $attendanceSummary['hours_worked'],
                ] as $label => $value)
                    <div class="app-panel-strong rounded-2xl border p-4">
                        <p class="app-muted text-xs font-semibold uppercase tracking-[0.22em]">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="space-y-3">
                @forelse ($attendanceLogs as $log)
                    <div class="app-panel-strong flex flex-wrap items-center justify-between gap-3 rounded-2xl border p-4">
                        <div>
                            <p class="font-semibold text-sm">{{ $log->attendance_date?->format('d M Y') }}</p>
                            <p class="app-muted mt-0.5 text-xs">
                                {{ $log->checked_in_at?->format('H:i') ?? '—' }}
                                →
                                {{ $log->checked_out_at?->format('H:i') ?? '—' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-sm">{{ round($log->hours_worked_minutes / 60, 2) }} hrs</p>
                            <p class="app-muted mt-0.5 text-xs uppercase tracking-wider">{{ $log->source }}</p>
                        </div>
                    </div>
                @empty
                    <p class="app-muted text-sm">{{ __('staff.show.no_attendance') }}</p>
                @endforelse
            </div>
        </section>

    {{-- DOCUMENTS TAB --}}
    @else
        <section class="app-panel rounded-[2rem] border p-6">
            <div class="grid gap-5 md:grid-cols-2">
                {{-- ID proof --}}
                <div class="app-panel-strong rounded-2xl border p-5">
                    <p class="app-muted mb-3 text-xs font-semibold uppercase tracking-[0.22em]">{{ __('staff.show.id_proof') }}</p>
                    @if ($staff->id_proof_url)
                        <a href="{{ asset('storage/'.$staff->id_proof_url) }}" target="_blank"
                           class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold hover:opacity-80">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            {{ __('staff.show.view_file') }}
                        </a>
                    @else
                        <p class="text-sm font-semibold">{{ __('staff.show.no_document') }}</p>
                    @endif
                </div>

                {{-- Profile photo --}}
                <div class="app-panel-strong rounded-2xl border p-5">
                    <p class="app-muted mb-3 text-xs font-semibold uppercase tracking-[0.22em]">{{ __('staff.show.profile_photo') }}</p>
                    @if ($staff->photo_url)
                        <div class="flex items-center gap-4">
                            <img src="{{ asset('storage/'.$staff->photo_url) }}"
                                 alt="{{ $staff->name }}"
                                 class="h-16 w-16 rounded-full object-cover ring-2 ring-[var(--app-border)]">
                            <a href="{{ asset('storage/'.$staff->photo_url) }}" target="_blank"
                               class="text-sm font-semibold text-orange-300 hover:underline">
                                {{ __('staff.show.view_photo') }}
                            </a>
                        </div>
                    @else
                        <p class="text-sm font-semibold">{{ __('staff.show.no_photo') }}</p>
                    @endif
                </div>
            </div>

            {{-- Danger zone --}}
            @if ($canManage)
                <div class="mt-6 rounded-2xl border border-red-400/20 bg-red-500/10 p-5">
                    <p class="text-sm font-semibold text-red-200">{{ __('staff.show.delete_title') }}</p>
                    <p class="mt-1 text-sm text-red-200">
                        {{ str(__('staff.show.delete_warning'))->replace(':name', '<strong>'.$staff->name.'</strong>') }}
                    </p>
                    <form method="POST" action="{{ route('tenant.staff.destroy', $staff) }}"
                          class="mt-4 flex flex-col gap-3 sm:flex-row">
                        @csrf
                        @method('DELETE')
                        <input name="confirm_name" required placeholder="{{ $staff->name }}"
                               class="flex-1 rounded-2xl border px-4 py-3 text-sm outline-none">
                        <button type="submit"
                                class="rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400">
                            {{ __('staff.show.delete_button') }}
                        </button>
                    </form>
                </div>
            @endif
        </section>
    @endif

</x-layouts.admin>
