<x-layouts.admin :title="$moduleTitles[$moduleKey]">
@include('tenant.assess._styles')
<style>
.as-col-3{grid-column:span 3}
@media(max-width:900px){.as-col-3{grid-column:span 6}}
@media(max-width:600px){.as-col-3{grid-column:span 12}}

.gf-results-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem}
@media(max-width:900px){.gf-results-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:480px){.gf-results-grid{grid-template-columns:1fr}}

.gf-card{background:var(--app-panel);border:1px solid var(--app-border);border-radius:1.25rem;padding:1.25rem 1.5rem}
.gf-card-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--app-text-muted)}
.gf-card-value{font-size:2rem;font-weight:800;color:var(--app-text);margin-top:.3rem;line-height:1.1}
.gf-card-sub{font-size:.82rem;color:var(--app-text-muted);margin-top:.3rem}

.gf-insight{background:color-mix(in srgb,var(--app-brand) 10%,transparent);border:1px solid color-mix(in srgb,var(--app-brand) 25%,transparent);border-radius:1rem;padding:.9rem 1.25rem;font-size:.92rem;color:var(--app-text);line-height:1.6}

.gf-section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap}
.gf-section-title{font-size:1rem;font-weight:700;color:var(--app-text)}
.gf-section-sub{font-size:.84rem;color:var(--app-text-muted);margin-top:.15rem}

.gf-workout-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1.25rem;margin-bottom:1.5rem}
@media(max-width:760px){.gf-workout-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}

.gf-field-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--app-text-muted);margin-bottom:.4rem;display:block}
.gf-field-row{display:flex;align-items:center;gap:.5rem}
.gf-pct-unit{font-size:.9rem;color:var(--app-text-muted)}
.gf-total-hint{font-size:.78rem;color:var(--app-text-muted);margin-top:.3rem}

.gf-chart-nav{display:flex;align-items:center;gap:.6rem}
.gf-nav-btn{display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border-radius:.6rem;border:1px solid var(--app-border);background:var(--app-panel-strong);color:var(--app-text);cursor:pointer;transition:opacity .15s}
.gf-nav-btn:hover{opacity:.75}
.gf-nav-btn:disabled{opacity:.35;cursor:not-allowed}
.gf-nav-label{font-size:.88rem;font-weight:600;color:var(--app-text);min-width:3.2rem;text-align:center}

.gf-legend{display:flex;align-items:center;justify-content:center;gap:1.5rem;margin-top:.75rem;flex-wrap:wrap}
.gf-legend-item{display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:var(--app-text-muted)}
.gf-legend-dot{width:1.6rem;height:3px;border-radius:2px}
</style>

<div class="as-shell">
    {{-- Header --}}
    <div class="as-head">
        <div>
            <div class="as-title">Goal Forecasting</div>
            <div class="as-sub">Estimate goal duration and plan your workout &amp; nutrition strategy week by week.</div>
        </div>
    </div>

    @include('tenant.assess._nav')
    @include('tenant.assess._member-picker', ['action' => route('tenant.assess.goal-forecasting'), 'member' => $member])

    @if (! $member)
        <div class="as-panel as-empty">Select a client above to begin goal forecasting.</div>
    @else

    {{-- ── Goal Parameters Form ──────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('tenant.assess.goal-forecasting') }}" class="as-panel">
        <input type="hidden" name="member_id"  value="{{ $member->id }}">
        <input type="hidden" name="calculate"  value="1">

        <div class="gf-section-head" style="margin-bottom:1rem">
            <div>
                <div class="gf-section-title">Goal Parameters</div>
                <div class="gf-section-sub">Enter the goal details to calculate the forecast</div>
            </div>
        </div>

        <div class="as-grid">
            <div class="as-col-3">
                <label class="as-label">Current Weight (kg)</label>
                <input class="as-input" type="number" step="0.1" min="1" name="current_weight_kg"
                       value="{{ request('current_weight_kg', data_get($latestBodyMetrics?->payload,'weight_kg')) }}"
                       placeholder="e.g. 85.0" required>
                @if ($latestBodyMetrics)
                    <div class="as-help" style="margin-top:.3rem">
                        Latest: {{ data_get($latestBodyMetrics->payload,'weight_kg') }} kg
                    </div>
                @endif
            </div>

            <div class="as-col-3">
                <label class="as-label">Goal Type</label>
                <select class="as-select" name="goal_type">
                    <option value="weight_loss" @selected(request('goal_type','weight_loss')==='weight_loss')>Weight Loss</option>
                    <option value="weight_gain" @selected(request('goal_type')==='weight_gain')>Weight Gain</option>
                    <option value="maintain"    @selected(request('goal_type')==='maintain')>Maintain Weight</option>
                </select>
            </div>

            <div class="as-col-3">
                <label class="as-label">Target Weight (kg)</label>
                <input class="as-input" type="number" step="0.1" min="1" name="target_weight_kg"
                       value="{{ request('target_weight_kg') }}"
                       placeholder="e.g. 75.0" required>
            </div>

            <div class="as-col-3">
                <label class="as-label">Weekly Rate</label>
                <select class="as-select" name="weekly_rate">
                    <option value="slow"        @selected(request('weekly_rate')==='slow')>Conservative — 0.25 kg/week</option>
                    <option value="recommended" @selected(request('weekly_rate','recommended')==='recommended')>Recommended — 0.5 kg/week</option>
                    <option value="extreme"     @selected(request('weekly_rate')==='extreme')>Aggressive — 1.0 kg/week</option>
                </select>
            </div>
        </div>

        <div class="as-actions" style="margin-top:1.25rem">
            <button class="as-btn as-btn-primary" type="submit">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 9h6M9 12h6M9 15h4"/></svg>
                Calculate Forecast
            </button>
        </div>
    </form>

    @if ($result)

    {{-- ── Forecast Results ─────────────────────────────────────────────────── --}}
    <div>
        <div class="gf-section-head" style="margin-bottom:.9rem">
            <div>
                <div class="gf-section-title">Forecast Results</div>
                <div class="gf-section-sub">Calculated timeline and target date</div>
            </div>
            <button type="button" onclick="window.print()" class="as-btn as-btn-secondary" style="gap:.5rem">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download PDF
            </button>
        </div>

        <div class="gf-results-grid">
            <div class="gf-card">
                <div class="gf-card-label">Weight Difference</div>
                <div class="gf-card-value">{{ abs($result['weight_diff_kg']) }} kg</div>
                <div class="gf-card-sub">
                    {{ $result['goal_type'] === 'weight_loss' ? 'to lose' : ($result['goal_type'] === 'weight_gain' ? 'to gain' : 'maintain') }}
                </div>
            </div>
            <div class="gf-card">
                <div class="gf-card-label">Weekly Rate</div>
                <div class="gf-card-value">{{ $result['weekly_rate_kg'] }} kg/week</div>
                <div class="gf-card-sub">{{ ucfirst($result['weekly_rate_key']) }}</div>
            </div>
            <div class="gf-card">
                <div class="gf-card-label">Duration</div>
                <div class="gf-card-value">{{ $result['duration_weeks'] }} weeks</div>
                <div class="gf-card-sub">≈ {{ $result['duration_months'] }} months</div>
            </div>
            <div class="gf-card">
                <div class="gf-card-label">Target Date</div>
                <div class="gf-card-value" style="font-size:1.3rem">
                    {{ \Carbon\Carbon::parse($result['estimated_target_date'])->format('F j, Y') }}
                </div>
            </div>
        </div>

        @php
            $rateWord = match($result['weekly_rate_key']) {
                'slow'    => 'conservative',
                'extreme' => 'aggressive',
                default   => 'recommended',
            };
            $diffAbs  = abs($result['weight_diff_kg']);
            $goalVerb = $result['goal_type'] === 'weight_loss' ? 'lose' : ($result['goal_type'] === 'weight_gain' ? 'gain' : 'maintain');
        @endphp
        <div class="gf-insight" style="margin-top:.9rem">
            At a <strong>{{ $rateWord }} pace</strong>, <strong>{{ $member->name }}</strong>
            may {{ $goalVerb }} {{ $diffAbs }} kg and reach their goal
            in ~<strong>{{ $result['duration_weeks'] }} weeks</strong>
            (around <strong>{{ \Carbon\Carbon::parse($result['estimated_target_date'])->format('F j, Y') }}</strong>).
        </div>
    </div>

    {{-- ── Forecast vs Actual Progress Chart ───────────────────────────────── --}}
    <div class="as-panel">
        <div class="gf-section-head">
            <div>
                <div class="gf-section-title">
                    <svg style="display:inline;vertical-align:middle;margin-right:.35rem" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                    Forecast vs Actual Progress
                </div>
                <div class="gf-section-sub">Compare forecasted weight trajectory with actual measurements</div>
            </div>
        </div>

        <div style="position:relative;height:300px">
            <canvas id="gfForecastChart"></canvas>
        </div>

        <div class="gf-legend">
            <div class="gf-legend-item">
                <div class="gf-legend-dot" style="background:var(--app-brand);border-top:2px dashed var(--app-brand);background:none"></div>
                Forecast
            </div>
            <div class="gf-legend-item">
                <div class="gf-legend-dot" style="background:#22c55e"></div>
                Actual
            </div>
            <div class="gf-legend-item">
                <div class="gf-legend-dot" style="background:#94a3b8;border-top:2px dashed #94a3b8;background:none"></div>
                Current Weight
            </div>
            <div class="gf-legend-item">
                <div class="gf-legend-dot" style="background:#f59e0b;border-top:2px dashed #f59e0b;background:none"></div>
                Target Weight
            </div>
        </div>

        <p style="text-align:center;font-size:.78rem;color:var(--app-text-muted);margin-top:.6rem">
            Forecast updates automatically based on actual progress every week.
        </p>
    </div>

    {{-- ── Workout vs Diet Plan ─────────────────────────────────────────────── --}}
    <div class="as-panel">
        <div class="gf-section-head">
            <div>
                <div class="gf-section-title">Workout vs Diet Plan</div>
                <div class="gf-section-sub">Weekly breakdown of calories to burn from exercise and deficit from diet. Plan your workout and nutrition strategy week by week.</div>
            </div>
        </div>

        {{-- Contribution inputs (client-side, real-time) --}}
        <div class="gf-workout-grid">
            <div>
                <label class="gf-field-label" for="gfExPct">Exercise Contribution (%)</label>
                <div class="gf-field-row">
                    <input id="gfExPct" type="number" class="as-input" min="0" max="100" value="50" style="max-width:90px">
                    <span class="gf-pct-unit">%</span>
                </div>
            </div>
            <div>
                <label class="gf-field-label" for="gfDietPct">Diet Contribution (%)</label>
                <div class="gf-field-row">
                    <input id="gfDietPct" type="number" class="as-input" min="0" max="100" value="50" style="max-width:90px">
                    <span class="gf-pct-unit">%</span>
                </div>
                <div id="gfTotalPct" class="gf-total-hint">Total: 100%</div>
            </div>
            <div>
                <label class="gf-field-label" for="gfWorkoutDays">Workout Days/Week</label>
                <input id="gfWorkoutDays" type="number" class="as-input" min="1" max="7" value="5" style="max-width:100px">
            </div>
            <div>
                <label class="gf-field-label" for="gfMeals">Meals Per Day</label>
                <input id="gfMeals" type="number" class="as-input" min="1" max="10" value="3" style="max-width:100px">
            </div>
        </div>

        {{-- Bar chart header with pagination --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem">
            <div class="gf-section-title" style="font-size:.9rem">Weekly Calorie Breakdown</div>
            <div class="gf-chart-nav">
                <button type="button" class="gf-nav-btn" id="gfPrevPage">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <span class="gf-nav-label" id="gfPageLabel">1 / 1</span>
                <button type="button" class="gf-nav-btn" id="gfNextPage">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <div style="position:relative;height:260px">
            <canvas id="gfDietChart"></canvas>
        </div>

        <div style="display:flex;align-items:center;justify-content:center;gap:1.5rem;margin-top:.75rem;flex-wrap:wrap">
            <div class="gf-legend-item">
                <div class="gf-legend-dot" style="background:#22c55e;height:10px;border-radius:3px"></div>
                Exercise
            </div>
            <div class="gf-legend-item">
                <div class="gf-legend-dot" style="background:#f59e0b;height:10px;border-radius:3px"></div>
                Diet
            </div>
        </div>

        {{-- Weekly Details Table --}}
        <div style="margin-top:1.75rem">
            <div class="gf-section-title" style="font-size:.9rem;margin-bottom:.9rem">Weekly Details</div>
            <div class="as-table-wrap">
                <table class="as-table">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Forecast Weight</th>
                            <th>Exercise Calories</th>
                            <th>Exercise/Day</th>
                            <th>Diet Deficit</th>
                            <th>Diet/Day</th>
                            <th>Diet/Meal</th>
                        </tr>
                    </thead>
                    <tbody id="gfWeeklyBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    @endif {{-- $result --}}
    @endif {{-- $member --}}
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if ($result)
(function () {
    // ── Server data ────────────────────────────────────────────────────────────
    @php
        $gfData = [
            'forecastSeries' => $result['forecast_series'],
            'actualSeries'   => $result['actual_series'],
            'currentWeight'  => $result['current_weight'],
            'targetWeight'   => $result['target_weight'],
            'weeklyRateKg'   => $result['weekly_rate_kg'],
            'durationWeeks'  => $result['duration_weeks'],
            'memberName'     => $member->name,
        ];
    @endphp
    const GF = @json($gfData);

    // ── Theme colours (read CSS vars once) ────────────────────────────────────
    const cs       = getComputedStyle(document.documentElement);
    const appText  = cs.getPropertyValue('--app-text').trim()       || '#0f172a';
    const appMuted = cs.getPropertyValue('--app-text-muted').trim() || '#64748b';
    const appBorder= cs.getPropertyValue('--app-border').trim()     || '#e2e8f0';
    const appBrand = cs.getPropertyValue('--app-brand').trim()      || '#f97316';

    const GREEN  = '#22c55e';
    const ORANGE = '#f59e0b';
    const SLATE  = '#94a3b8';

    // ── Helpers ────────────────────────────────────────────────────────────────
    function fmtDate(d) {
        if (!d) return '';
        const dt = new Date(d);
        return dt.toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' });
    }
    function fmtShortDate(d) {
        if (!d) return '';
        const dt = new Date(d);
        return dt.toLocaleDateString('en-GB', { day:'numeric', month:'short' });
    }
    function num(n) { return Math.round(n).toLocaleString(); }

    // ── Forecast vs Actual chart ───────────────────────────────────────────────
    (function buildForecastChart() {
        const forecastPts = GF.forecastSeries;
        const actualPts   = GF.actualSeries;

        // Merge all dates, sorted
        const allDates = [...new Set([
            ...forecastPts.map(p => p.date),
            ...actualPts.map(p => p.date),
        ])].sort();

        const actualMap = {};
        actualPts.forEach(p => actualMap[p.date] = p.actual_weight_kg);

        const labels        = allDates.map(fmtShortDate);
        const forecastData  = allDates.map(d => {
            const pt = forecastPts.find(p => p.date === d);
            return pt ? pt.forecasted_weight_kg : null;
        });
        const actualData = allDates.map(d => actualMap[d] ?? null);

        // Horizontal reference line data (same value for all points)
        const currentLine = allDates.map(() => GF.currentWeight);
        const targetLine  = allDates.map(() => GF.targetWeight);

        const ctx = document.getElementById('gfForecastChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Forecast',
                        data: forecastData,
                        borderColor: appBrand,
                        borderWidth: 2,
                        borderDash: [6, 4],
                        backgroundColor: 'transparent',
                        pointRadius: 0,
                        spanGaps: true,
                        tension: 0.3,
                        order: 2,
                    },
                    {
                        label: 'Actual',
                        data: actualData,
                        borderColor: GREEN,
                        borderWidth: 2.5,
                        backgroundColor: 'rgba(34,197,94,0.10)',
                        fill: true,
                        pointRadius: actualData.map(v => v !== null ? 4 : 0),
                        pointBackgroundColor: GREEN,
                        spanGaps: false,
                        tension: 0.35,
                        order: 1,
                    },
                    {
                        label: 'Current Weight',
                        data: currentLine,
                        borderColor: SLATE,
                        borderWidth: 1.5,
                        borderDash: [4, 4],
                        backgroundColor: 'transparent',
                        pointRadius: 0,
                        tension: 0,
                        order: 3,
                    },
                    {
                        label: 'Target Weight',
                        data: targetLine,
                        borderColor: ORANGE,
                        borderWidth: 1.5,
                        borderDash: [4, 4],
                        backgroundColor: 'transparent',
                        pointRadius: 0,
                        tension: 0,
                        order: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.88)',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        callbacks: {
                            label(ctx) {
                                const v = ctx.parsed.y;
                                if (v === null || v === undefined) return null;
                                return `${ctx.dataset.label}: ${v} kg`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { color: appBorder, lineWidth: .5 },
                        ticks: {
                            color: appMuted,
                            font: { size: 11 },
                            maxTicksLimit: 8,
                            maxRotation: 30,
                        },
                    },
                    y: {
                        grid: { color: appBorder, lineWidth: .5 },
                        ticks: {
                            color: appMuted,
                            font: { size: 11 },
                            callback: v => v + ' kg',
                        },
                    },
                },
            },
        });
    })();

    // ── Workout vs Diet interactive section ────────────────────────────────────
    const WEEKS_PER_PAGE = 4;
    let barPage = 0;
    let dietChart = null;

    const elExPct       = document.getElementById('gfExPct');
    const elDietPct     = document.getElementById('gfDietPct');
    const elWorkoutDays = document.getElementById('gfWorkoutDays');
    const elMeals       = document.getElementById('gfMeals');
    const elTotalPct    = document.getElementById('gfTotalPct');
    const elPrevBtn     = document.getElementById('gfPrevPage');
    const elNextBtn     = document.getElementById('gfNextPage');
    const elPageLabel   = document.getElementById('gfPageLabel');
    const elTableBody   = document.getElementById('gfWeeklyBody');

    function calcBreakdown() {
        const exPct      = Math.max(0, Math.min(100, parseFloat(elExPct.value)  || 0));
        const dietPct    = Math.max(0, Math.min(100, parseFloat(elDietPct.value) || 0));
        const wdays      = Math.max(1, Math.min(7,  parseInt(elWorkoutDays.value) || 5));
        const meals      = Math.max(1, Math.min(10, parseInt(elMeals.value)       || 3));
        const weekly     = GF.weeklyRateKg * 7700;

        const exKcal     = Math.round(weekly * exPct   / 100);
        const dietKcal   = Math.round(weekly * dietPct / 100);
        const exPerDay   = Math.round(exKcal   / wdays);
        const dietPerDay = Math.round(dietKcal / 7);
        const dietPerMeal= Math.round(dietPerDay / meals);

        const total = exPct + dietPct;
        elTotalPct.textContent = `Total: ${total}%`;
        elTotalPct.style.color = Math.abs(total - 100) > 1 ? '#ef4444' : 'var(--app-text-muted)';

        return { exKcal, dietKcal, exPerDay, dietPerDay, dietPerMeal };
    }

    function buildWeekRows(breakdown) {
        const totalWeeks  = Math.max(1, GF.durationWeeks);
        const forecastPts = GF.forecastSeries;
        let html = '';
        for (let i = 0; i < totalWeeks; i++) {
            const weekNum = i + 1;
            const pt = forecastPts[i] ?? forecastPts[forecastPts.length - 1];
            const dt = pt ? fmtDate(pt.date) : '';
            html += `<tr>
                <td style="min-width:100px">
                    <div style="font-weight:600;color:var(--app-text)">Week ${weekNum}</div>
                    <div style="font-size:.76rem;color:var(--app-text-muted)">${dt}</div>
                </td>
                <td style="font-weight:600;color:var(--app-text)">${pt ? pt.forecasted_weight_kg + ' kg' : '—'}</td>
                <td>${num(breakdown.exKcal)} kcal</td>
                <td>${num(breakdown.exPerDay)} kcal</td>
                <td>${num(breakdown.dietKcal)} kcal</td>
                <td>${num(breakdown.dietPerDay)} kcal</td>
                <td>${num(breakdown.dietPerMeal)} kcal</td>
            </tr>`;
        }
        elTableBody.innerHTML = html;
    }

    function renderBarChart(breakdown) {
        const totalWeeks = Math.max(1, GF.durationWeeks);
        const totalPages = Math.ceil(totalWeeks / WEEKS_PER_PAGE);

        if (barPage >= totalPages) barPage = totalPages - 1;
        if (barPage < 0) barPage = 0;

        const start = barPage * WEEKS_PER_PAGE;
        const end   = Math.min(start + WEEKS_PER_PAGE, totalWeeks);

        const labels = [];
        const exData = [];
        const dData  = [];
        for (let i = start; i < end; i++) {
            labels.push('Week ' + (i + 1));
            exData.push(breakdown.exKcal);
            dData.push(breakdown.dietKcal);
        }

        elPageLabel.textContent = `${barPage + 1} / ${totalPages}`;
        elPrevBtn.disabled = barPage === 0;
        elNextBtn.disabled = barPage >= totalPages - 1;

        if (!dietChart) {
            const ctx = document.getElementById('gfDietChart');
            dietChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Exercise',
                            data: exData,
                            backgroundColor: 'rgba(34,197,94,0.85)',
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                        {
                            label: 'Diet',
                            data: dData,
                            backgroundColor: 'rgba(245,158,11,0.85)',
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,0.88)',
                            titleColor: '#f8fafc',
                            bodyColor: '#cbd5e1',
                            padding: 12,
                            callbacks: {
                                label(ctx) {
                                    return `${ctx.dataset.label}: ${num(ctx.parsed.y)} kcal`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { color: appBorder, lineWidth: .5 },
                            ticks: { color: appMuted, font: { size: 12 } },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: appBorder, lineWidth: .5 },
                            ticks: {
                                color: appMuted,
                                font: { size: 11 },
                                callback: v => num(v) + ' kcal',
                            },
                        },
                    },
                },
            });
        } else {
            dietChart.data.labels = labels;
            dietChart.data.datasets[0].data = exData;
            dietChart.data.datasets[1].data = dData;
            dietChart.update();
        }
    }

    function refresh() {
        const bd = calcBreakdown();
        renderBarChart(bd);
        buildWeekRows(bd);
    }

    // Wire inputs
    [elExPct, elDietPct, elWorkoutDays, elMeals].forEach(el => el.addEventListener('input', () => { barPage = 0; refresh(); }));
    elPrevBtn.addEventListener('click', () => { barPage--; refresh(); });
    elNextBtn.addEventListener('click', () => { barPage++; refresh(); });

    // Initial render
    refresh();
})();
@endif
</script>
</x-layouts.admin>
