<script setup>
import { computed, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    expense: {
        type: Object,
        default: null,
    },
    branches: {
        type: [Array, Object],
        default: () => [],
    },
    staffList: {
        type: [Array, Object],
        default: () => [],
    },
    categories: {
        type: Object,
        default: () => ({}),
    },
    methods: {
        type: [Array, Object],
        default: () => [],
    },
    recurrence: {
        type: [Array, Object],
        default: () => [],
    },
    selectedBranchId: {
        type: [String, Number],
        default: null,
    },
});

const editing = computed(() => !!props.expense);
const branchOptions = computed(() => Object.values(props.branches || {}));
const staffOptions = computed(() => Object.values(props.staffList || {}));
const methodOptions = computed(() => Object.values(props.methods || {}));
const recurrenceOptions = computed(() => Object.values(props.recurrence || {}));
const fixedBranchId = computed(() => {
    if (props.selectedBranchId) {
        return String(props.selectedBranchId);
    }

    return branchOptions.value.length === 1 ? String(branchOptions.value[0].id) : '';
});
const showBranchSelect = computed(() => branchOptions.value.length > 1 && !fixedBranchId.value);

const dateValue = (value) => {
    if (!value) {
        return new Date().toISOString().slice(0, 10);
    }

    return String(value).split('T')[0];
};

const form = useForm({
    date: dateValue(props.expense?.date),
    branch_id: fixedBranchId.value || props.expense?.branch_id || '',
    category: props.expense?.category || '',
    sub_category: props.expense?.sub_category || '',
    description: props.expense?.description || '',
    amount: props.expense?.amount_paise ? (props.expense.amount_paise / 100).toFixed(2) : '',
    gst: props.expense?.gst_paise ? (props.expense.gst_paise / 100).toFixed(2) : '0',
    method: props.expense?.method || methodOptions.value[0] || 'cash',
    reference: props.expense?.reference || '',
    vendor: props.expense?.vendor || '',
    receipt_url: props.expense?.receipt_url || '',
    notes: props.expense?.notes || '',
    staff_id: props.expense?.staff_id || '',
    salary_month: props.expense?.salary_month || '',
    is_recurring: Boolean(props.expense?.is_recurring),
    recurrence_freq: props.expense?.recurrence_freq || '',
    recurrence_end: props.expense?.recurrence_end ? dateValue(props.expense.recurrence_end) : '',
});

const categoryOptions = computed(() => Object.keys(props.categories || {}));
const subCategories = computed(() => props.categories?.[form.category] || []);
const showSalaryFields = computed(() => form.category === 'salaries');
const showRecurringFields = computed(() => Boolean(form.is_recurring));

watch(fixedBranchId, (branchId) => {
    if (branchId) {
        form.branch_id = branchId;
    }
}, { immediate: true });

watch(() => form.category, () => {
    if (!subCategories.value.includes(form.sub_category)) {
        form.sub_category = '';
    }

    if (form.category !== 'salaries') {
        form.staff_id = '';
        form.salary_month = '';
    }
});

watch(() => form.is_recurring, (enabled) => {
    if (!enabled) {
        form.recurrence_freq = '';
        form.recurrence_end = '';
    }
});

const titleCase = (value) => String(value || '-').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
const fieldError = (field) => form.errors?.[field] || '';

const submit = () => {
    if (fixedBranchId.value) {
        form.branch_id = fixedBranchId.value;
    }

    if (editing.value) {
        form.put(`/expenses/${props.expense.id}`);
        return;
    }

    form.post('/expenses');
};
</script>

<template>
    <form @submit.prevent="submit" class="app-panel max-w-4xl rounded-xl border p-4">
        <div class="grid gap-3 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium">Date <span class="text-red-400">*</span></label>
                <input v-model="form.date" type="date" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('date') }" required>
                <p v-if="fieldError('date')" class="field-error">{{ fieldError('date') }}</p>
            </div>

            <div v-if="showBranchSelect">
                <label class="mb-1.5 block text-sm font-medium">Branch <span class="text-red-400">*</span></label>
                <select v-model="form.branch_id" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('branch_id') }" required>
                    <option value="">Select Branch</option>
                    <option v-for="branch in branchOptions" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                </select>
                <p v-if="fieldError('branch_id')" class="field-error">{{ fieldError('branch_id') }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium">Category <span class="text-red-400">*</span></label>
                <select v-model="form.category" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('category') }" required>
                    <option value="">Select Category</option>
                    <option v-for="category in categoryOptions" :key="category" :value="category">{{ titleCase(category) }}</option>
                </select>
                <p v-if="fieldError('category')" class="field-error">{{ fieldError('category') }}</p>
            </div>

            <div v-if="subCategories.length">
                <label class="mb-1.5 block text-sm font-medium">Sub-category</label>
                <select v-model="form.sub_category" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('sub_category') }">
                    <option value="">Select Sub-category</option>
                    <option v-for="sub in subCategories" :key="sub" :value="sub">{{ titleCase(sub) }}</option>
                </select>
                <p v-if="fieldError('sub_category')" class="field-error">{{ fieldError('sub_category') }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium">Description <span class="text-red-400">*</span></label>
                <input v-model="form.description" type="text" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('description') }" required>
                <p v-if="fieldError('description')" class="field-error">{{ fieldError('description') }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium">Amount (Rs.) <span class="text-red-400">*</span></label>
                <input v-model="form.amount" type="number" step="0.01" min="0.01" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('amount') }" required>
                <p v-if="fieldError('amount')" class="field-error">{{ fieldError('amount') }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium">GST (Rs.)</label>
                <input v-model="form.gst" type="number" step="0.01" min="0" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('gst') }">
                <p v-if="fieldError('gst')" class="field-error">{{ fieldError('gst') }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium">Method <span class="text-red-400">*</span></label>
                <select v-model="form.method" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('method') }" required>
                    <option v-for="method in methodOptions" :key="method" :value="method">{{ titleCase(method) }}</option>
                </select>
                <p v-if="fieldError('method')" class="field-error">{{ fieldError('method') }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium">Reference</label>
                <input v-model="form.reference" type="text" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('reference') }">
                <p v-if="fieldError('reference')" class="field-error">{{ fieldError('reference') }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium">Vendor</label>
                <input v-model="form.vendor" type="text" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('vendor') }">
                <p v-if="fieldError('vendor')" class="field-error">{{ fieldError('vendor') }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium">Receipt URL</label>
                <input v-model="form.receipt_url" type="url" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('receipt_url') }" placeholder="https://...">
                <p v-if="fieldError('receipt_url')" class="field-error">{{ fieldError('receipt_url') }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium">Notes</label>
                <textarea v-model="form.notes" rows="2" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('notes') }"></textarea>
                <p v-if="fieldError('notes')" class="field-error">{{ fieldError('notes') }}</p>
            </div>
        </div>

        <div v-if="showSalaryFields" class="mt-4 grid gap-3 border-t pt-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium">Staff Member</label>
                <select v-model="form.staff_id" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('staff_id') }">
                    <option value="">Select Staff</option>
                    <option v-for="staff in staffOptions" :key="staff.id" :value="staff.id">{{ staff.name }} ({{ titleCase(staff.role) }})</option>
                </select>
                <p v-if="fieldError('staff_id')" class="field-error">{{ fieldError('staff_id') }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Salary Month</label>
                <input v-model="form.salary_month" type="month" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('salary_month') }">
                <p v-if="fieldError('salary_month')" class="field-error">{{ fieldError('salary_month') }}</p>
            </div>
        </div>

        <div class="mt-4 border-t pt-4">
            <label class="flex items-center gap-2 text-sm">
                <input v-model="form.is_recurring" type="checkbox" class="h-4 w-4 accent-orange-500">
                <span>Recurring expense</span>
            </label>
            <div v-if="showRecurringFields" class="mt-3 grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Frequency</label>
                    <select v-model="form.recurrence_freq" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('recurrence_freq') }">
                        <option value="">Select Frequency</option>
                        <option v-for="freq in recurrenceOptions" :key="freq" :value="freq">{{ titleCase(freq) }}</option>
                    </select>
                    <p v-if="fieldError('recurrence_freq')" class="field-error">{{ fieldError('recurrence_freq') }}</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Recurrence End</label>
                    <input v-model="form.recurrence_end" type="date" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': fieldError('recurrence_end') }">
                    <p v-if="fieldError('recurrence_end')" class="field-error">{{ fieldError('recurrence_end') }}</p>
                </div>
            </div>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <Link href="/expenses" class="app-panel rounded-lg border px-4 py-2 text-sm font-semibold transition hover:opacity-80">Cancel</Link>
            <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-orange-400 disabled:opacity-60" :disabled="form.processing">
                {{ form.processing ? 'Saving...' : (editing ? 'Update Expense' : 'Save Expense') }}
            </button>
        </div>
    </form>
</template>
