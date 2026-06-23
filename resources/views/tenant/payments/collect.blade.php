<x-layouts.admin :title="__('payments.nav.collect')">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('payments.nav.collect') }}</h1>
        <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('payments.collect.subtitle') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('tenant.payments.history') }}"
           class="px-3 py-1.5 text-sm rounded border"
           style="border-color:var(--app-border);color:var(--app-text-muted)">{{ __('payments.nav.history') }}</a>
        <a href="{{ route('tenant.payments.history', ['tab' => 'dues']) }}"
           class="px-3 py-1.5 text-sm rounded border"
           style="border-color:var(--app-border);color:var(--app-text-muted)">{{ __('payments.nav.dues') }}</a>
    </div>
</div>

<style>
/* ── Collect fee page ─────────────────────────────────────── */
.pm-field label { display:block; font-size:.75rem; font-weight:600; margin-bottom:.3rem; color:var(--app-text-muted); }
.pm-field input, .pm-field select {
    width:100%; padding:.45rem .75rem; border:1px solid var(--app-border); border-radius:.6rem;
    font-size:.85rem; background:var(--app-panel-strong); color:var(--app-text); outline:none;
}
.pm-field input:focus, .pm-field select:focus { border-color:var(--app-brand); }

/* Split rows */
.split-row {
    display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:.5rem; align-items:end;
    padding:.75rem; border:1px solid var(--app-border); border-radius:.75rem;
    background:var(--app-panel-strong);
}
@media(max-width:640px){ .split-row{ grid-template-columns:1fr 1fr; } }
.split-row-remove {
    border:none; background:transparent; color:var(--app-text-muted); cursor:pointer;
    padding:.4rem; border-radius:.4rem; font-size:1rem; line-height:1;
}
.split-row-remove:hover { background:rgba(239,68,68,.1); color:#ef4444; }
.pm-add-split {
    display:inline-flex; align-items:center; gap:.3rem; border:1px dashed var(--app-border);
    border-radius:.6rem; padding:.38rem .8rem; font-size:.8rem; font-weight:600;
    color:var(--app-text-muted); background:transparent; cursor:pointer;
    transition:.15s;
}
.pm-add-split:hover { border-color:var(--app-brand); color:var(--app-brand); }

/* Partial / due section */
.pm-due-section {
    border:1px solid rgba(234,88,12,.35); border-radius:.9rem;
    padding:1rem 1.1rem; background:rgba(234,88,12,.05);
}
.pm-due-title { font-size:.82rem; font-weight:700; color:#ea580c; margin-bottom:.6rem; }
</style>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- Left: Member search + form --}}
    <div class="lg:col-span-3 space-y-4">

        {{-- Member search --}}
        <div class="rounded-xl p-5" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <h2 class="font-medium mb-3" style="color:var(--app-text)">{{ __('payments.collect.member') }}</h2>
            <div class="relative">
                <input id="pmSearch" type="text"
                       placeholder="{{ __('payments.collect.search_placeholder') }}"
                       autocomplete="off" oninput="pmDoSearch(this.value)"
                       class="w-full px-3 py-2 rounded-lg border text-sm"
                       style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                <ul id="pmResults"
                    class="absolute z-30 w-full mt-1 rounded-lg border shadow-lg hidden"
                    style="background:var(--app-panel);border-color:var(--app-border)"></ul>
            </div>
            <div id="pmCard" class="hidden mt-3 rounded-lg p-3" style="background:var(--app-panel-strong);border:1px solid var(--app-border)">
                <div class="flex items-start justify-between">
                    <div>
                        <p id="pmName" class="font-medium text-sm" style="color:var(--app-text)"></p>
                        <p id="pmMeta" class="text-xs mt-0.5" style="color:var(--app-text-muted)"></p>
                    </div>
                    <div id="pmBalance" class="text-sm font-semibold"></div>
                </div>
                <button type="button" onclick="pmClear()" class="text-xs mt-2" style="color:var(--app-text-muted)">
                    ✕ {{ __('payments.collect.change_member') }}
                </button>
            </div>
            <div id="pmDueAlert" class="hidden mt-3 rounded-lg px-3 py-2 text-sm"
                 style="background:rgba(234,88,12,.08);border:1px solid rgba(234,88,12,.28);color:#c2410c"></div>
        </div>

        {{-- Payment form --}}
        <form id="pmForm" action="{{ route('tenant.payments.store') }}" method="POST"
              class="rounded-xl p-5 space-y-5" style="background:var(--app-panel);border:1px solid var(--app-border)">
            @csrf
            <input type="hidden" name="member_id" id="pmMemberId">

            <h2 class="font-medium" style="color:var(--app-text)">{{ __('payments.collect.payment_details') }}</h2>

            <div class="grid grid-cols-2 gap-4">
                {{-- Branch --}}
                <div class="pm-field">
                    <label>{{ __('common.branch') }}</label>
                    <select name="branch_id" id="pmBranchId" required>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id', $selectedBranchId) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Plan --}}
                <div class="pm-field">
                    <label>{{ __('payments.collect.plan') }}</label>
                        <select name="plan_id" id="pmPlanId" onchange="pmPlanChanged(this)">
                            <option value="">— {{ __('payments.collect.no_plan') }} —</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}"
                                    data-price="{{ $plan->total_price_paise }}"
                                    data-base-price="{{ $plan->price_paise }}"
                                    data-gst="{{ $plan->gst_applicable ? $plan->gst_rate : 0 }}"
                                    data-gst-amount="{{ $plan->gst_amount_paise }}">
                                {{ $plan->name }} (₹{{ number_format($plan->total_price_paise / 100, 2) }})
                                @if($plan->gst_amount_paise > 0)
                                    incl. GST
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- Amount --}}
                <div class="pm-field">
                    <label>{{ __('payments.collect.amount') }} (₹) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="pmAmount" step="0.01" min="0.01" required
                           oninput="pmRecalc()" placeholder="0.00">
                    @error('amount')<p class="text-xs mt-1 text-red-500">{{ $message }}</p>@enderror
                </div>
                {{-- Payment date --}}
                <div class="pm-field">
                    <label>{{ __('payments.collect.payment_date') }}</label>
                    <input type="date" name="payment_date" required value="{{ old('payment_date', today()->toDateString()) }}">
                </div>
            </div>

            {{-- ── Payment splits ─────────────────────────────────────────── --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-semibold" style="color:var(--app-text-muted)">PAYMENT METHOD(S)</label>
                    <button type="button" class="pm-add-split" onclick="pmAddSplit()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M12 5v14M5 12h14"/></svg>
                        Add Method
                    </button>
                </div>

                <div id="pm-splits" class="space-y-2">
                    {{-- JS renders rows here --}}
                </div>

                @error('splits')<p class="text-xs mt-1 text-red-500">{{ $message }}</p>@enderror
                @error('splits.*.method')<p class="text-xs mt-1 text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Collected vs total indicator --}}
            <div id="pm-collected-bar" class="hidden rounded-lg px-3 py-2 text-sm flex items-center justify-between"
                 style="background:var(--app-panel-strong);border:1px solid var(--app-border)">
                <span style="color:var(--app-text-muted)">Collected: <span id="pm-collected-val" class="font-semibold" style="color:var(--app-text)">₹0</span></span>
                <span id="pm-shortfall-label" class="text-xs font-semibold" style="color:#ea580c"></span>
            </div>

            {{-- ── Partial / due section ──────────────────────────────────── --}}
            <div id="pm-due-wrap" class="hidden">
                <div class="pm-due-section">
                    <p class="pm-due-title">Record Balance Due</p>
                    <label class="flex items-center gap-2 text-sm cursor-pointer mb-3" style="color:var(--app-text)">
                        <input type="checkbox" name="is_partial" id="pmIsPartial" value="1" onchange="pmToggleDue()">
                        Remaining ₹<span id="pm-remaining-val">0</span> to be paid later
                    </label>
                    <div id="pm-due-fields" class="hidden grid grid-cols-2 gap-3">
                        <div class="pm-field">
                            <label>Due Amount (₹)</label>
                            <input type="number" name="due_amount" id="pmDueAmount" step="0.01" min="0.01"
                                   oninput="pmRecalc()" placeholder="0.00">
                        </div>
                        <div class="pm-field">
                            <label>Due Date <span class="text-red-500">*</span></label>
                            <input type="date" name="due_date" id="pmDueDate"
                                   min="{{ now()->addDay()->toDateString() }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="pm-field">
                <label>{{ __('payments.collect.notes') }}</label>
                <textarea name="notes" rows="2" maxlength="500"
                          class="w-full px-3 py-2 rounded-lg border text-sm"
                          style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" id="pmSubmit"
                    class="w-full py-2.5 rounded-lg text-sm font-semibold text-white"
                    style="background:var(--app-brand)">
                {{ __('payments.collect.submit') }}
            </button>
        </form>
    </div>

    {{-- Right: Summary card --}}
    <div class="lg:col-span-2">
        <div class="sticky top-4 rounded-xl p-5 space-y-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <h2 class="font-medium" style="color:var(--app-text)">{{ __('payments.collect.summary') }}</h2>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span style="color:var(--app-text-muted)">{{ __('payments.collect.base_amount') }}</span>
                    <span id="sumBase" style="color:var(--app-text)">₹ —</span>
                </div>
                <div class="flex justify-between">
                    <span style="color:var(--app-text-muted)" id="sumGstLabel">{{ __('payments.collect.gst') }} (0%)</span>
                    <span id="sumGst" style="color:var(--app-text)">₹ 0</span>
                </div>
                <div class="border-t pt-2 flex justify-between font-semibold" style="border-color:var(--app-border)">
                    <span style="color:var(--app-text)">{{ __('payments.collect.total') }}</span>
                    <span id="sumTotal" style="color:var(--app-brand)">₹ —</span>
                </div>
                <div id="sumPaidRow" class="hidden flex justify-between text-xs">
                    <span style="color:var(--app-text-muted)">Collected Now</span>
                    <span id="sumPaid" style="color:#16a34a;font-weight:600">₹ 0</span>
                </div>
                <div id="sumDueRow" class="hidden flex justify-between text-xs">
                    <span style="color:var(--app-text-muted)">Due on <span id="sumDueDate"></span></span>
                    <span id="sumDue" style="color:#ea580c;font-weight:600">₹ 0</span>
                </div>
            </div>

            <div class="rounded-lg p-3 text-xs" style="background:var(--app-panel-strong)">
                <p style="color:var(--app-text-muted)">{{ __('payments.collect.receipt_note') }}</p>
            </div>
        </div>
    </div>
</div>

@php
    $pmPreselect = isset($preselectedMember)
        ? array_merge(
            $preselectedMember->only(['id','name','phone','member_code','plan_id','balance_paise','branch_id']),
            ['pending_due_paise' => max(0, (int) -$preselectedMember->balance_paise)]
        )
        : null;
@endphp
<script>
const PM_METHODS  = @json(\App\Models\Payment::METHODS);
const PM_REF_REQ  = @json(\App\Models\Payment::REF_REQUIRED);
const PM_LABELS   = { cash:'Cash', upi:'UPI', card:'Card', bank:'Bank Transfer', cheque:'Cheque' };
const PM_PRESELECT = @json($pmPreselect);
const PM_PENDING_DUE_MSG = @json(__('payments.collect.pending_due_first', ['amount' => '__AMOUNT__']));

let pmTimer = null;

// ── Member search ─────────────────────────────────────────────────────────────
function pmDoSearch(q) {
    clearTimeout(pmTimer);
    const ul = document.getElementById('pmResults');
    if (q.length < 2) { ul.classList.add('hidden'); return; }
    pmTimer = setTimeout(() => {
        fetch('{{ route('tenant.payments.member-search') }}?q=' + encodeURIComponent(q))
            .then(r => r.json()).then(data => {
                ul.innerHTML = '';
                if (!data.length) {
                    ul.innerHTML = '<li class="px-3 py-2 text-sm" style="color:var(--app-text-muted)">{{ __('payments.collect.no_results') }}</li>';
                } else {
                    data.forEach(m => {
                        const li = document.createElement('li');
                        li.className = 'px-3 py-2 text-sm cursor-pointer hover:opacity-80';
                        li.style.color = 'var(--app-text)';
                        const bal = m.balance_paise < 0
                            ? '<span class="text-red-500 text-xs ml-2">Due: ₹' + Math.abs(m.balance_paise/100).toFixed(0) + '</span>'
                            : (m.balance_paise > 0 ? '<span class="text-green-600 text-xs ml-2">Bal: ₹' + (m.balance_paise/100).toFixed(0) + '</span>' : '');
                        li.innerHTML = '<span class="font-medium">' + m.name + '</span>'
                            + '<span class="text-xs ml-2" style="color:var(--app-text-muted)">' + m.phone + ' · ' + m.member_code + '</span>' + bal;
                        li.onclick = () => pmSelect(m);
                        ul.appendChild(li);
                    });
                }
                ul.classList.remove('hidden');
            });
    }, 250);
}

function pmSelect(m) {
    document.getElementById('pmMemberId').value = m.id;
    document.getElementById('pmSearch').value   = m.name;
    document.getElementById('pmResults').classList.add('hidden');
    document.getElementById('pmName').textContent = m.member_code ? m.name + ' (' + m.member_code + ')' : m.name;
    document.getElementById('pmMeta').textContent = m.member_code ? m.phone + ' · ' + m.member_code : m.phone;
    const balEl = document.getElementById('pmBalance');
    if (m.balance_paise < 0) {
        balEl.textContent  = '₹' + Math.abs(m.balance_paise/100).toFixed(0) + ' due';
        balEl.style.color  = 'var(--color-red-500,#ef4444)';
    } else {
        balEl.textContent  = '₹' + (m.balance_paise/100).toFixed(0) + ' bal';
        balEl.style.color  = 'var(--color-green-600,#16a34a)';
    }
    if (m.branch_id) {
        const sel = document.getElementById('pmBranchId');
        if (sel) sel.value = m.branch_id;
    }
    const planSel = document.getElementById('pmPlanId');
    const hasPendingDue = (parseInt(m.pending_due_paise || 0, 10) || 0) > 0;
    if (planSel && !hasPendingDue) {
        planSel.value = m.plan_id || '';
        pmPlanChanged(planSel);
    } else if (planSel) {
        planSel.value = '';
        pmPlanChanged(planSel);
        document.getElementById('pmAmount').value = ((parseInt(m.pending_due_paise || 0, 10) || 0) / 100).toFixed(2);
    }
    pmSyncDueGuard(m);
    document.getElementById('pmCard').classList.remove('hidden');
}

function pmClear() {
    document.getElementById('pmMemberId').value = '';
    document.getElementById('pmSearch').value   = '';
    document.getElementById('pmCard').classList.add('hidden');
    const planSel = document.getElementById('pmPlanId');
    if (planSel) {
        planSel.value = '';
        pmPlanChanged(planSel);
    }
    pmSyncDueGuard(null);
}

function pmSyncDueGuard(member) {
    const dueAlert = document.getElementById('pmDueAlert');
    const submitBtn = document.getElementById('pmSubmit');
    const pendingDuePaise = parseInt(member?.pending_due_paise || 0, 10) || 0;
    const hasPendingDue = pendingDuePaise > 0;

    if (dueAlert) {
        if (hasPendingDue) {
            const amount = '₹' + (pendingDuePaise / 100).toFixed(2);
            dueAlert.textContent = PM_PENDING_DUE_MSG.replace('__AMOUNT__', amount);
            dueAlert.classList.remove('hidden');
        } else {
            dueAlert.textContent = '';
            dueAlert.classList.add('hidden');
        }
    }

    if (submitBtn) {
        submitBtn.dataset.pendingDue = hasPendingDue ? '1' : '0';
    }
}

// ── Plan selection ────────────────────────────────────────────────────────────
function pmPlanChanged(sel) {
    if (sel.value && document.getElementById('pmSubmit')?.dataset.pendingDue === '1') {
        alert('{{ __('payments.collect.pending_due_block') }}');
        sel.value = '';
    }
    const opt = sel.options[sel.selectedIndex];
    const pricePaise = parseInt(opt.dataset.price || 0, 10);
    const amountInput = document.getElementById('pmAmount');
    const hasPlan = !!opt.value;
    if (pricePaise) {
        amountInput.value = (pricePaise / 100).toFixed(2);
    }
    amountInput.readOnly = hasPlan;
    pmRecalc();
}

// ── Split rows ────────────────────────────────────────────────────────────────
function pmBuildSplitRow(idx) {
    const div = document.createElement('div');
    div.className = 'split-row';
    div.dataset.idx = idx;

    // Method select
    const methodWrap = document.createElement('div');
    methodWrap.className = 'pm-field';
    const mLabel = document.createElement('label');
    mLabel.textContent = 'Method';
    const mSel = document.createElement('select');
    mSel.name = 'splits[' + idx + '][method]';
    mSel.required = true;
    mSel.onchange = () => pmUpdateRefField(div, mSel.value, idx);
    PM_METHODS.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m; opt.textContent = PM_LABELS[m] || m;
        mSel.appendChild(opt);
    });
    methodWrap.appendChild(mLabel);
    methodWrap.appendChild(mSel);

    // Amount input
    const amtWrap = document.createElement('div');
    amtWrap.className = 'pm-field';
    const aLabel = document.createElement('label');
    aLabel.textContent = 'Amount (₹)';
    const aInp = document.createElement('input');
    aInp.type = 'number'; aInp.name = 'splits[' + idx + '][amount]';
    aInp.min = '0.01'; aInp.step = '0.01'; aInp.required = true;
    aInp.placeholder = '0.00';
    aInp.oninput = pmRecalc;
    amtWrap.appendChild(aLabel);
    amtWrap.appendChild(aInp);

    // Reference input
    const refWrap = document.createElement('div');
    refWrap.className = 'pm-field';
    const rLabel = document.createElement('label');
    rLabel.textContent = 'Reference';
    const rInp = document.createElement('input');
    rInp.type = 'text'; rInp.name = 'splits[' + idx + '][reference]';
    rInp.placeholder = 'Txn ID / Cheque no.';
    rInp.maxLength = 100;
    refWrap.appendChild(rLabel);
    refWrap.appendChild(rInp);
    refWrap.dataset.refWrap = '1';

    // Remove button
    const rmBtn = document.createElement('button');
    rmBtn.type = 'button';
    rmBtn.className = 'split-row-remove';
    rmBtn.innerHTML = '✕';
    rmBtn.title = 'Remove';
    rmBtn.onclick = () => { div.remove(); pmRecalc(); pmCheckRemovable(); };

    div.appendChild(methodWrap);
    div.appendChild(amtWrap);
    div.appendChild(refWrap);
    div.appendChild(rmBtn);

    pmUpdateRefField(div, 'cash', idx);
    return div;
}

function pmUpdateRefField(row, method, idx) {
    const refWrap = row.querySelector('[data-ref-wrap]');
    const refInp  = refWrap?.querySelector('input');
    if (!refWrap || !refInp) return;
    const required = PM_REF_REQ.includes(method);
    refWrap.querySelector('label').textContent = required ? 'Reference *' : 'Reference';
    refInp.required = required;
    refInp.name = 'splits[' + idx + '][reference]';
}

let pmSplitIdx = 0;

function pmAddSplit(prefillAmount) {
    const container = document.getElementById('pm-splits');
    const row = pmBuildSplitRow(pmSplitIdx++);
    if (prefillAmount) {
        row.querySelector('input[type=number]').value = prefillAmount.toFixed(2);
    }
    container.appendChild(row);
    pmCheckRemovable();
    pmRecalc();
}

function pmCheckRemovable() {
    const rows = document.querySelectorAll('#pm-splits .split-row');
    rows.forEach(r => {
        const btn = r.querySelector('.split-row-remove');
        if (btn) btn.style.visibility = rows.length > 1 ? 'visible' : 'hidden';
    });
}

// ── Recalc summary ────────────────────────────────────────────────────────────
function pmRecalc() {
    const planSel = document.getElementById('pmPlanId');
    const planOpt = planSel?.options[planSel.selectedIndex];
    const hasPlan = !!planOpt?.value;
    const enteredAmount = parseFloat(document.getElementById('pmAmount').value) || 0;
    const planBase = (parseInt(planOpt?.dataset.basePrice || 0, 10) || 0) / 100;
    const planGst = (parseInt(planOpt?.dataset.gstAmount || 0, 10) || 0) / 100;
    const gstRate = hasPlan ? (parseFloat(planOpt?.dataset.gst || 0) || 0) : 0;
    const amount = hasPlan ? planBase : enteredAmount;
    const gst = hasPlan ? planGst : 0;
    const total = hasPlan ? enteredAmount : amount;

    document.getElementById('sumBase').textContent  = '₹ ' + amount.toFixed(2);
    document.getElementById('sumGst').textContent   = '₹ ' + gst.toFixed(2);
    document.getElementById('sumGstLabel').textContent = '{{ __('payments.collect.gst') }} (' + gstRate.toFixed(0) + '%)';
    document.getElementById('sumTotal').textContent = '₹ ' + total.toFixed(2);

    // Sum splits
    let collected = 0;
    document.querySelectorAll('#pm-splits input[type=number]').forEach(inp => {
        collected += parseFloat(inp.value) || 0;
    });

    const bar       = document.getElementById('pm-collected-bar');
    const dueWrap   = document.getElementById('pm-due-wrap');
    const remaining = total - collected;

    document.getElementById('pm-collected-val').textContent = '₹' + collected.toFixed(2);

    if (total > 0 && Math.abs(remaining) > 0.009) {
        bar.classList.remove('hidden');
        const label = document.getElementById('pm-shortfall-label');
        if (remaining > 0) {
            label.textContent = '₹' + remaining.toFixed(2) + ' short';
            label.style.color = '#ea580c';
        } else {
            label.textContent = '₹' + Math.abs(remaining).toFixed(2) + ' excess';
            label.style.color = '#16a34a';
        }
        if (remaining > 0) {
            dueWrap.classList.remove('hidden');
            document.getElementById('pm-remaining-val').textContent = remaining.toFixed(2);
            // Pre-fill due amount
            const dueAmt = document.getElementById('pmDueAmount');
            if (!dueAmt.dataset.manual) dueAmt.value = remaining.toFixed(2);
        } else {
            dueWrap.classList.add('hidden');
            document.getElementById('pmIsPartial').checked = false;
            pmSetDueVisibility();
        }
    } else {
        bar.classList.add('hidden');
        dueWrap.classList.add('hidden');
        document.getElementById('pmIsPartial').checked = false;
        pmSetDueVisibility();
    }

    // Summary rows
    const isPartial = document.getElementById('pmIsPartial').checked;
    const dueDate   = document.getElementById('pmDueDate').value;
    const dueAmt    = parseFloat(document.getElementById('pmDueAmount').value) || 0;

    document.getElementById('sumPaidRow').classList.toggle('hidden', !isPartial);
    document.getElementById('sumDueRow').classList.toggle('hidden', !isPartial || !dueDate);
    document.getElementById('sumPaid').textContent = '₹ ' + collected.toFixed(2);
    document.getElementById('sumDue').textContent  = '₹ ' + dueAmt.toFixed(2);
    document.getElementById('sumDueDate').textContent = dueDate || '';
}

function pmSetDueVisibility() {
    const on = document.getElementById('pmIsPartial').checked;
    document.getElementById('pm-due-fields').classList.toggle('hidden', !on);
}

function pmToggleDue() {
    pmSetDueVisibility();
    pmRecalc();
}

// Prevent auto-overwrite of due amount if user edits it
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('pmDueAmount')?.addEventListener('input', function () {
        this.dataset.manual = '1';
    });
    document.getElementById('pmDueDate')?.addEventListener('change', pmRecalc);
});

// ── Form submit guard ─────────────────────────────────────────────────────────
document.getElementById('pmForm').addEventListener('submit', function (e) {
    if (!document.getElementById('pmMemberId').value) {
        e.preventDefault();
        alert('{{ __('payments.collect.select_member_first') }}');
        document.getElementById('pmSearch').focus();
        return;
    }
    // Validate split amounts sum > 0
    let collected = 0;
    document.querySelectorAll('#pm-splits input[type=number]').forEach(inp => {
        collected += parseFloat(inp.value) || 0;
    });
    if (collected <= 0) {
        e.preventDefault();
        alert('Please enter at least one payment method with an amount.');
        return;
    }
    if (document.getElementById('pmSubmit')?.dataset.pendingDue === '1' && document.getElementById('pmPlanId').value) {
        e.preventDefault();
        alert('{{ __('payments.collect.pending_due_block') }}');
        document.getElementById('pmPlanId').focus();
        return;
    }
    // Partial requires due date
    if (document.getElementById('pmIsPartial').checked && !document.getElementById('pmDueDate').value) {
        e.preventDefault();
        alert('Please select a due date for the remaining balance.');
        document.getElementById('pmDueDate').focus();
    }
});

// ── Init ──────────────────────────────────────────────────────────────────────
pmAddSplit();
pmRecalc();

if (PM_PRESELECT) {
    pmSelect(PM_PRESELECT);
    if (PM_PRESELECT.plan_id && !(parseInt(PM_PRESELECT.pending_due_paise || 0, 10) > 0)) {
        var planSel = document.getElementById('pmPlanId');
        if (planSel) {
            planSel.value = PM_PRESELECT.plan_id;
            pmPlanChanged(planSel);
        }
    }
}
</script>

</x-layouts.admin>
