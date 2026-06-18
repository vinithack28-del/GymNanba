@php $editing = isset($expense); @endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Date --}}
    <div>
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.date') }} *</label>
        <input type="date" name="date" required
               value="{{ old('date', $editing ? $expense->date->toDateString() : today()->toDateString()) }}"
               max="{{ today()->toDateString() }}"
               min="{{ now()->subYear()->toDateString() }}"
               class="w-full px-3 py-2 rounded-lg border text-sm"
               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
        @error('date') <p class="text-xs mt-1 text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Branch --}}
    <div>
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('common.branch') }} *</label>
        <select name="branch_id" required
                class="w-full px-3 py-2 rounded-lg border text-sm"
                style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
            <option value="">— {{ __('expenses.form.select_branch') }} —</option>
            @foreach ($branches as $b)
                <option value="{{ $b->id }}" {{ old('branch_id', $editing ? $expense->branch_id : ($selectedBranchId ?? '')) == $b->id ? 'selected' : '' }}>
                    {{ $b->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id') <p class="text-xs mt-1 text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Category --}}
    <div>
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.category') }} *</label>
        <select name="category" id="exCategory" required
                onchange="exCategoryChanged(this.value)"
                class="w-full px-3 py-2 rounded-lg border text-sm"
                style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
            <option value="">— {{ __('expenses.form.select_category') }} —</option>
            @foreach (array_keys(\App\Models\Expense::CATEGORIES) as $cat)
                <option value="{{ $cat }}" {{ old('category', $editing ? $expense->category : '') === $cat ? 'selected' : '' }}>
                    {{ __('expenses.categories.' . $cat) }}
                </option>
            @endforeach
        </select>
        @error('category') <p class="text-xs mt-1 text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Sub-category --}}
    <div id="exSubWrap">
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.sub_category') }}</label>
        <select name="sub_category" id="exSubCategory"
                class="w-full px-3 py-2 rounded-lg border text-sm"
                style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
            <option value="">— {{ __('expenses.form.select_sub') }} —</option>
        </select>
    </div>

    {{-- Description --}}
    <div class="lg:col-span-2">
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.description') }} *</label>
        <input type="text" name="description" required minlength="5" maxlength="200"
               value="{{ old('description', $editing ? $expense->description : '') }}"
               class="w-full px-3 py-2 rounded-lg border text-sm"
               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
        @error('description') <p class="text-xs mt-1 text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Amount --}}
    <div>
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.amount') }} (₹) *</label>
        <input type="number" name="amount" required step="0.01" min="0.01" max="999999"
               value="{{ old('amount', $editing ? number_format($expense->amount_paise / 100, 2, '.', '') : '') }}"
               class="w-full px-3 py-2 rounded-lg border text-sm"
               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
        @error('amount') <p class="text-xs mt-1 text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- GST paid --}}
    <div>
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.gst') }} (₹)</label>
        <input type="number" name="gst" step="0.01" min="0"
               value="{{ old('gst', $editing ? number_format($expense->gst_paise / 100, 2, '.', '') : '0') }}"
               class="w-full px-3 py-2 rounded-lg border text-sm"
               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
    </div>

    {{-- Method --}}
    <div>
        <label class="block text-xs font-medium mb-2" style="color:var(--app-text-muted)">{{ __('expenses.form.method') }} *</label>
        <div class="flex flex-wrap gap-2">
            @foreach (\App\Models\Expense::METHODS as $method)
                <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer ex-method-label text-sm"
                       style="border-color:var(--app-border)">
                    <input type="radio" name="method" value="{{ $method }}" class="hidden"
                           onchange="exMethodChanged('{{ $method }}')"
                           {{ old('method', $editing ? $expense->method : 'cash') === $method ? 'checked' : '' }}>
                    {{ __('expenses.methods.' . $method) }}
                </label>
            @endforeach
        </div>
        @error('method') <p class="text-xs mt-1 text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Reference --}}
    <div>
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.reference') }}</label>
        <input type="text" name="reference" maxlength="100"
               value="{{ old('reference', $editing ? $expense->reference : '') }}"
               placeholder="{{ __('expenses.form.reference_placeholder') }}"
               class="w-full px-3 py-2 rounded-lg border text-sm"
               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
    </div>

    {{-- Vendor --}}
    <div>
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.vendor') }}</label>
        <input type="text" name="vendor" maxlength="100"
               value="{{ old('vendor', $editing ? $expense->vendor : '') }}"
               class="w-full px-3 py-2 rounded-lg border text-sm"
               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
    </div>

    {{-- Receipt URL --}}
    <div>
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.receipt_url') }}</label>
        <input type="url" name="receipt_url" maxlength="500"
               value="{{ old('receipt_url', $editing ? $expense->receipt_url : '') }}"
               placeholder="https://..."
               class="w-full px-3 py-2 rounded-lg border text-sm"
               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
    </div>

    {{-- Notes --}}
    <div class="lg:col-span-2">
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.notes') }}</label>
        <textarea name="notes" rows="2" maxlength="500"
                  class="w-full px-3 py-2 rounded-lg border text-sm"
                  style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">{{ old('notes', $editing ? $expense->notes : '') }}</textarea>
    </div>
</div>

{{-- Salary fields (shown when category = salaries) --}}
<div id="exSalaryFields" class="hidden mt-4 pt-4 grid grid-cols-1 lg:grid-cols-2 gap-4"
     style="border-top:1px solid var(--app-border)">
    <div class="lg:col-span-2">
        <p class="text-xs font-semibold uppercase tracking-wide mb-3" style="color:var(--app-text-muted)">{{ __('expenses.form.salary_section') }}</p>
    </div>
    <div>
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.staff_member') }}</label>
        <select name="staff_id"
                class="w-full px-3 py-2 rounded-lg border text-sm"
                style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
            <option value="">— {{ __('expenses.form.select_staff') }} —</option>
            @foreach ($staffList as $s)
                <option value="{{ $s->id }}" {{ old('staff_id', $editing ? $expense->staff_id : '') == $s->id ? 'selected' : '' }}>
                    {{ $s->name }} ({{ $s->role }})
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.salary_month') }}</label>
        <input type="month" name="salary_month"
               value="{{ old('salary_month', $editing ? $expense->salary_month : '') }}"
               class="w-full px-3 py-2 rounded-lg border text-sm"
               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
    </div>
</div>

{{-- Recurring --}}
<div class="mt-4 pt-4" style="border-top:1px solid var(--app-border)">
    <label class="flex items-center gap-2 text-sm cursor-pointer" style="color:var(--app-text)">
        <input type="checkbox" name="is_recurring" id="exIsRecurring" value="1"
               onchange="exRecurringChanged(this.checked)"
               {{ old('is_recurring', $editing ? $expense->is_recurring : false) ? 'checked' : '' }}>
        {{ __('expenses.form.is_recurring') }}
    </label>
    <div id="exRecurringFields" class="{{ old('is_recurring', $editing ? $expense->is_recurring : false) ? '' : 'hidden' }} grid grid-cols-2 gap-4 mt-3">
        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.frequency') }}</label>
            <select name="recurrence_freq"
                    class="w-full px-3 py-2 rounded-lg border text-sm"
                    style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                @foreach (\App\Models\Expense::RECURRENCE as $freq)
                    <option value="{{ $freq }}" {{ old('recurrence_freq', $editing ? $expense->recurrence_freq : '') === $freq ? 'selected' : '' }}>
                        {{ __('expenses.recurrence.' . $freq) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.recurrence_end') }}</label>
            <input type="date" name="recurrence_end"
                   value="{{ old('recurrence_end', $editing ? $expense->recurrence_end?->toDateString() : '') }}"
                   class="w-full px-3 py-2 rounded-lg border text-sm"
                   style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
        </div>
    </div>
</div>

<style>
.ex-method-label:has(input:checked) {
    background: color-mix(in srgb, var(--app-brand) 12%, transparent);
    border-color: var(--app-brand);
    color: var(--app-brand);
}
</style>

<script>
const EX_SUB_CATS = @json(\App\Models\Expense::CATEGORIES);
const EX_INITIAL_CATEGORY = '{{ old('category', $editing ? $expense->category : '') }}';
const EX_INITIAL_SUB = '{{ old('sub_category', $editing ? $expense->sub_category : '') }}';

function exCategoryChanged(cat) {
    const subs = EX_SUB_CATS[cat] || [];
    const sel  = document.getElementById('exSubCategory');
    const wrap = document.getElementById('exSubWrap');
    sel.innerHTML = '<option value="">— {{ __('expenses.form.select_sub') }} —</option>';
    subs.forEach(s => {
        const o = document.createElement('option');
        o.value = s;
        // Simple label: replace underscores with spaces, capitalise
        o.textContent = s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        sel.appendChild(o);
    });
    wrap.style.display = subs.length ? '' : 'none';
    document.getElementById('exSalaryFields').classList.toggle('hidden', cat !== 'salaries');
    document.getElementById('exSalaryFields').style.display = cat === 'salaries' ? 'grid' : 'none';
}

function exMethodChanged(method) {
    document.querySelectorAll('.ex-method-label input').forEach(inp => {
        inp.parentElement.style.background = '';
        inp.parentElement.style.borderColor = 'var(--app-border)';
        inp.parentElement.style.color = 'var(--app-text)';
    });
    const checked = document.querySelector('.ex-method-label input:checked');
    if (checked) {
        checked.parentElement.style.background = 'color-mix(in srgb, var(--app-brand) 12%, transparent)';
        checked.parentElement.style.borderColor = 'var(--app-brand)';
        checked.parentElement.style.color = 'var(--app-brand)';
    }
}

function exRecurringChanged(checked) {
    document.getElementById('exRecurringFields').classList.toggle('hidden', !checked);
}

// Init
exCategoryChanged(EX_INITIAL_CATEGORY);
if (EX_INITIAL_SUB) {
    document.getElementById('exSubCategory').value = EX_INITIAL_SUB;
}
exMethodChanged('{{ old('method', $editing ? $expense->method : 'cash') }}');
</script>
