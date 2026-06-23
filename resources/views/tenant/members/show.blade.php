<x-layouts.admin :title="$member->name">

<style>
.mp-back { display:inline-flex; align-items:center; gap:.4rem; font-size:.82rem; font-weight:600; color:var(--app-text-muted); text-decoration:none; margin-bottom:1.25rem; transition:color .15s; }
.mp-back:hover { color:var(--app-brand); }

.mp-grid { display:grid; grid-template-columns:320px 1fr; gap:1.5rem; align-items:start; }
@media(max-width:960px){ .mp-grid{ grid-template-columns:1fr; } }

/* Profile card */
.mp-card { border:1px solid var(--app-border); border-radius:1.5rem; overflow:hidden; background:var(--app-panel); }
.mp-card-head { padding:1.5rem; display:flex; flex-direction:column; align-items:center; gap:.75rem; border-bottom:1px solid var(--app-border); }
.mp-avatar { width:4.5rem; height:4.5rem; border-radius:999px; background:var(--app-brand-soft); color:var(--app-brand); display:flex; align-items:center; justify-content:center; font-size:1.5rem; font-weight:700; flex-shrink:0; }
.mp-name { font-size:1.1rem; font-weight:700; color:var(--app-text); text-align:center; }
.mp-code { font-size:.75rem; font-weight:600; color:var(--app-text-muted); text-align:center; font-family:monospace; }
.mp-status { display:inline-flex; align-items:center; padding:.2rem .7rem; border-radius:999px; font-size:.72rem; font-weight:700; letter-spacing:.03em; }
.mp-status-active   { background:#d1fae5; color:#065f46; }
.mp-status-inactive { background:#f1f5f9; color:#64748b; }
.mp-status-expired  { background:#fee2e2; color:#991b1b; }
.mp-status-frozen   { background:#dbeafe; color:#1d4ed8; }

.mp-card-body { padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:.85rem; }
.mp-row { display:flex; align-items:flex-start; gap:.75rem; }
.mp-row-icon { flex-shrink:0; width:1.5rem; color:var(--app-text-muted); display:flex; align-items:center; justify-content:center; margin-top:.1rem; }
.mp-row-icon svg { width:1rem; height:1rem; }
.mp-row-label { font-size:.72rem; font-weight:600; color:var(--app-text-muted); text-transform:uppercase; letter-spacing:.06em; }
.mp-row-val { font-size:.88rem; color:var(--app-text); margin-top:.1rem; }

.mp-plan-chip { display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .75rem; border-radius:.75rem; font-size:.82rem; font-weight:600; border:1px solid var(--app-border); background:var(--app-panel-strong); color:var(--app-text); }
.mp-expiry-ok      { color:#065f46; }
.mp-expiry-warning { color:#b45309; }
.mp-expiry-expired { color:#991b1b; }

.mp-actions { display:flex; flex-direction:column; gap:.5rem; padding:1rem 1.5rem; border-top:1px solid var(--app-border); }
.mp-action-btn { display:flex; align-items:center; gap:.6rem; padding:.55rem .8rem; border-radius:.75rem; font-size:.83rem; font-weight:600; text-decoration:none; border:1px solid var(--app-border); color:var(--app-text-muted); background:transparent; transition:.13s; cursor:pointer; width:100%; text-align:left; }
.mp-action-btn:hover { background:var(--app-panel-strong); color:var(--app-text); border-color:var(--app-brand); }
.mp-action-btn svg { width:.95rem; height:.95rem; flex-shrink:0; }
.mp-action-primary { background:var(--app-brand); color:#fff; border-color:var(--app-brand); }
.mp-action-primary:hover { opacity:.9; color:#fff; }

/* Payment history */
.mp-hist { border:1px solid var(--app-border); border-radius:1.5rem; overflow:hidden; background:var(--app-panel); }
.mp-hist-head { padding:1rem 1.5rem; border-bottom:1px solid var(--app-border); display:flex; align-items:center; justify-content:space-between; }
.mp-hist-head h3 { font-size:.95rem; font-weight:700; color:var(--app-text); }
.mp-hist-count { font-size:.75rem; font-weight:600; color:var(--app-text-muted); background:var(--app-panel-strong); border:1px solid var(--app-border); border-radius:999px; padding:.1rem .55rem; }

.mp-badge-partial { display:inline-flex; align-items:center; padding:.1rem .45rem; border-radius:999px; font-size:.65rem; font-weight:700; background:#fef9c3; color:#854d0e; }
.mp-badge-voided  { display:inline-flex; align-items:center; padding:.1rem .45rem; border-radius:999px; font-size:.65rem; font-weight:700; background:#fee2e2; color:#991b1b; }
.mp-badge-active  { display:inline-flex; align-items:center; padding:.1rem .45rem; border-radius:999px; font-size:.65rem; font-weight:700; background:#d1fae5; color:#065f46; }

.mp-duration { display:flex; align-items:center; gap:.3rem; font-size:.75rem; color:var(--app-text-muted); white-space:nowrap; }
.mp-duration-arrow { color:var(--app-brand); font-size:.8rem; }
</style>

<a href="{{ route('tenant.members.index') }}" class="mp-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Back to Members
</a>

<div class="mp-grid">

    {{-- ── Left: profile card ───────────────────────────────────────────────── --}}
    <div class="mp-card">
        <div class="mp-card-head">
            <div class="mp-avatar">{{ $member->initials }}</div>
            <div>
                <p class="mp-name">{{ $member->name }}</p>
                <p class="mp-code">{{ $member->member_code }}</p>
            </div>
            <span class="mp-status mp-status-{{ $member->effective_status }}">
                {{ $member->status_label }}
            </span>
        </div>

        <div class="mp-card-body">
            {{-- Phone --}}
            <div class="mp-row">
                <span class="mp-row-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.62 3.38 2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.56a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                <div>
                    <p class="mp-row-label">Phone</p>
                    <p class="mp-row-val">{{ $member->phone }}</p>
                </div>
            </div>

            {{-- Email --}}
            @if($member->email)
            <div class="mp-row">
                <span class="mp-row-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span>
                <div>
                    <p class="mp-row-label">Email</p>
                    <p class="mp-row-val">{{ $member->email }}</p>
                </div>
            </div>
            @endif

            {{-- Gender / DOB --}}
            @if($member->gender || $member->dob)
            <div class="mp-row">
                <span class="mp-row-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 0 0-16 0"/></svg></span>
                <div>
                    <p class="mp-row-label">Personal</p>
                    <p class="mp-row-val">
                        @if($member->gender) {{ ucfirst($member->gender) }} @endif
                        @if($member->gender && $member->dob) · @endif
                        @if($member->dob) {{ $member->dob->format('d M Y') }} @endif
                    </p>
                </div>
            </div>
            @endif

            {{-- Branch --}}
            @if($member->branch)
            <div class="mp-row">
                <span class="mp-row-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg></span>
                <div>
                    <p class="mp-row-label">Branch</p>
                    <p class="mp-row-val">{{ $member->branch->name }}</p>
                </div>
            </div>
            @endif

            {{-- Current plan --}}
            <div class="mp-row">
                <span class="mp-row-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h6"/><path d="M9 11h6"/><path d="M9 15h4"/></svg></span>
                <div>
                    <p class="mp-row-label">Current Plan</p>
                    <p class="mp-row-val mt-1">
                        @if($member->plan_name)
                            <span class="mp-plan-chip">{{ $member->plan_name }}</span>
                        @else
                            <span style="color:var(--app-text-muted)">—</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Membership dates --}}
            @if($member->start_date || $member->expiry_date)
            <div class="mp-row">
                <span class="mp-row-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M3 11h18"/></svg></span>
                <div>
                    <p class="mp-row-label">Membership Period</p>
                    @if($member->start_date)
                        <p class="mp-row-val">{{ $member->start_date->format('d M Y') }} → @if($member->expiry_date)
                            @php
                                $daysLeft = now()->diffInDays($member->expiry_date, false);
                                $expiryClass = $daysLeft < 0 ? 'mp-expiry-expired' : ($daysLeft <= 7 ? 'mp-expiry-warning' : 'mp-expiry-ok');
                            @endphp
                            <span class="{{ $expiryClass }}">{{ $member->expiry_date->format('d M Y') }}</span>
                            <span style="font-size:.75rem;color:var(--app-text-muted)">
                                @if($daysLeft < 0) (expired {{ abs((int)$daysLeft) }}d ago)
                                @elseif($daysLeft === 0) (expires today)
                                @else ({{ (int)$daysLeft }}d left)
                                @endif
                            </span>
                        @else —
                        @endif
                        </p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Address --}}
            @if($member->address)
            <div class="mp-row">
                <span class="mp-row-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
                <div>
                    <p class="mp-row-label">Address</p>
                    <p class="mp-row-val">{{ $member->address }}</p>
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($member->notes)
            <div class="mp-row">
                <span class="mp-row-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg></span>
                <div>
                    <p class="mp-row-label">Notes</p>
                    <p class="mp-row-val">{{ $member->notes }}</p>
                </div>
            </div>
            @endif

            {{-- Joined --}}
            <div class="mp-row">
                <span class="mp-row-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg></span>
                <div>
                    <p class="mp-row-label">Member Since</p>
                    <p class="mp-row-val">{{ $member->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>

        <div class="mp-actions">
            <a href="{{ route('tenant.payments.collect', ['member_id' => $member->id]) }}" class="mp-action-btn mp-action-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M16 12h.01"/><path d="M3 9h18"/></svg>
                Collect Payment
            </a>
            <a href="{{ route('tenant.members.edit', $member) }}" class="mp-action-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/></svg>
                Edit Member
            </a>
        </div>
    </div>

    {{-- ── Right: payment history ───────────────────────────────────────────── --}}
    <div class="mp-hist">
        <div class="mp-hist-head">
            <h3>Payment History</h3>
            <span class="mp-hist-count">{{ $payments->count() }} {{ Str::plural('record', $payments->count()) }}</span>
        </div>

        @php
            // Build chained start→end dates by walking payments oldest→newest
            $chainedDates = [];
            $prevEnd = null;
            foreach ($payments->sortBy([['payment_date','asc'],['id','asc']]) as $p) {
                $payDate = $p->payment_date->toDateString();
                if ($p->plan) {
                    $start = ($prevEnd && $prevEnd > $payDate) ? $prevEnd : $payDate;
                    $end   = $p->plan->computeExpiryDate($start);
                    $chainedDates[$p->id] = ['start' => $start, 'end' => $end];
                    $prevEnd = $end;
                } else {
                    $chainedDates[$p->id] = ['start' => $payDate, 'end' => null];
                }
            }
        @endphp
        @if($payments->isEmpty())
            <div style="padding:3rem 1.5rem;text-align:center;color:var(--app-text-muted);font-size:.88rem">
                No payment records found.
            </div>
        @else
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm">
                    <thead style="background:var(--app-panel-strong)">
                        <tr>
                            <th class="text-left px-5 py-3 font-medium text-xs uppercase tracking-wide" style="color:var(--app-text-muted)">Receipt</th>
                            <th class="text-left px-4 py-3 font-medium text-xs uppercase tracking-wide" style="color:var(--app-text-muted)">Plan</th>
                            <th class="text-left px-4 py-3 font-medium text-xs uppercase tracking-wide" style="color:var(--app-text-muted)">Duration</th>
                            <th class="text-right px-4 py-3 font-medium text-xs uppercase tracking-wide" style="color:var(--app-text-muted)">Amount</th>
                            <th class="text-left px-4 py-3 font-medium text-xs uppercase tracking-wide" style="color:var(--app-text-muted)">Method</th>
                            <th class="text-left px-4 py-3 font-medium text-xs uppercase tracking-wide" style="color:var(--app-text-muted)">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--app-border)]">
                        @foreach($payments as $payment)
                            @php
                                $dates     = $chainedDates[$payment->id] ?? ['start' => $payment->payment_date->toDateString(), 'end' => null];
                                $startDate = \Carbon\Carbon::parse($dates['start']);
                                $endDate   = $dates['end'] ? \Carbon\Carbon::parse($dates['end']) : null;
                            @endphp
                            <tr style="background:{{ $payment->status === 'voided' ? 'var(--app-panel-strong)' : 'var(--app-panel)' }}">
                                <td class="px-5 py-3">
                                    <a href="{{ route('tenant.payments.receipt', $payment) }}"
                                       class="font-mono text-xs font-semibold hover:underline"
                                       style="color:var(--app-brand)">
                                        {{ $payment->receipt_number }}
                                    </a>
                                    <div class="text-xs mt-0.5" style="color:var(--app-text-muted)">{{ $payment->payment_date->format('d M Y') }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs" style="color:var(--app-text-muted)">
                                    {{ $payment->plan?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($endDate)
                                        <div class="mp-duration">
                                            <span>{{ $startDate->format('d M Y') }}</span>
                                            <span class="mp-duration-arrow">→</span>
                                            <span>{{ $endDate->format('d M Y') }}</span>
                                        </div>
                                        <div class="text-xs mt-0.5" style="color:var(--app-text-muted)">{{ $payment->plan->duration_label }}</div>
                                    @else
                                        <span class="text-xs" style="color:var(--app-text-muted)">{{ $startDate->format('d M Y') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-semibold" style="color:var(--app-text)">₹{{ number_format($payment->paid_paise / 100, 0) }}</div>
                                    @if($payment->is_partial && $payment->due_paise > 0)
                                        <div class="text-xs" style="color:#ea580c">₹{{ number_format($payment->due_paise / 100, 0) }} due</div>
                                    @elseif($payment->gst_paise > 0)
                                        <div class="text-xs" style="color:var(--app-text-muted)">+₹{{ number_format($payment->gst_paise / 100, 0) }} GST</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs" style="color:var(--app-text-muted)">
                                    {{ ucfirst($payment->method) }}
                                    @if($payment->reference)
                                        <div class="font-mono" style="font-size:.68rem">{{ $payment->reference }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($payment->status === 'voided')
                                        <span class="mp-badge-voided">Voided</span>
                                    @elseif($payment->is_partial && $payment->due_paise > 0)
                                        <span class="mp-badge-partial">Partial</span>
                                    @else
                                        <span class="mp-badge-active">Paid</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('tenant.payments.receipt', $payment) }}"
                                       class="text-xs px-2.5 py-1 rounded-lg border"
                                       style="border-color:var(--app-border);color:var(--app-text-muted)">
                                        Receipt
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

</x-layouts.admin>
