@php
    $current = $locker->currentAssignment;
    $isOccupied = $current !== null;
@endphp

<x-layouts.admin :title="'Locker '.$locker->locker_number">

<style>
.lkp-back { align-items:center; color:var(--app-text-muted); display:inline-flex; font-size:.82rem; font-weight:600; gap:.4rem; margin-bottom:1.25rem; text-decoration:none; transition:color .15s; }
.lkp-back:hover { color:var(--app-brand); }
.lkp-back svg { height:14px; width:14px; }

.lkp-grid { align-items:start; display:grid; gap:1.5rem; grid-template-columns:320px 1fr; }
@media(max-width:960px){ .lkp-grid{ grid-template-columns:1fr; } }

/* Profile card */
.lkp-card { background:var(--app-panel); border:1px solid var(--app-border); border-radius:1.5rem; overflow:hidden; }
.lkp-card-head { align-items:center; border-bottom:1px solid var(--app-border); display:flex; flex-direction:column; gap:.75rem; padding:1.5rem; }
.lkp-avatar { align-items:center; background:var(--app-panel-strong); border-radius:999px; border:1px solid var(--app-border); color:var(--app-text-muted); display:flex; flex-shrink:0; height:4.5rem; justify-content:center; width:4.5rem; }
.lkp-avatar svg { height:1.9rem; width:1.9rem; }
.lkp-number { color:var(--app-text); font-size:1.1rem; font-weight:700; text-align:center; }
.lkp-badges { align-items:center; display:flex; flex-wrap:wrap; gap:.4rem; justify-content:center; }
.lkp-badge { align-items:center; border-radius:999px; display:inline-flex; font-size:.72rem; font-weight:700; gap:.35rem; letter-spacing:.02em; padding:.25rem .7rem; }
.lkp-badge-available { background:color-mix(in srgb,#22c55e 14%,transparent); color:#22c55e; }
.lkp-badge-occupied  { background:color-mix(in srgb,#f59e0b 14%,transparent); color:#f59e0b; }
.lkp-badge-active    { background:color-mix(in srgb,#22c55e 10%,transparent); color:#16a34a; }
.lkp-badge-inactive  { background:color-mix(in srgb,#94a3b8 12%,transparent); color:#94a3b8; }
.lkp-badge-maintenance { background:color-mix(in srgb,#f59e0b 10%,transparent); color:#b45309; }

.lkp-card-body { display:flex; flex-direction:column; gap:.85rem; padding:1.25rem 1.5rem; }
.lkp-row { align-items:flex-start; display:flex; gap:.75rem; }
.lkp-row-icon { align-items:center; color:var(--app-text-muted); display:flex; flex-shrink:0; justify-content:center; margin-top:.1rem; width:1.5rem; }
.lkp-row-icon svg { height:1rem; width:1rem; }
.lkp-row-label { color:var(--app-text-muted); font-size:.72rem; font-weight:600; letter-spacing:.06em; text-transform:uppercase; }
.lkp-row-val { color:var(--app-text); font-size:.88rem; margin-top:.1rem; }

/* Assignment block */
.lkp-assign { border-top:1px solid var(--app-border); margin-top:.25rem; padding:1.25rem 1.5rem; }
.lkp-assign-label { color:var(--app-text-muted); font-size:.72rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; }
.lkp-assign-name { color:var(--app-text); font-size:1rem; font-weight:700; margin-top:.5rem; text-decoration:none; }
.lkp-assign-name:hover { text-decoration:underline; }
.lkp-assign-meta { color:var(--app-text-muted); font-size:.78rem; margin-top:.2rem; }
.lkp-assign-dates { align-items:center; color:var(--app-text-muted); display:flex; font-size:.78rem; gap:.3rem; margin-top:.55rem; }
.lkp-assign-arrow { color:var(--app-brand); }
.lkp-assign-empty { color:var(--app-text-muted); font-size:.85rem; margin-top:.5rem; }

/* History panel */
.lkp-hist { background:var(--app-panel); border:1px solid var(--app-border); border-radius:1.5rem; overflow:hidden; }
.lkp-hist-head { align-items:center; border-bottom:1px solid var(--app-border); display:flex; justify-content:space-between; padding:1rem 1.5rem; }
.lkp-hist-head h3 { color:var(--app-text); font-size:.95rem; font-weight:700; }
.lkp-hist-count { background:var(--app-panel-strong); border:1px solid var(--app-border); border-radius:999px; color:var(--app-text-muted); font-size:.75rem; font-weight:600; padding:.1rem .55rem; }
.lkp-badge-current { background:color-mix(in srgb,var(--app-brand) 14%,transparent); border-radius:999px; color:var(--app-brand); font-size:.65rem; font-weight:700; padding:.1rem .45rem; }
</style>

<a href="{{ route('tenant.lockers.index') }}" class="lkp-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Back to Lockers
</a>

<div class="lkp-grid">

    {{-- ── Left: locker profile card ───────────────────────────────────────── --}}
    <div class="lkp-card">
        <div class="lkp-card-head">
            <div class="lkp-avatar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="5" y="2" width="14" height="20" rx="2"/>
                    <circle cx="12" cy="13" r="2"/>
                    <path d="M12 9v2"/>
                </svg>
            </div>
            <div>
                <p class="lkp-number">{{ $locker->locker_number }}</p>
            </div>
            <div class="lkp-badges">
                <span class="lkp-badge {{ $isOccupied ? 'lkp-badge-occupied' : 'lkp-badge-available' }}">
                    <span style="background:currentColor;border-radius:50%;display:inline-block;height:.45rem;width:.45rem"></span>
                    {{ $isOccupied ? 'Occupied' : 'Available' }}
                </span>
                @php
                    $statusBadge = match($locker->status) {
                        'active'      => 'lkp-badge-active',
                        'inactive'    => 'lkp-badge-inactive',
                        'maintenance' => 'lkp-badge-maintenance',
                        default       => 'lkp-badge-inactive',
                    };
                @endphp
                <span class="lkp-badge {{ $statusBadge }}">
                    <span style="background:currentColor;border-radius:50%;display:inline-block;height:.45rem;width:.45rem"></span>
                    {{ \App\Models\Locker::STATUSES[$locker->status] ?? ucfirst($locker->status) }}
                </span>
            </div>
        </div>

        <div class="lkp-card-body">
            {{-- Location --}}
            <div class="lkp-row">
                <span class="lkp-row-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </span>
                <div>
                    <p class="lkp-row-label">Location / Zone</p>
                    <p class="lkp-row-val">{{ $locker->location ?: '—' }}</p>
                </div>
            </div>

            {{-- Branch --}}
            @if ($locker->branch)
            <div class="lkp-row">
                <span class="lkp-row-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg>
                </span>
                <div>
                    <p class="lkp-row-label">Branch</p>
                    <p class="lkp-row-val">{{ $locker->branch->name }}</p>
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if ($locker->notes)
            <div class="lkp-row">
                <span class="lkp-row-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>
                </span>
                <div>
                    <p class="lkp-row-label">Notes</p>
                    <p class="lkp-row-val">{{ $locker->notes }}</p>
                </div>
            </div>
            @endif

            {{-- Added --}}
            <div class="lkp-row">
                <span class="lkp-row-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>
                </span>
                <div>
                    <p class="lkp-row-label">Added</p>
                    <p class="lkp-row-val">{{ $locker->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Current assignment --}}
        <div class="lkp-assign">
            <p class="lkp-assign-label">Currently Assigned To</p>
            @if ($current && $current->member)
                <a href="{{ route('tenant.members.show', $current->member) }}" class="lkp-assign-name">
                    {{ $current->member->name }}
                </a>
                <p class="lkp-assign-meta">{{ $current->member->member_code }} · {{ $current->member->phone }}</p>
                <div class="lkp-assign-dates">
                    <span>{{ $current->from_date?->format('d M Y') ?? '—' }}</span>
                    <span class="lkp-assign-arrow">→</span>
                    <span>{{ $current->to_date?->format('d M Y') ?? 'Ongoing' }}</span>
                    <span style="color:var(--app-text-muted)">·</span>
                    <span>{{ $lockerData['current_assignment']['days_so_far'] ?? 0 }} days</span>
                </div>
                @if ($current->notes)
                    <p class="lkp-assign-meta" style="margin-top:.5rem">{{ $current->notes }}</p>
                @endif
            @else
                <p class="lkp-assign-empty">No active assignment.</p>
            @endif
        </div>
    </div>

    {{-- ── Right: usage history ─────────────────────────────────────────────── --}}
    <div class="lkp-hist">
        <div class="lkp-hist-head">
            <h3>Usage History</h3>
            <span class="lkp-hist-count">{{ count($lockerData['history'] ?? []) }} {{ Str::plural('record', count($lockerData['history'] ?? [])) }}</span>
        </div>

        @if (!empty($lockerData['history']))
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm">
                    <thead style="background:var(--app-panel-strong)">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Member</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">From</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">To</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Days</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--app-border)]">
                        @foreach ($lockerData['history'] as $row)
                            <tr style="{{ $row['is_current'] ? 'background:color-mix(in srgb,var(--app-brand) 6%,transparent)' : 'background:var(--app-panel)' }}">
                                <td class="px-5 py-3.5">
                                    @if ($row['member_url'])
                                        <a href="{{ $row['member_url'] }}" class="font-semibold hover:underline" style="color:var(--app-text)">{{ $row['member_name'] }}</a>
                                    @else
                                        <span class="font-semibold" style="color:var(--app-text)">{{ $row['member_name'] }}</span>
                                    @endif
                                    @if ($row['member_code'])
                                        <div class="text-xs mt-0.5" style="color:var(--app-text-muted)">{{ $row['member_code'] }}</div>
                                    @endif
                                    @if ($row['is_current'])
                                        <span class="lkp-badge-current mt-1 inline-block">Current</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-sm" style="color:var(--app-text-muted)">{{ $row['from_date'] }}</td>
                                <td class="px-4 py-3.5 text-sm" style="color:var(--app-text-muted)">{{ $row['to_date'] ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold" style="color:var(--app-text)">{{ $row['days'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center gap-3 py-16 text-center px-6">
                <div style="align-items:center;background:color-mix(in srgb,var(--app-brand) 10%,transparent);border-radius:1rem;color:var(--app-brand);display:flex;height:3.5rem;justify-content:center;width:3.5rem">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="13" r="2"/><path d="M12 9v2"/></svg>
                </div>
                <p class="text-sm font-semibold" style="color:var(--app-text)">No usage history yet.</p>
                <p class="text-sm" style="color:var(--app-text-muted)">Assign a member to this locker to start tracking usage.</p>
            </div>
        @endif
    </div>

</div>

</x-layouts.admin>
