<x-layouts.admin :title="__('payments.nav.collect')">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('payments.nav.collect') }}</h1>
        <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('payments.collect.subtitle') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('tenant.payments.history') }}"
           class="px-3 py-1.5 text-sm rounded border"
           style="border-color:var(--app-border);color:var(--app-text-muted)">
            {{ __('payments.nav.history') }}
        </a>
        <a href="{{ route('tenant.payments.dues') }}"
           class="px-3 py-1.5 text-sm rounded border"
           style="border-color:var(--app-border);color:var(--app-text-muted)">
            {{ __('payments.nav.dues') }}
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- Left: Member search + form --}}
    <div class="lg:col-span-3 space-y-4">

        {{-- Member search --}}
        <div class="rounded-xl p-5" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <h2 class="font-medium mb-3" style="color:var(--app-text)">{{ __('payments.collect.member') }}</h2>

            <div class="relative">
                <input id="pmSearch" type="text"
                       placeholder="{{ __('payments.collect.search_placeholder') }}"
                       autocomplete="off"
                       oninput="pmDoSearch(this.value)"
                       class="w-full px-3 py-2 rounded-lg border text-sm"
                       style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                <ul id="pmResults"
                    class="absolute z-30 w-full mt-1 rounded-lg border shadow-lg hidden"
                    style="background:var(--app-panel);border-color:var(--app-border)"></ul>
            </div>

            {{-- Selected member card --}}
            <div id="pmCard" class="hidden mt-3 rounded-lg p-3" style="background:var(--app-panel-strong);border:1px solid var(--app-border)">
                <div class="flex items-start justify-between">
                    <div>
                        <p id="pmName" class="font-medium text-sm" style="color:var(--app-text)"></p>
                        <p id="pmMeta" class="text-xs mt-0.5" style="color:var(--app-text-muted)"></p>
                    </div>
                    <div id="pmBalance" class="text-sm font-semibold"></div>
                </div>
                <button type="button" onclick="pmClear()"
                        class="text-xs mt-2" style="color:var(--app-text-muted)">
                    ✕ {{ __('payments.collect.change_member') }}
                </button>
            </div>
        </div>

        {{-- Payment form --}}
        <form id="pmForm" action="{{ route('tenant.payments.store') }}" method="POST"
              class="rounded-xl p-5 space-y-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
            @csrf
            <input type="hidden" name="member_id" id="pmMemberId">

            <h2 class="font-medium" style="color:var(--app-text)">{{ __('payments.collect.payment_details') }}</h2>

            <div class="grid grid-cols-2 gap-4">
                {{-- Branch --}}
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('common.branch') }}</label>
                    <select name="branch_id" id="pmBranchId" required
                            class="w-full px-3 py-2 rounded-lg border text-sm"
                            style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}"
                                @selected(old('branch_id', $selectedBranchId) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Plan --}}
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('payments.collect.plan') }}</label>
                    <select name="plan_id" id="pmPlanId"
                            onchange="pmPlanChanged(this)"
                            class="w-full px-3 py-2 rounded-lg border text-sm"
                            style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        <option value="">— {{ __('payments.collect.no_plan') }} —</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}"
                                    data-price="{{ $plan->price_paise }}"
                                    data-gst="{{ $plan->gst_applicable ? $plan->gst_rate : 0 }}">
                                {{ $plan->name }} (₹{{ number_format($plan->price_paise / 100, 0) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Amount --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('payments.collect.amount') }} (₹)</label>
                    <input type="number" name="amount" id="pmAmount" step="0.01" min="0.01" required
                           oninput="pmRecalc()"
                           class="w-full px-3 py-2 rounded-lg border text-sm"
                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    @error('amount')
                        <p class="text-xs mt-1 text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('payments.collect.payment_date') }}</label>
                    <input type="date" name="payment_date" required
                           value="{{ old('payment_date', today()->toDateString()) }}"
                           class="w-full px-3 py-2 rounded-lg border text-sm"
                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                </div>
            </div>

            {{-- GST row --}}
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm cursor-pointer" style="color:var(--app-text)">
                    <input type="checkbox" name="apply_gst" id="pmApplyGst" value="1" onchange="pmRecalc()">
                    {{ __('payments.collect.apply_gst') }}
                </label>
                <div id="pmGstRateWrap" class="hidden flex items-center gap-2">
                    <input type="number" name="gst_rate" id="pmGstRate" step="0.01" min="0" max="100" value="18"
                           oninput="pmRecalc()"
                           class="w-20 px-2 py-1 rounded border text-sm"
                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    <span class="text-sm" style="color:var(--app-text-muted)">%</span>
                </div>
            </div>

            {{-- Method --}}
            <div>
                <label class="block text-xs font-medium mb-2" style="color:var(--app-text-muted)">{{ __('payments.collect.method') }}</label>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\Payment::METHODS as $method)
                        <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer pm-method-label text-sm"
                               style="border-color:var(--app-border)">
                            <input type="radio" name="method" value="{{ $method }}"
                                   class="hidden"
                                   onchange="pmMethodChanged('{{ $method }}')"
                                   {{ old('method', 'cash') === $method ? 'checked' : '' }}>
                            {{ __('payments.methods.' . $method) }}
                        </label>
                    @endforeach
                </div>
                @error('method')
                    <p class="text-xs mt-1 text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Reference --}}
            <div id="pmRefWrap" class="hidden">
                <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('payments.collect.reference') }}</label>
                <input type="text" name="reference" id="pmReference" maxlength="100"
                       placeholder="{{ __('payments.collect.reference_placeholder') }}"
                       class="w-full px-3 py-2 rounded-lg border text-sm"
                       style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                @error('reference')
                    <p class="text-xs mt-1 text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('payments.collect.notes') }}</label>
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
            </div>

            <div class="rounded-lg p-3 text-xs" style="background:var(--app-panel-strong)">
                <p style="color:var(--app-text-muted)">{{ __('payments.collect.receipt_note') }}</p>
            </div>
        </div>
    </div>
</div>

<style>
.pm-method-label:has(input:checked) {
    background: color-mix(in srgb, var(--app-brand) 12%, transparent);
    border-color: var(--app-brand);
    color: var(--app-brand);
}
</style>

<script>
let pmTimer = null;
const PM_REF_REQUIRED = @json(\App\Models\Payment::REF_REQUIRED);

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
                        li.innerHTML = '<span class="font-medium">' + m.name + '</span><span class="text-xs ml-2" style="color:var(--app-text-muted)">' + m.phone + ' · ' + m.member_code + '</span>' + bal;
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
    document.getElementById('pmSearch').value = m.name;
    document.getElementById('pmResults').classList.add('hidden');
    document.getElementById('pmName').textContent = m.name + ' (' + m.member_code + ')';
    document.getElementById('pmMeta').textContent = m.phone + ' · ' + m.member_code;
    const balEl = document.getElementById('pmBalance');
    if (m.balance_paise < 0) {
        balEl.textContent = '₹' + Math.abs(m.balance_paise/100).toFixed(0) + ' due';
        balEl.style.color = 'var(--color-red-500, #ef4444)';
    } else {
        balEl.textContent = '₹' + (m.balance_paise/100).toFixed(0) + ' bal';
        balEl.style.color = 'var(--color-green-600, #16a34a)';
    }
    // Set branch if member has one
    if (m.branch_id) {
        const sel = document.getElementById('pmBranchId');
        if (sel) sel.value = m.branch_id;
    }
    document.getElementById('pmCard').classList.remove('hidden');
}

function pmClear() {
    document.getElementById('pmMemberId').value = '';
    document.getElementById('pmSearch').value = '';
    document.getElementById('pmCard').classList.add('hidden');
}

function pmPlanChanged(sel) {
    const opt = sel.options[sel.selectedIndex];
    const pricePaise = parseInt(opt.dataset.price || 0);
    const gstRate = parseFloat(opt.dataset.gst || 0);
    if (pricePaise) {
        document.getElementById('pmAmount').value = (pricePaise / 100).toFixed(2);
    }
    if (gstRate > 0) {
        document.getElementById('pmApplyGst').checked = true;
        document.getElementById('pmGstRateWrap').classList.remove('hidden');
        document.getElementById('pmGstRate').value = gstRate;
    } else {
        document.getElementById('pmApplyGst').checked = false;
        document.getElementById('pmGstRateWrap').classList.add('hidden');
    }
    pmRecalc();
}

function pmRecalc() {
    const amount = parseFloat(document.getElementById('pmAmount').value) || 0;
    const applyGst = document.getElementById('pmApplyGst').checked;
    const gstRate = applyGst ? (parseFloat(document.getElementById('pmGstRate').value) || 0) : 0;
    const gst = amount * gstRate / 100;
    const total = amount + gst;

    document.getElementById('sumBase').textContent = '₹ ' + amount.toFixed(2);
    document.getElementById('sumGst').textContent = '₹ ' + gst.toFixed(2);
    document.getElementById('sumGstLabel').textContent = '{{ __('payments.collect.gst') }} (' + gstRate.toFixed(0) + '%)';
    document.getElementById('sumTotal').textContent = '₹ ' + total.toFixed(2);
}

function pmMethodChanged(method) {
    // Style labels
    document.querySelectorAll('.pm-method-label input').forEach(inp => {
        inp.parentElement.style.background = '';
        inp.parentElement.style.borderColor = 'var(--app-border)';
        inp.parentElement.style.color = 'var(--app-text)';
    });
    const checked = document.querySelector('.pm-method-label input:checked');
    if (checked) {
        checked.parentElement.style.background = 'color-mix(in srgb, var(--app-brand) 12%, transparent)';
        checked.parentElement.style.borderColor = 'var(--app-brand)';
        checked.parentElement.style.color = 'var(--app-brand)';
    }
    // Reference field
    const refWrap = document.getElementById('pmRefWrap');
    const refInput = document.getElementById('pmReference');
    if (PM_REF_REQUIRED.includes(method)) {
        refWrap.classList.remove('hidden');
        refInput.required = true;
    } else {
        refWrap.classList.add('hidden');
        refInput.required = false;
        refInput.value = '';
    }
}

document.getElementById('pmApplyGst').addEventListener('change', function () {
    document.getElementById('pmGstRateWrap').classList.toggle('hidden', !this.checked);
    pmRecalc();
});

// Form submit guard
document.getElementById('pmForm').addEventListener('submit', function (e) {
    if (!document.getElementById('pmMemberId').value) {
        e.preventDefault();
        alert('{{ __('payments.collect.select_member_first') }}');
        document.getElementById('pmSearch').focus();
    }
});

// Init
pmMethodChanged('{{ old('method', 'cash') }}');
pmRecalc();
</script>

</x-layouts.admin>
