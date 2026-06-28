<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    branches: Object,
    staffList: Object,
    categories: Object,
    methods: Object,
    recurrence: Object,
});

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    branch_id: '',
    category: '',
    sub_category: '',
    description: '',
    amount: '',
    gst: '0',
    method: 'cash',
    reference: '',
    vendor: '',
    receipt_url: '',
    notes: '',
    staff_id: '',
    salary_month: '',
    is_recurring: false,
    recurrence_freq: '',
    recurrence_end: '',
});

const showSalaryFields = ref(false);
const showRecurringFields = ref(false);
const subCategories = ref([]);

const categoryChanged = (cat) => {
    const subs = props.categories[cat] || [];
    subCategories.value = subs;
    showSalaryFields.value = cat === 'salaries';
    form.sub_category = '';
};

const methodChanged = (method) => {
    form.method = method;
};

const recurringChanged = (checked) => {
    showRecurringFields.value = checked;
    if (!checked) {
        form.recurrence_freq = '';
        form.recurrence_end = '';
    }
};

const submit = () => {
    form.post('/tenant/expenses');
};

onMounted(() => {
    categoryChanged(form.category);
});
</script>

<template>
    <AppLayout>
        <Head title="Add Expense" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Add Expense</h1>
                    <p class="mt-0.5 text-sm text-slate-400">Record a new expense for your gym</p>
                </div>
                <Link href="/tenant/expenses" class="rounded-lg border border-white/10 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">
                    ← Expenses
                </Link>
            </div>

            <form @submit.prevent="submit" class="max-w-3xl rounded-2xl border border-white/10 bg-white/5 p-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Date <span class="text-red-400">*</span></label>
                        <input v-model="form.date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Branch <span class="text-red-400">*</span></label>
                        <select v-model="form.branch_id" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            <option value="">— Select Branch —</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Category <span class="text-red-400">*</span></label>
                        <select v-model="form.category" @change="categoryChanged(form.category)" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            <option value="">— Select Category —</option>
                            <option v-for="(label, cat) in categories" :key="cat" :value="cat">{{ label }}</option>
                        </select>
                    </div>
                    <div v-if="subCategories.length">
                        <label class="mb-1 block text-xs font-medium text-slate-400">Sub-category</label>
                        <select v-model="form.sub_category" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                            <option value="">— Select Sub —</option>
                            <option v-for="sub in subCategories" :key="sub" :value="sub">{{ sub.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-400">Description <span class="text-red-400">*</span></label>
                        <input v-model="form.description" type="text" minlength="5" maxlength="200" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Amount (₹) <span class="text-red-400">*</span></label>
                        <input v-model="form.amount" type="number" step="0.01" min="0.01" max="999999" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">GST (₹)</label>
                        <input v-model="form.gst" type="number" step="0.01" min="0" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-medium text-slate-400">Method <span class="text-red-400">*</span></label>
                        <div class="flex flex-wrap gap-2">
                            <label v-for="method in methods" :key="method" class="flex items-center gap-1.5 rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm cursor-pointer" :class="form.method === method ? 'border-orange-400 bg-orange-500/10 text-orange-400' : 'text-slate-300'">
                                <input type="radio" :value="method" v-model="form.method" class="hidden">
                                <span>{{ method.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) }}</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Reference</label>
                        <input v-model="form.reference" type="text" maxlength="100" placeholder="Reference number" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Vendor</label>
                        <input v-model="form.vendor" type="text" maxlength="100" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Receipt URL</label>
                        <input v-model="form.receipt_url" type="url" maxlength="500" placeholder="https://..." class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-400">Notes</label>
                        <textarea v-model="form.notes" rows="2" maxlength="500" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400"></textarea>
                    </div>
                </div>

                <div v-if="showSalaryFields" class="mt-4 grid gap-4 md:grid-cols-2 border-t border-white/10 pt-4">
                    <div class="md:col-span-2">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Salary Details</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Staff Member</label>
                        <select v-model="form.staff_id" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                            <option value="">— Select Staff —</option>
                            <option v-for="staff in staffList" :key="staff.id" :value="staff.id">{{ staff.name }} ({{ staff.role }})</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Salary Month</label>
                        <input v-model="form.salary_month" type="month" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                </div>

                <div class="mt-4 border-t border-white/10 pt-4">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-300">
                        <input v-model="form.is_recurring" type="checkbox" @change="recurringChanged(form.is_recurring)" class="text-orange-400">
                        <span>Is Recurring</span>
                    </label>
                    <div v-if="showRecurringFields" class="mt-3 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">Frequency</label>
                            <select v-model="form.recurrence_freq" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                <option v-for="(label, freq) in recurrence" :key="freq" :value="freq">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">Recurrence End</label>
                            <input v-model="form.recurrence_end" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        Save Expense
                    </button>
                    <Link href="/tenant/expenses" class="rounded-lg border border-white/10 px-6 py-2.5 text-sm text-slate-300 hover:bg-white/5">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>