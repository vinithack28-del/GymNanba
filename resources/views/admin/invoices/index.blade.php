<x-layouts.admin
    title="Invoices & Payments"
    eyebrow="Finance"
    heading="Invoices & Payments"
    subheading="Process renewals, record part payments, and review collection history."
>

@push('styles')
<style>
.inv-shell{display:flex;flex-direction:column;gap:1.25rem}
.inv-panel{background:var(--app-panel);border:1px solid var(--app-border);border-radius:1.5rem;padding:1.5rem}
.inv-panel-head{font-size:.82rem;font-weight:700;color:var(--app-text);text-transform:uppercase;letter-spacing:.07em;margin-bottom:1rem}
.inv-label{display:block;font-size:.75rem;font-weight:600;color:var(--app-text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem}
.inv-input,.inv-select{width:100%;border:1px solid var(--app-border);background:var(--app-panel-strong);color:var(--app-text);border-radius:.9rem;padding:.72rem 1rem;font-size:.88rem;outline:none}
.inv-input:focus,.inv-select:focus{border-color:var(--app-brand)}
.inv-btn-primary{display:inline-flex;align-items:center;justify-content:center;border-radius:.95rem;padding:.72rem 1.25rem;font-size:.88rem;font-weight:700;cursor:pointer;background:var(--app-brand);color:#fff;border:none;transition:opacity .15s}
.inv-btn-primary:hover{opacity:.88}
.inv-btn-secondary{display:inline-flex;align-items:center;justify-content:center;border-radius:.95rem;padding:.72rem 1.25rem;font-size:.88rem;font-weight:700;text-decoration:none;background:var(--app-panel-strong);color:var(--app-text);border:1px solid var(--app-border);transition:opacity .15s}
.inv-table-wrap{overflow-x:auto}
.inv-table{width:100%;border-collapse:collapse;font-size:.88rem}
.inv-table th{padding:.75rem 1rem;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--app-text-muted);background:var(--app-panel-strong);text-align:left;white-space:nowrap}
.inv-table td{padding:.85rem 1rem;border-top:1px solid var(--app-border);color:var(--app-text);vertical-align:middle}
.inv-table tr:hover td{background:color-mix(in srgb,var(--app-brand) 4%,transparent)}
.inv-badge{display:inline-flex;align-items:center;padding:.18rem .55rem;border-radius:999px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
.inv-badge-green{background:color-mix(in srgb,#22c55e 15%,transparent);color:#16a34a}
.inv-badge-amber{background:color-mix(in srgb,#f59e0b 15%,transparent);color:#b45309}
.inv-badge-red{background:color-mix(in srgb,#ef4444 15%,transparent);color:#dc2626}
.inv-badge-sky{background:color-mix(in srgb,#38bdf8 15%,transparent);color:#0284c7}
.inv-badge-slate{background:color-mix(in srgb,#94a3b8 15%,transparent);color:#64748b}
.inv-info-strip{border:1px solid var(--app-border);background:var(--app-panel-strong);border-radius:.9rem;padding:.65rem 1rem;font-size:.78rem;color:var(--app-text-muted)}
.inv-price-bar{border:1px solid var(--app-border);background:var(--app-panel-strong);border-radius:.9rem;padding:.9rem 1rem}
.inv-price-row{display:flex;justify-content:space-between;font-size:.8rem;color:var(--app-text-muted);margin-bottom:.4rem}
.inv-price-row:last-child{margin-bottom:0}
.inv-due-card{border:1px solid var(--app-border);background:var(--app-panel-strong);border-radius:1.2rem;padding:1rem;cursor:pointer;transition:border-color .15s}
.inv-due-card:hover{border-color:var(--app-brand)}
.inv-muted{color:var(--app-text-muted)}
.split-row .inv-label{margin-bottom:.25rem}
[data-theme='light'] .inv-badge-green{background:#dcfce7;color:#166534}
[data-theme='light'] .inv-badge-amber{background:#fef3c7;color:#92400e}
[data-theme='light'] .inv-badge-red{background:#fee2e2;color:#991b1b}
[data-theme='light'] .inv-badge-sky{background:#e0f2fe;color:#0369a1}
[data-theme='light'] .inv-badge-slate{background:#f1f5f9;color:#475569}
</style>
@endpush

<div class="inv-shell">

    {{-- Tab bar --}}
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        @foreach (['renewal_due' => 'Renewal Due', 'history' => 'Payment History'] as $key => $label)
            <a href="{{ route('admin.invoices.index', ['tab' => $key]) }}"
               style="display:inline-flex;align-items:center;padding:.6rem 1.1rem;border-radius:999px;border:1px solid var(--app-border);font-size:.85rem;font-weight:700;text-decoration:none;transition:background .15s;
                      {{ $tab === $key ? 'background:var(--app-brand);border-color:var(--app-brand);color:#fff' : 'background:var(--app-panel);color:var(--app-text)' }}">
                {{ $label }}
                @if ($key === 'renewal_due' && $renewalsDue->count())
                    <span style="margin-left:.5rem;background:rgba(0,0,0,.2);border-radius:999px;padding:.05rem .45rem;font-size:.72rem">{{ $renewalsDue->count() }}</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- ══ RENEWAL DUE ══════════════════════════════════════════════════════ --}}
    @if ($tab === 'renewal_due')
    <div style="display:grid;gap:1.25rem;grid-template-columns:1fr 1.4fr">

        {{-- Renewal form --}}
        <form method="POST" action="{{ route('admin.invoices.renewals.store') }}" id="renewal-form" class="inv-panel" style="display:flex;flex-direction:column;gap:1rem;align-self:start">
            @csrf
            <div>
                <p class="inv-panel-head" style="margin-bottom:.2rem">Process Renewal</p>
                <p class="inv-muted" style="font-size:.8rem">Choose a tenant, pick a plan, enter amount. Part payments allowed.</p>
            </div>

            {{-- Searchable tenant picker --}}
            <div style="position:relative" id="tenant-picker-wrap">
                <label class="inv-label">Tenant</label>
                <input type="hidden" name="tenant_id" id="renewal-tenant-id" required>
                <input type="text" id="renewal-tenant-search" class="inv-input" autocomplete="off"
                       placeholder="Type to search gym name…"
                       oninput="filterTenants(this.value)"
                       onfocus="openTenantDropdown()"
                       onkeydown="tenantKeyNav(event)"
                       style="padding-right:2.2rem">
                <span style="position:absolute;right:.9rem;top:2.35rem;color:var(--app-text-muted);pointer-events:none;font-size:.85rem">▾</span>
                <div id="tenant-dropdown"
                     style="display:none;position:absolute;top:100%;left:0;right:0;z-index:99;
                            background:var(--app-panel);border:1px solid var(--app-border);
                            border-radius:1rem;box-shadow:0 8px 24px rgba(0,0,0,.12);
                            max-height:220px;overflow-y:auto;margin-top:.25rem">
                </div>
                {{-- Data store: all tenants, enriched with subscription info where due --}}
                @php
                    $renewalsDueMap = $renewalsDue->keyBy('id');
                    $tenantPickerData = $tenants->map(function ($t) use ($renewalsDueMap) {
                        $rt  = $renewalsDueMap->get($t->id);
                        $sub = $rt?->_sub;
                        return [
                            'id'        => $t->id,
                            'name'      => $t->gym_name,
                            'plan'      => $sub?->plan_id,
                            'planPrice' => $sub?->plan?->price_paise ?? 0,
                            'balance'   => $rt?->_balance_paise ?? 0,
                            'expiry'    => $sub?->end_date?->format('d M Y') ?? $sub?->trial_end_date?->format('d M Y') ?? '—',
                            'status'    => $sub?->status ?? '',
                        ];
                    })->values();
                @endphp
                <script id="tenant-data" type="application/json">{!! json_encode($tenantPickerData) !!}</script>
            </div>

            <div id="renewal-info" class="inv-info-strip" style="display:none"></div>

            <div>
                <label class="inv-label">Plan</label>
                <select name="plan_id" id="renewal-plan" class="inv-select" required onchange="onPlanChange(this)">
                    <option value="">Select plan…</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" data-price="{{ $plan->price_paise }}">
                            {{ $plan->name }} — {{ $plan->billing_cycle }} — Rs. {{ number_format($plan->price_paise / 100, 0) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="price-info" class="inv-price-bar" style="display:none">
                <div class="inv-price-row"><span>Plan price</span><span id="plan-price-display">—</span></div>
                <div class="inv-price-row"><span>Paying now</span><span id="paying-now-display">—</span></div>
                <div class="inv-price-row" style="font-weight:700;color:var(--app-text)"><span>Balance due</span><span id="balance-display" style="color:#b45309">—</span></div>
                <p id="full-paid-note" style="display:none;font-size:.78rem;color:#16a34a;margin-top:.5rem">✓ Full payment — subscription will be activated.</p>
                <p id="part-paid-note" style="display:none;font-size:.78rem;color:#b45309;margin-top:.5rem">⚠ Part payment — subscription will be marked partial.</p>
            </div>

            {{-- Split payment rows --}}
            <div>
                <label class="inv-label">Payment</label>
                <div id="renewal-splits-container" style="display:flex;flex-direction:column;gap:.5rem">
                    <div class="split-row" style="display:grid;grid-template-columns:1.1fr 1fr 1.2fr auto;gap:.5rem;align-items:end">
                        <div>
                            <label class="inv-label" style="font-size:.7rem">Method</label>
                            <select name="splits[0][method]" class="inv-select split-method" required>
                                @foreach (['Cash', 'Bank transfer', 'UPI', 'Cheque'] as $m)<option>{{ $m }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="inv-label" style="font-size:.7rem">Amount (₹)</label>
                            <input type="number" step="0.01" min="0.01" name="splits[0][amount]" class="inv-input split-amount" required placeholder="0.00" oninput="updateSplitBalance('renewal')">
                        </div>
                        <div>
                            <label class="inv-label" style="font-size:.7rem">Reference</label>
                            <input name="splits[0][reference]" class="inv-input split-ref" placeholder="UPI ID / cheque no.">
                        </div>
                        <div style="padding-bottom:.05rem">
                            <button type="button" onclick="removeSplit(this,'renewal-splits-container','renewal')"
                                style="height:2.75rem;width:2.75rem;border-radius:.9rem;border:1px solid var(--app-border);background:var(--app-panel-strong);color:#dc2626;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center"
                                title="Remove">✕</button>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addSplit('renewal-splits-container','renewal')"
                    style="margin-top:.5rem;width:100%;padding:.6rem;border:1px dashed var(--app-border);background:transparent;color:var(--app-text-muted);border-radius:.9rem;cursor:pointer;font-size:.82rem;font-weight:600">
                    + Add Payment Method
                </button>
            </div>

            <div>
                <label class="inv-label">Date</label>
                <input type="date" name="paid_at" value="{{ now()->toDateString() }}" class="inv-input" required>
            </div>
            <div>
                <label class="inv-label">Notes</label>
                <input name="notes" class="inv-input" placeholder="Optional">
            </div>
            <button type="submit" class="inv-btn-primary" style="width:100%">Process Renewal</button>
        </form>

        {{-- Due list --}}
        <div class="inv-panel">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
                <p class="inv-panel-head" style="margin-bottom:0">Tenants Due for Renewal</p>
                <span class="inv-badge inv-badge-amber">{{ $renewalsDue->count() }}</span>
            </div>

            @if ($renewalsDue->isEmpty())
                <p class="inv-muted" style="text-align:center;padding:2rem 0;font-size:.88rem">No renewals due. All subscriptions are current.</p>
            @else
                <div style="display:flex;flex-direction:column;gap:.75rem">
                    @foreach ($renewalsDue as $t)
                        @php
                            $sub = $t->_sub;
                            $isExpired = in_array($sub?->status, ['expired', 'trial_ended']);
                            $expDate = $sub?->end_date ?? $sub?->trial_end_date;
                            $daysLeft = $expDate ? (int) now()->diffInDays($expDate, false) : null;
                        @endphp
                        <div class="inv-due-card" onclick="selectTenant({{ $t->id }}, {{ $sub?->plan_id ?? 'null' }})">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem">
                                <div>
                                    <p style="font-size:.9rem;font-weight:700;color:var(--app-text)">{{ $t->gym_name }}</p>
                                    <p class="inv-muted" style="font-size:.78rem;margin-top:.15rem">{{ $sub?->plan?->name ?? '—' }}</p>
                                </div>
                                @if ($isExpired || ($daysLeft !== null && $daysLeft <= 0))
                                    <span class="inv-badge inv-badge-red">Expired</span>
                                @elseif ($daysLeft !== null && $daysLeft <= 7)
                                    <span class="inv-badge inv-badge-amber">{{ $daysLeft }}d left</span>
                                @else
                                    <span class="inv-badge inv-badge-sky">{{ $daysLeft }}d left</span>
                                @endif
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-top:.6rem;font-size:.78rem">
                                <span class="inv-muted">Expires: {{ $expDate?->format('d M Y') ?? '—' }}</span>
                                @if ($t->_balance_paise > 0)
                                    <span style="font-weight:700;color:#b45309">Balance: Rs. {{ number_format($t->_balance_paise / 100, 0) }}</span>
                                @else
                                    <span class="inv-muted">Rs. {{ number_format(($sub?->plan?->price_paise ?? 0) / 100, 0) }} / {{ $sub?->plan?->billing_cycle ?? '—' }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
    @endif

    {{-- ══ HISTORY ══════════════════════════════════════════════════════════ --}}
    @if ($tab === 'history')

        {{-- Pending part-payments panel --}}
        @php $partialSubs = $payments->filter(fn($p) => $p->subscription?->status === 'partial')->pluck('subscription')->unique('id')->filter(); @endphp
        @if ($partialSubs->isNotEmpty())
        <div class="inv-panel" style="border-color:color-mix(in srgb,#f59e0b 35%,transparent)">
            <p class="inv-panel-head" style="color:#b45309">Pending Part Payments</p>
            <form method="POST" action="{{ route('admin.invoices.part-payment.store') }}"
                  style="display:grid;grid-template-columns:repeat(4,1fr);gap:.85rem;align-items:end">
                @csrf
                <div>
                    <label class="inv-label">Subscription</label>
                    <select name="subscription_id" class="inv-select" required>
                        @foreach ($partialSubs as $ps)
                            @php $paid = $payments->where('subscription_id', $ps->id)->sum('amount_paise'); @endphp
                            <option value="{{ $ps->id }}">{{ $ps->tenant?->gym_name }} — {{ $ps->plan?->name }} (Rs. {{ number_format(max(0, $ps->price_paise - $paid) / 100, 0) }} due)</option>
                        @endforeach
                    </select>
                </div>
                <div style="grid-column:span 4">
                    <label class="inv-label">Payment</label>
                    <div id="part-splits-container" style="display:flex;flex-direction:column;gap:.5rem">
                        <div class="split-row" style="display:grid;grid-template-columns:1.1fr 1fr 1.2fr auto;gap:.5rem;align-items:end">
                            <div>
                                <label class="inv-label" style="font-size:.7rem">Method</label>
                                <select name="splits[0][method]" class="inv-select split-method" required>
                                    @foreach (['Cash', 'Bank transfer', 'UPI', 'Cheque'] as $m)<option>{{ $m }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="inv-label" style="font-size:.7rem">Amount (₹)</label>
                                <input type="number" step="0.01" min="0.01" name="splits[0][amount]" class="inv-input split-amount" required placeholder="0.00">
                            </div>
                            <div>
                                <label class="inv-label" style="font-size:.7rem">Reference</label>
                                <input name="splits[0][reference]" class="inv-input split-ref" placeholder="UPI ID / cheque no.">
                            </div>
                            <div style="padding-bottom:.05rem">
                                <button type="button" onclick="removeSplit(this,'part-splits-container','part')"
                                    style="height:2.75rem;width:2.75rem;border-radius:.9rem;border:1px solid var(--app-border);background:var(--app-panel-strong);color:#dc2626;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center"
                                    title="Remove">✕</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addSplit('part-splits-container','part')"
                        style="margin-top:.5rem;width:100%;padding:.55rem;border:1px dashed var(--app-border);background:transparent;color:var(--app-text-muted);border-radius:.9rem;cursor:pointer;font-size:.82rem;font-weight:600">
                        + Add Payment Method
                    </button>
                </div>
                <div>
                    <label class="inv-label">Date</label>
                    <input type="date" name="paid_at" value="{{ now()->toDateString() }}" class="inv-input" required>
                </div>
                <div style="grid-column:span 3">
                    <label class="inv-label">Notes</label>
                    <input name="notes" class="inv-input">
                </div>
                <div style="grid-column:span 4">
                    <button type="submit" class="inv-btn-primary">Record Part Payment</button>
                </div>
            </form>
        </div>
        @endif

        {{-- History table --}}
        <div class="inv-panel" style="padding:0;overflow:hidden">
            <div style="padding:1.1rem 1.4rem;border-bottom:1px solid var(--app-border)">
                <p class="inv-panel-head" style="margin-bottom:0">Payment History</p>
            </div>
            <div class="inv-table-wrap">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>Gym</th>
                            <th>Plan</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td style="font-weight:600">{{ $payment->tenant?->gym_name }}</td>
                                <td class="inv-muted">{{ $payment->subscription?->plan?->name ?? '—' }}</td>
                                <td>
                                    @php
                                        $bc = match($payment->payment_type) {
                                            'new'          => 'inv-badge-sky',
                                            'renewal'      => 'inv-badge-green',
                                            'part_payment' => 'inv-badge-amber',
                                            default        => 'inv-badge-slate',
                                        };
                                    @endphp
                                    <span class="inv-badge {{ $bc }}">{{ str_replace('_', ' ', $payment->payment_type) }}</span>
                                </td>
                                <td style="font-weight:700">Rs. {{ number_format($payment->amount_paise / 100, 2) }}</td>
                                <td class="inv-muted">{{ $payment->payment_method }}</td>
                                <td class="inv-muted" style="font-family:monospace;font-size:.78rem">{{ $payment->transaction_ref ?: '—' }}</td>
                                <td class="inv-muted">{{ $payment->paid_at?->format('d M Y') }}</td>
                                <td class="inv-muted" style="font-size:.8rem">{{ $payment->admin?->name ?? 'System' }}</td>
                            </tr>
                            @if ($payment->notes)
                            <tr>
                                <td colspan="8" style="padding:.2rem 1rem .75rem;border-top:none;font-size:.78rem;color:var(--app-text-muted)">↳ {{ $payment->notes }}</td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" style="text-align:center;padding:2.5rem;color:var(--app-text-muted)">No payments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($payments->isNotEmpty())
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
                    background:var(--app-panel);border:1px solid var(--app-border);border-radius:1.5rem;padding:.75rem 1.25rem">
            <p style="font-size:.78rem;color:var(--app-text-muted)">
                Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ number_format($payments->total()) }} records
            </p>
            {{ $payments->appends(['tab' => 'history'])->links() }}
        </div>
        @endif

    @endif

</div>

<script>
// ── Split row management ──────────────────────────────────────────────────

function addSplit(containerId, formKey) {
    const container = document.getElementById(containerId);
    const rows = container.querySelectorAll('.split-row');
    const idx  = rows.length;
    const tpl  = rows[0].cloneNode(true);

    // Update indices in name attributes
    tpl.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${idx}]`);
        if (el.tagName === 'INPUT') el.value = '';
    });

    // Wire oninput on amount if it was on the first row
    const amtEl = tpl.querySelector('.split-amount');
    if (amtEl && formKey === 'renewal') {
        amtEl.setAttribute('oninput', `updateSplitBalance('renewal')`);
    }

    container.appendChild(tpl);
    renumberSplits(containerId);
}

function removeSplit(btn, containerId, formKey) {
    const container = document.getElementById(containerId);
    const rows = container.querySelectorAll('.split-row');
    if (rows.length <= 1) return; // keep at least one row
    btn.closest('.split-row').remove();
    renumberSplits(containerId);
    if (formKey === 'renewal') updateSplitBalance('renewal');
}

function renumberSplits(containerId) {
    document.getElementById(containerId).querySelectorAll('.split-row').forEach((row, i) => {
        row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
        });
    });
}

// ── Tenant typeahead ──────────────────────────────────────────────────────

const _tenants = JSON.parse(document.getElementById('tenant-data')?.textContent || '[]');
let _tenantFocusIdx = -1;

function renderTenantDropdown(items) {
    const dd = document.getElementById('tenant-dropdown');
    if (!items.length) { dd.style.display = 'none'; return; }
    dd.innerHTML = items.map((t, i) =>
        `<div class="td-item" data-idx="${i}" data-id="${t.id}"
              style="padding:.65rem 1rem;cursor:pointer;font-size:.88rem;border-bottom:1px solid var(--app-border)"
              onmousedown="pickTenant(${t.id})"
              onmouseenter="highlightTenantItem(this)">
            <span style="font-weight:600;color:var(--app-text)">${t.name}</span>
            ${t.status ? `<span style="margin-left:.5rem;font-size:.72rem;color:var(--app-text-muted);text-transform:uppercase">${t.status}</span>` : ''}
         </div>`
    ).join('');
    dd.lastElementChild.style.borderBottom = 'none';
    dd.style.display = 'block';
    _tenantFocusIdx = -1;
}

function filterTenants(q) {
    const items = q.trim()
        ? _tenants.filter(t => t.name.toLowerCase().includes(q.toLowerCase()))
        : _tenants;
    renderTenantDropdown(items);
}

function openTenantDropdown() {
    const q = document.getElementById('renewal-tenant-search').value;
    filterTenants(q || '');
}

function closeTenantDropdown() {
    document.getElementById('tenant-dropdown').style.display = 'none';
}

function highlightTenantItem(el) {
    document.querySelectorAll('#tenant-dropdown .td-item').forEach(e => e.style.background = '');
    el.style.background = 'color-mix(in srgb,var(--app-brand) 8%,transparent)';
    _tenantFocusIdx = parseInt(el.dataset.idx);
}

function tenantKeyNav(e) {
    const items = document.querySelectorAll('#tenant-dropdown .td-item');
    if (!items.length) return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        _tenantFocusIdx = Math.min(_tenantFocusIdx + 1, items.length - 1);
        items.forEach((el, i) => el.style.background = i === _tenantFocusIdx ? 'color-mix(in srgb,var(--app-brand) 8%,transparent)' : '');
        items[_tenantFocusIdx]?.scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        _tenantFocusIdx = Math.max(_tenantFocusIdx - 1, 0);
        items.forEach((el, i) => el.style.background = i === _tenantFocusIdx ? 'color-mix(in srgb,var(--app-brand) 8%,transparent)' : '');
        items[_tenantFocusIdx]?.scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter' && _tenantFocusIdx >= 0) {
        e.preventDefault();
        pickTenant(parseInt(items[_tenantFocusIdx].dataset.id));
    } else if (e.key === 'Escape') {
        closeTenantDropdown();
    }
}

function pickTenant(id) {
    const t = _tenants.find(t => t.id === id);
    if (!t) return;
    document.getElementById('renewal-tenant-id').value = id;
    document.getElementById('renewal-tenant-search').value = t.name;
    closeTenantDropdown();
    applyTenantData(t);
}

function applyTenantData(t) {
    const info = document.getElementById('renewal-info');
    let msg = `Expiry: ${t.expiry} · Status: ${t.status || '—'}`;
    if (t.balance > 0) msg += ` · Balance due: Rs. ${(t.balance / 100).toFixed(0)}`;
    info.textContent = msg;
    info.style.display = 'block';
    if (t.plan) {
        const ps = document.getElementById('renewal-plan');
        ps.value = t.plan;
        onPlanChange(ps);
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', e => {
    if (!document.getElementById('tenant-picker-wrap')?.contains(e.target)) {
        closeTenantDropdown();
    }
});

// ── Renewal form helpers ──────────────────────────────────────────────────

function selectTenant(tenantId, planId) {
    const t = _tenants.find(t => t.id === tenantId);
    if (!t) return;
    pickTenant(tenantId);
    document.getElementById('renewal-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function onPlanChange(sel) {
    const opt   = sel.options[sel.selectedIndex];
    const price = parseInt(opt?.dataset.price || 0);
    const pi    = document.getElementById('price-info');
    if (!price) { pi.style.display = 'none'; return; }
    pi.style.display = 'block';
    document.getElementById('plan-price-display').textContent = `Rs. ${(price / 100).toFixed(0)}`;
    // Auto-fill first split row amount if empty
    const firstAmt = document.querySelector('#renewal-splits-container .split-amount');
    if (firstAmt && !firstAmt.value) firstAmt.value = (price / 100).toFixed(2);
    updateSplitBalance('renewal');
}

function updateSplitBalance(formKey) {
    if (formKey !== 'renewal') return;
    const ps    = document.getElementById('renewal-plan');
    const price = parseInt(ps?.options[ps?.selectedIndex]?.dataset.price || 0);
    if (!price) return;

    let totalPaise = 0;
    document.querySelectorAll('#renewal-splits-container .split-amount').forEach(inp => {
        totalPaise += Math.round(parseFloat(inp.value || 0) * 100);
    });

    const balance = Math.max(0, price - totalPaise);
    document.getElementById('paying-now-display').textContent = `Rs. ${(totalPaise / 100).toFixed(0)}`;
    document.getElementById('balance-display').textContent    = `Rs. ${(balance / 100).toFixed(0)}`;
    document.getElementById('full-paid-note').style.display = (balance === 0 && totalPaise > 0) ? 'block' : 'none';
    document.getElementById('part-paid-note').style.display = (balance > 0 && totalPaise > 0) ? 'block' : 'none';
}
</script>

</x-layouts.admin>
