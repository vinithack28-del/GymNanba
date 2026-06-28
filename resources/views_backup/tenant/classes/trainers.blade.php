<x-layouts.admin
    title="{{ __('classes.trainers.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('classes.trainers.title') }}"
    subheading="{{ __('classes.trainers.subtitle') }}"
>

<style>
.trn-filter { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; margin-bottom:1.25rem; }
.trn-select { border:1px solid var(--app-border); border-radius:.6rem; padding:.45rem .85rem; font-size:.8rem; background:transparent; color:var(--app-text); appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='4 6 8 10 12 6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right .6rem center; padding-right:2rem; }
.trn-btn { border:1px solid var(--app-border); border-radius:.6rem; padding:.45rem 1rem; font-size:.8rem; font-weight:600; cursor:pointer; background:var(--app-brand); border-color:var(--app-brand); color:#fff; text-decoration:none; display:inline-flex; align-items:center; gap:.35rem; }

.trn-empty { display:flex; flex-direction:column; align-items:center; padding:5rem 1rem; text-align:center; }
.trn-empty-icon { background:var(--app-panel-strong); border:1px solid var(--app-border); border-radius:999px; color:var(--app-text-muted); height:4.5rem; width:4.5rem; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem; }
</style>

{{-- Filter --}}
<form method="GET" action="{{ route('tenant.classes.trainers') }}">
    <div class="trn-filter">
        @if($branches->isNotEmpty())
            <select name="branch_id" class="trn-select" onchange="this.form.submit()">
                <option value="">{{ __('classes.timetable.all_branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        @endif
        <div class="ml-auto"></div>
        <a href="{{ route('tenant.staff.create') }}?role=trainer" class="trn-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path d="M12 5v14M5 12h14"/></svg>
            {{ __('classes.trainers.add_trainer') }}
        </a>
    </div>
</form>

@if($trainers->isEmpty())
    <div class="app-panel rounded-[2rem] border">
        <div class="trn-empty">
            <div class="trn-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <p class="font-bold text-lg">{{ __('classes.trainers.no_trainers') }}</p>
            <p class="text-sm text-[var(--app-text-muted)] mt-1 max-w-xs">{{ __('classes.trainers.no_trainers_sub') }}</p>
        </div>
    </div>
@else
    <div class="app-panel w-full overflow-hidden rounded-[2rem] border">
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[var(--app-border)] bg-[var(--app-panel-strong)]">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('classes.trainers.table_name') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('classes.trainers.table_spec') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('classes.trainers.table_phone') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('classes.trainers.table_classes') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('classes.trainers.table_status') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--app-border)]">
                    @foreach ($trainers as $trainer)
                        <tr class="transition hover:bg-[var(--app-panel-strong)]">
                            <td class="whitespace-nowrap px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    @if($trainer->photo_url)
                                        <img src="{{ $trainer->photo_url }}" class="h-9 w-9 rounded-full object-cover flex-shrink-0" alt="">
                                    @else
                                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-[color:var(--app-brand-soft)] text-sm font-bold text-[var(--app-brand)]">
                                            {{ strtoupper(substr($trainer->name,0,1)) }}
                                        </span>
                                    @endif
                                    <div>
                                        <p class="font-semibold">{{ $trainer->name }}</p>
                                        <p class="text-xs text-[var(--app-text-muted)]">{{ $trainer->branch?->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">{{ $trainer->specialisation ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">{{ $trainer->phone ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="font-semibold">{{ $classCounts[$trainer->id] ?? 0 }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center text-xs font-bold px-2.5 py-0.5 rounded-full
                                    {{ $trainer->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                    {{ ucfirst($trainer->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('tenant.classes.timetable', ['trainer_id' => $trainer->id]) }}"
                                   class="inline-flex items-center gap-1 border border-[var(--app-border)] rounded-lg px-3 py-1.5 text-xs font-semibold hover:bg-[var(--app-panel-strong)] transition">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                    {{ __('classes.trainers.view_schedule') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

</x-layouts.admin>
