@php
    $branchLabel = $branch?->name ?? 'All Branches';
@endphp

<x-layouts.admin
    title="Dashboard"
    eyebrow="Gym Workspace"
    heading="{{ $tenant?->gym_name ?? 'Gym Dashboard' }}"
    subheading="Operational snapshot for {{ $branchLabel }}."
>
<style>
.dash-shell { display:flex; flex-direction:column; gap:1.25rem; }
.dash-card-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
@media(max-width:1280px){ .dash-card-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:720px){ .dash-card-grid{ grid-template-columns:1fr; } }
.dash-stat {
    display:block; text-decoration:none; border:1px solid var(--app-border); border-radius:1.4rem;
    background:linear-gradient(180deg, color-mix(in srgb, var(--app-panel) 92%, white 8%), var(--app-panel));
    padding:1rem 1.05rem; transition:transform .15s ease,border-color .15s ease,box-shadow .15s ease;
}
.dash-stat:hover { transform:translateY(-2px); border-color:color-mix(in srgb, var(--app-brand) 35%, var(--app-border)); box-shadow:0 10px 28px rgba(15,23,42,.08); }
.dash-stat-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.11em; color:var(--app-text-muted); }
.dash-stat-value { margin-top:.55rem; font-size:1.75rem; font-weight:700; color:var(--app-text); }
.dash-stat-sub { margin-top:.35rem; font-size:.82rem; color:var(--app-text-muted); }
.dash-grid-2 { display:grid; grid-template-columns:1.2fr .8fr; gap:1rem; }
.dash-grid-3 { display:grid; grid-template-columns:1.1fr .9fr; gap:1rem; }
@media(max-width:1100px){ .dash-grid-2,.dash-grid-3{ grid-template-columns:1fr; } }
.dash-panel { border:1px solid var(--app-border); border-radius:1.6rem; background:var(--app-panel); padding:1.15rem 1.2rem; }
.dash-head { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; margin-bottom:1rem; }
.dash-title { font-size:1rem; font-weight:700; color:var(--app-text); }
.dash-sub { margin-top:.22rem; font-size:.8rem; color:var(--app-text-muted); }
.dash-link { font-size:.78rem; font-weight:700; color:var(--app-brand); text-decoration:none; white-space:nowrap; }
.dash-link:hover { text-decoration:underline; }
.dash-chart-wrap { height:280px; }
.dash-table { width:100%; border-collapse:collapse; }
.dash-table th { text-align:left; font-size:.7rem; text-transform:uppercase; letter-spacing:.1em; color:var(--app-text-muted); padding:.7rem .25rem; border-bottom:1px solid var(--app-border); }
.dash-table td { padding:.85rem .25rem; border-bottom:1px solid color-mix(in srgb, var(--app-border) 65%, transparent); font-size:.84rem; color:var(--app-text); vertical-align:top; }
.dash-table tr:last-child td { border-bottom:none; }
.dash-muted { color:var(--app-text-muted); }
.dash-pill { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.18rem .55rem; font-size:.68rem; font-weight:700; background:var(--app-panel-strong); color:var(--app-text-muted); }
.dash-list { display:flex; flex-direction:column; gap:.7rem; }
.dash-item { border:1px solid var(--app-border); border-radius:1rem; background:var(--app-panel-strong); padding:.8rem .9rem; }
.dash-item-title { font-size:.88rem; font-weight:700; color:var(--app-text); }
.dash-item-sub { margin-top:.22rem; font-size:.78rem; color:var(--app-text-muted); }
.dash-item-row { display:flex; align-items:center; justify-content:space-between; gap:.7rem; }
.dash-empty { padding:1.25rem 0; text-align:center; font-size:.84rem; color:var(--app-text-muted); }
.dash-search {
    width:100%; border:1px solid var(--app-border); border-radius:.75rem; padding:.55rem .8rem; font-size:.84rem;
    background:var(--app-panel-strong); color:var(--app-text); outline:none;
}
.dash-search:focus { border-color:var(--app-brand); }
.dash-action {
    display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--app-border); border-radius:.7rem;
    padding:.42rem .72rem; font-size:.76rem; font-weight:700; color:var(--app-text); text-decoration:none; background:transparent;
}
.dash-action:hover { border-color:var(--app-brand); color:var(--app-brand); }
.dash-tabs { display:flex; flex-wrap:wrap; gap:.45rem; margin-bottom:.9rem; }
.dash-tab {
    border:1px solid var(--app-border); background:transparent; color:var(--app-text-muted); border-radius:999px;
    padding:.36rem .72rem; font-size:.77rem; font-weight:700; cursor:pointer;
}
.dash-tab.active { color:var(--app-text); border-color:color-mix(in srgb, var(--app-brand) 35%, var(--app-border)); background:color-mix(in srgb, var(--app-brand-soft) 55%, transparent); }
.dash-tab-panel { display:none; }
.dash-tab-panel.active { display:block; }
</style>

<div class="dash-shell">
    <section class="dash-panel">
        <div class="dash-head" style="margin-bottom:0">
            <div>
                <div class="dash-title">Overview</div>
                <div class="dash-sub">Showing metrics for {{ $branchLabel }}{{ $branch ? '' : ' across all active branches' }}.</div>
            </div>
            <span class="dash-pill">{{ now()->format('d M Y') }}</span>
        </div>
    </section>

    <section class="dash-card-grid">
        @foreach ($stats as $card)
            <a href="{{ $card['route'] }}" class="dash-stat">
                <div class="dash-stat-label">{{ $card['label'] }}</div>
                <div class="dash-stat-value">{{ $card['value'] }}</div>
                <div class="dash-stat-sub">{{ $card['sub'] }}</div>
            </a>
        @endforeach
    </section>

    @if ($canViewRevenue || $canViewAttendance)
        <section class="dash-grid-2">
            @if ($canViewRevenue)
                <div class="dash-panel">
                    <div class="dash-head">
                        <div>
                            <div class="dash-title">Monthly Revenue</div>
                            <div class="dash-sub">Revenue trend for the last 6 months.</div>
                        </div>
                        <a href="{{ route('tenant.reports.revenue') }}" class="dash-link">View Report</a>
                    </div>
                    <div class="dash-chart-wrap"><canvas id="dashRevenueChart"></canvas></div>
                </div>
            @endif

            @if ($canViewAttendance)
                <div class="dash-panel">
                    <div class="dash-head">
                        <div>
                            <div class="dash-title">Weekly Check-ins</div>
                            <div class="dash-sub">Daily member check-ins over the last 7 days.</div>
                        </div>
                        <a href="{{ route('tenant.attendance.checkins') }}" class="dash-link">View Attendance</a>
                    </div>
                    <div class="dash-chart-wrap"><canvas id="dashCheckinChart"></canvas></div>
                </div>
            @endif
        </section>
    @endif

    <section class="dash-grid-3">
        @if ($canViewRevenue)
            <div class="dash-panel">
                <div class="dash-head">
                    <div>
                        <div class="dash-title">Recent Payments</div>
                        <div class="dash-sub">Latest 5 payments recorded for this branch scope.</div>
                    </div>
                    <a href="{{ route('tenant.payments.history') }}" class="dash-link">View All</a>
                </div>
                @if ($recentPayments->isEmpty())
                    <div class="dash-empty">No recent payments found.</div>
                @else
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Plan</th>
                                <th style="text-align:right">Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentPayments as $payment)
                                <tr>
                                    <td>{{ $payment->member?->name ?? 'Walk-in' }}</td>
                                    <td class="dash-muted">{{ $payment->plan?->name ?? '—' }}</td>
                                    <td style="text-align:right">₹{{ number_format($payment->total_paise / 100, 2) }}</td>
                                    <td class="dash-muted">{{ $payment->payment_date?->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        <div class="dash-panel">
            <div class="dash-head">
                <div>
                    <div class="dash-title">Upcoming Birthdays</div>
                    <div class="dash-sub">Members with birthdays today or within this week.</div>
                </div>
            </div>
            @if ($birthdays->isEmpty())
                <div class="dash-empty">No birthdays today or this week.</div>
            @else
                <div class="dash-list">
                    @foreach ($birthdays as $member)
                        @php
                            $phone = preg_replace('/\D+/', '', $member->phone ?? '');
                            $wishText = rawurlencode("Happy Birthday {$member->name}! Wishing you strength, health, and a fantastic year ahead from {$tenant?->gym_name}.");
                        @endphp
                        <div class="dash-item">
                            <div class="dash-item-row">
                                <div>
                                    <div class="dash-item-title">{{ $member->name }}</div>
                                    <div class="dash-item-sub">
                                        {{ $member->next_birthday->format('D, d M') }}
                                        •
                                        {{ $member->birthday_bucket === 'today' ? 'Today' : 'This Week' }}
                                    </div>
                                </div>
                                @if ($phone)
                                    <a href="https://wa.me/{{ $phone }}?text={{ $wishText }}" target="_blank" rel="noopener" class="dash-action">WhatsApp</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if ($canViewRenewals)
        <section class="dash-grid-2">
            <div class="dash-panel">
                <div class="dash-head">
                    <div>
                        <div class="dash-title">Expired – Not Renewed</div>
                        <div class="dash-sub">Expired plans with no renewal recorded yet.</div>
                    </div>
                    <a href="{{ route('tenant.renewals.index', ['tab' => 'expired']) }}" class="dash-link">View All</a>
                </div>
                <input type="text" class="dash-search" id="expiredSearch" placeholder="Search by client or plan">
                <div class="dash-list" id="expiredList" style="margin-top:.9rem">
                    @forelse ($expiredMembers as $member)
                        <div class="dash-item expired-row" data-search="{{ strtolower($member->name . ' ' . ($member->plan?->name ?? $member->plan_name ?? '')) }}">
                            <div class="dash-item-row">
                                <div>
                                    <div class="dash-item-title">{{ $member->name }}</div>
                                    <div class="dash-item-sub">{{ $member->plan?->name ?? $member->plan_name ?? '—' }} • Expired {{ $member->expiry_date?->format('d M Y') }}</div>
                                </div>
                                <a href="{{ route('tenant.payments.collect', ['member_id' => $member->id]) }}" class="dash-action">Add Revenue</a>
                            </div>
                        </div>
                    @empty
                        <div class="dash-empty">No expired memberships pending renewal.</div>
                    @endforelse
                </div>
            </div>

            <div class="dash-panel">
                <div class="dash-head">
                    <div>
                        <div class="dash-title">Upcoming Renewals</div>
                        <div class="dash-sub">Expiring soon by renewal window.</div>
                    </div>
                    <a href="{{ route('tenant.renewals.index') }}" class="dash-link">View All</a>
                </div>
                <div class="dash-tabs" id="renewalTabs">
                    @foreach ($renewalTabs as $key => $tab)
                        <button type="button" class="dash-tab {{ $loop->first ? 'active' : '' }}" data-tab="{{ $key }}">{{ $tab['label'] }}</button>
                    @endforeach
                </div>
                <input type="text" class="dash-search" id="renewalSearch" placeholder="Search by client or plan">
                @foreach ($renewalTabs as $key => $tab)
                    <div class="dash-tab-panel {{ $loop->first ? 'active' : '' }}" data-panel="{{ $key }}" style="margin-top:.9rem">
                        <div class="dash-list">
                            @forelse ($upcomingRenewals[$key] as $member)
                                <div class="dash-item renewal-row" data-tab-row="{{ $key }}" data-search="{{ strtolower($member->name . ' ' . ($member->plan?->name ?? $member->plan_name ?? '')) }}">
                                    <div class="dash-item-row">
                                        <div>
                                            <div class="dash-item-title">{{ $member->name }}</div>
                                            <div class="dash-item-sub">{{ $member->plan?->name ?? $member->plan_name ?? '—' }} • Expires {{ $member->expiry_date?->format('d M Y') }}</div>
                                        </div>
                                        <a href="{{ route('tenant.payments.collect', ['member_id' => $member->id]) }}" class="dash-action">Add Revenue</a>
                                    </div>
                                </div>
                            @empty
                                <div class="dash-empty">No renewals in this window.</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const DASH_REVENUE = @json($revenueChart);
const DASH_CHECKINS = @json($checkinChart);

function filterDashboardRows(inputId, selector) {
    const query = (document.getElementById(inputId)?.value || '').trim().toLowerCase();
    document.querySelectorAll(selector).forEach((row) => {
        row.style.display = !query || row.dataset.search.includes(query) ? '' : 'none';
    });
}

document.getElementById('expiredSearch')?.addEventListener('input', () => filterDashboardRows('expiredSearch', '.expired-row'));
document.getElementById('renewalSearch')?.addEventListener('input', () => filterDashboardRows('renewalSearch', '.renewal-row'));

document.querySelectorAll('#renewalTabs .dash-tab').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#renewalTabs .dash-tab').forEach((item) => item.classList.remove('active'));
        document.querySelectorAll('.dash-tab-panel').forEach((panel) => panel.classList.remove('active'));
        btn.classList.add('active');
        document.querySelector(`.dash-tab-panel[data-panel="${btn.dataset.tab}"]`)?.classList.add('active');
    });
});

if (document.getElementById('dashRevenueChart')) {
    new Chart(document.getElementById('dashRevenueChart'), {
        type: 'line',
        data: {
            labels: DASH_REVENUE.labels,
            datasets: [{
                label: 'Revenue',
                data: DASH_REVENUE.values,
                borderColor: '#0f766e',
                backgroundColor: 'rgba(15,118,110,0.16)',
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointHoverRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: (value) => '₹' + (value / 100).toFixed(0) } },
            }
        }
    });
}

if (document.getElementById('dashCheckinChart')) {
    new Chart(document.getElementById('dashCheckinChart'), {
        type: 'bar',
        data: {
            labels: DASH_CHECKINS.labels,
            datasets: [{
                label: 'Check-ins',
                data: DASH_CHECKINS.values,
                borderRadius: 10,
                backgroundColor: '#2563eb',
                maxBarThickness: 34,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        }
    });
}
</script>
</x-layouts.admin>
