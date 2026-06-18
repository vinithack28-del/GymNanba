<x-layouts.admin :title="__('invoices.nav.create')">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('invoices.nav.create') }}</h1>
        <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('invoices.create.subtitle') }}</p>
    </div>
    <a href="{{ route('tenant.invoices.index') }}"
       class="px-3 py-1.5 text-sm rounded border"
       style="border-color:var(--app-border);color:var(--app-text-muted)">
        ← {{ __('invoices.nav.invoices') }}
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- Left: form --}}
    <div class="lg:col-span-3 space-y-5">

        {{-- Member --}}
        <div class="rounded-xl p-5" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <h2 class="font-medium mb-3" style="color:var(--app-text)">{{ __('invoices.create.member') }}</h2>
            <div class="relative">
                <input id="ivSearch" type="text"
                       placeholder="{{ __('invoices.create.search_placeholder') }}"
                       autocomplete="off"
                       oninput="ivDoSearch(this.value)"
                       class="w-full px-3 py-2 rounded-lg border text-sm"
                       style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                <ul id="ivResults"
                    class="absolute z-30 w-full mt-1 rounded-lg border shadow-lg hidden"
                    style="background:var(--app-panel);border-color:var(--app-border)"></ul>
            </div>
            <div id="ivCard" class="hidden mt-3 rounded-lg p-3" style="background:var(--app-panel-strong);border:1px solid var(--app-border)">
                <p id="ivName" class="font-medium text-sm" style="color:var(--app-text)"></p>
                <p id="ivMeta" class="text-xs mt-0.5" style="color:var(--app-text-muted)"></p>
                <button type="button" onclick="ivClear()" class="text-xs mt-2" style="color:var(--app-text-muted)">
                    ✕ {{ __('invoices.create.change_member') }}
                </button>
            </div>
        </div>

        {{-- Invoice details --}}
        <form id="ivForm" action="{{ route('tenant.invoices.store') }}" method="POST"
              class="rounded-xl p-5 space-y-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
            @csrf
            <input type="hidden" name="member_id" id="ivMemberId">

            <h2 class="font-medium" style="color:var(--app-text)">{{ __('invoices.create.details') }}</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('invoices.create.invoice_date') }}</label>
                    <input type="date" name="invoice_date" required value="{{ old('invoice_date', today()->toDateString()) }}"
                           class="w-full px-3 py-2 rounded-lg border text-sm"
                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('invoices.create.due_date') }}</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                           class="w-full px-3 py-2 rounded-lg border text-sm"
                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('common.branch') }}</label>
                <select name="branch_id"
                        class="w-full px-3 py-2 rounded-lg border text-sm"
                        style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    <option value="">— {{ __('invoices.create.no_branch') }} —</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" {{ old('branch_id', $selectedBranchId ?? null) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Line items --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium" style="color:var(--app-text-muted)">{{ __('invoices.create.line_items') }}</label>
                    <button type="button" onclick="ivAddRow()"
                            class="text-xs px-2 py-1 rounded border"
                            style="border-color:var(--app-border);color:var(--app-brand)">
                        + {{ __('invoices.create.add_line') }}
                    </button>
                </div>

                <div class="rounded-lg overflow-hidden" style="border:1px solid var(--app-border)">
                    <table class="w-full text-xs">
                        <thead style="background:var(--app-panel-strong)">
                            <tr>
                                <th class="text-left px-3 py-2 font-medium w-[38%]" style="color:var(--app-text-muted)">{{ __('invoices.create.col_desc') }}</th>
                                <th class="text-center px-2 py-2 font-medium w-[10%]" style="color:var(--app-text-muted)">{{ __('invoices.create.col_qty') }}</th>
                                <th class="text-right px-2 py-2 font-medium w-[18%]" style="color:var(--app-text-muted)">{{ __('invoices.create.col_rate') }}</th>
                                <th class="text-center px-2 py-2 font-medium w-[14%]" style="color:var(--app-text-muted)">{{ __('invoices.create.col_gst') }}</th>
                                <th class="text-right px-2 py-2 font-medium w-[14%]" style="color:var(--app-text-muted)">{{ __('invoices.create.col_amount') }}</th>
                                <th class="w-[6%]"></th>
                            </tr>
                        </thead>
                        <tbody id="ivRows"></tbody>
                    </table>
                </div>
                @error('line_items')
                    <p class="text-xs mt-1 text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('invoices.create.notes') }}</label>
                <textarea name="notes" rows="2" maxlength="1000"
                          class="w-full px-3 py-2 rounded-lg border text-sm"
                          style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" id="ivSubmit"
                    class="w-full py-2.5 rounded-lg text-sm font-semibold text-white"
                    style="background:var(--app-brand)">
                {{ __('invoices.create.submit') }}
            </button>
        </form>
    </div>

    {{-- Right: totals --}}
    <div class="lg:col-span-2">
        <div class="sticky top-4 rounded-xl p-5 space-y-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <h2 class="font-medium" style="color:var(--app-text)">{{ __('invoices.create.summary') }}</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span style="color:var(--app-text-muted)">{{ __('invoices.create.subtotal') }}</span>
                    <span id="sumSubtotal" style="color:var(--app-text)">₹ —</span>
                </div>
                <div class="flex justify-between">
                    <span style="color:var(--app-text-muted)">{{ __('invoices.create.gst') }}</span>
                    <span id="sumGst" style="color:var(--app-text)">₹ 0</span>
                </div>
                <div class="border-t pt-2 flex justify-between font-semibold" style="border-color:var(--app-border)">
                    <span style="color:var(--app-text)">{{ __('invoices.create.total') }}</span>
                    <span id="sumTotal" style="color:var(--app-brand)">₹ —</span>
                </div>
            </div>
            <div class="rounded-lg p-3 text-xs" style="background:var(--app-panel-strong)">
                <p style="color:var(--app-text-muted)">SAC: 998311 — Fitness centre services</p>
            </div>
        </div>
    </div>
</div>

<script>
let ivTimer = null;
let ivRowIdx = 0;
const GST_RATES = @json(\App\Models\Invoice::GST_RATES);

// ── Member search ──────────────────────────────────────────────────────────
function ivDoSearch(q) {
    clearTimeout(ivTimer);
    const ul = document.getElementById('ivResults');
    if (q.length < 2) { ul.classList.add('hidden'); return; }
    ivTimer = setTimeout(() => {
        fetch('{{ route('tenant.invoices.member-search') }}?q=' + encodeURIComponent(q))
            .then(r => r.json()).then(data => {
                ul.innerHTML = '';
                if (!data.length) {
                    ul.innerHTML = '<li class="px-3 py-2 text-sm" style="color:var(--app-text-muted)">{{ __('invoices.create.no_results') }}</li>';
                } else {
                    data.forEach(m => {
                        const li = document.createElement('li');
                        li.className = 'px-3 py-2 text-sm cursor-pointer hover:opacity-80';
                        li.style.color = 'var(--app-text)';
                        li.innerHTML = '<span class="font-medium">' + m.name + '</span><span class="text-xs ml-2" style="color:var(--app-text-muted)">' + m.phone + ' · ' + m.member_code + '</span>';
                        li.onclick = () => ivSelect(m);
                        ul.appendChild(li);
                    });
                }
                ul.classList.remove('hidden');
            });
    }, 250);
}

function ivSelect(m) {
    document.getElementById('ivMemberId').value = m.id;
    document.getElementById('ivSearch').value = m.name;
    document.getElementById('ivResults').classList.add('hidden');
    document.getElementById('ivName').textContent = m.name + ' (' + m.member_code + ')';
    document.getElementById('ivMeta').textContent = m.phone;
    document.getElementById('ivCard').classList.remove('hidden');
}

function ivClear() {
    document.getElementById('ivMemberId').value = '';
    document.getElementById('ivSearch').value = '';
    document.getElementById('ivCard').classList.add('hidden');
}

// ── Line items ─────────────────────────────────────────────────────────────
function ivAddRow(desc, qty, rate, gstRate) {
    const idx = ivRowIdx++;
    const tbody = document.getElementById('ivRows');
    const tr = document.createElement('tr');
    tr.id = 'ivRow_' + idx;
    tr.style.borderTop = '1px solid var(--app-border)';
    const gstOpts = GST_RATES.map(r => `<option value="${r}" ${r == (gstRate||0) ? 'selected' : ''}>${r}%</option>`).join('');
    tr.innerHTML = `
        <td class="px-2 py-1.5">
            <input type="text" name="line_items[${idx}][description]" required maxlength="200"
                   value="${desc||''}"
                   oninput="ivRecalc()"
                   class="w-full px-2 py-1 rounded border text-xs"
                   style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
        </td>
        <td class="px-2 py-1.5">
            <input type="number" name="line_items[${idx}][qty]" required min="1" value="${qty||1}"
                   oninput="ivRecalc()"
                   class="w-full px-2 py-1 rounded border text-xs text-center"
                   style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
        </td>
        <td class="px-2 py-1.5">
            <input type="number" name="line_items[${idx}][rate_paise]" required min="1" step="1"
                   value="${rate||''}"
                   placeholder="paise"
                   oninput="ivRecalc()"
                   class="w-full px-2 py-1 rounded border text-xs text-right"
                   style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
        </td>
        <td class="px-2 py-1.5">
            <select name="line_items[${idx}][gst_rate]" onchange="ivRecalc()"
                    class="w-full px-1 py-1 rounded border text-xs"
                    style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                ${gstOpts}
            </select>
        </td>
        <td class="px-2 py-1.5 text-right text-xs font-medium" id="ivAmt_${idx}" style="color:var(--app-text)">—</td>
        <td class="px-2 py-1.5 text-center">
            <button type="button" onclick="ivRemoveRow(${idx})"
                    class="text-red-400 hover:text-red-600 text-base leading-none">×</button>
        </td>`;
    tbody.appendChild(tr);
    ivRecalc();
}

function ivRemoveRow(idx) {
    const row = document.getElementById('ivRow_' + idx);
    if (row) row.remove();
    ivRecalc();
}

function ivRecalc() {
    let subtotal = 0, gst = 0;
    document.querySelectorAll('#ivRows tr').forEach(tr => {
        const qty  = parseInt(tr.querySelector('[name$="[qty]"]')?.value) || 0;
        const rate = parseInt(tr.querySelector('[name$="[rate_paise]"]')?.value) || 0;
        const gstR = parseFloat(tr.querySelector('[name$="[gst_rate]"]')?.value) || 0;
        const amt  = qty * rate;
        const itemGst = Math.round(amt * gstR / 100);
        const idx  = tr.id.replace('ivRow_', '');
        const amtEl = document.getElementById('ivAmt_' + idx);
        if (amtEl) amtEl.textContent = amt > 0 ? '₹' + (amt/100).toFixed(2) : '—';
        subtotal += amt;
        gst += itemGst;
    });
    const total = subtotal + gst;
    document.getElementById('sumSubtotal').textContent = subtotal > 0 ? '₹ ' + (subtotal/100).toFixed(2) : '₹ —';
    document.getElementById('sumGst').textContent = '₹ ' + (gst/100).toFixed(2);
    document.getElementById('sumTotal').textContent = total > 0 ? '₹ ' + (total/100).toFixed(2) : '₹ —';
}

// form guard
document.getElementById('ivForm').addEventListener('submit', function(e) {
    if (!document.getElementById('ivMemberId').value) {
        e.preventDefault();
        alert('{{ __('invoices.create.select_member_first') }}');
        return;
    }
    if (!document.querySelectorAll('#ivRows tr').length) {
        e.preventDefault();
        alert('{{ __('invoices.create.add_line_first') }}');
    }
});

// init with one empty row
ivAddRow();
</script>

</x-layouts.admin>
