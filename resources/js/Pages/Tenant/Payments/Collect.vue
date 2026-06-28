<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    member: Object,
    plans: Array,
    prefillAmount: Number,
});

const selectedMember = ref(props.member);
const splits = ref([{ amount: '', method: 'cash', reference: '' }]);

const form = useForm({
    member_id: props.member?.id || '',
    plan_id: '',
    amount: props.prefillAmount || '',
    payment_date: new Date().toISOString().split('T')[0],
    method: 'cash',
    reference: '',
    splits: splits.value,
});

const addSplit = () => {
    splits.value.push({ amount: '', method: 'cash', reference: '' });
};

const removeSplit = (index) => {
    splits.value.splice(index, 1);
};

const submit = () => {
    form.splits = splits.value;
    form.post('/tenant/payments');
};
</script>

<template>
    <AppLayout>
        <Head title="Collect Fee" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Payments</p>
                    <h1 class="mt-2 text-3xl font-semibold">Collect Fee</h1>
                    <p class="mt-1 text-slate-300">Record payment from a member.</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/tenant/payments?tab=history" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">
                        History
                    </Link>
                    <Link href="/tenant/payments?tab=dues" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">
                        Dues
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-5 lg:grid-cols-1">
                <div class="lg:col-span-3 flex flex-col gap-4">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                        <h2 class="font-medium mb-3">Member</h2>
                        <div class="relative">
                            <input type="text" placeholder="Search by name or phone..." class="w-full rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div v-if="selectedMember" class="mt-3 rounded-lg border border-white/10 bg-slate-950/50 p-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-sm">{{ selectedMember.name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ selectedMember.phone }}</p>
                                </div>
                                <div class="text-sm font-semibold">{{ selectedMember.balance_rupees || '₹0.00' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                        <h2 class="font-medium mb-4">Payment Details</h2>
                        
                        <div class="flex flex-col gap-4">
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-400">Plan</label>
                                <select v-model="form.plan_id" class="w-full rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    <option value="">Select a plan</option>
                                    <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }} (₹{{ (plan.amount_paise / 100).toFixed(0) }})</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-400">Amount (₹)</label>
                                <input v-model="form.amount" type="number" class="w-full rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-400">Payment Date</label>
                                <input v-model="form.payment_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-400">Method</label>
                                <select v-model="form.method" class="w-full rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-400">Reference / Notes</label>
                                <input v-model="form.reference" type="text" placeholder="Transaction ID or notes" class="w-full rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-orange-400/30 bg-orange-500/5 p-4">
                        <h3 class="text-sm font-bold text-orange-400 mb-3">Split Payment</h3>
                        <div v-for="(split, index) in splits" :key="index" class="mb-3 grid gap-2 grid-cols-[1fr_1fr_1fr_auto] items-end sm:grid-cols-1">
                            <div>
                                <label class="mb-1 block text-xs text-slate-400">Amount</label>
                                <input v-model="split.amount" type="number" placeholder="₹" class="w-full rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-slate-400">Method</label>
                                <select v-model="split.method" class="w-full rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-slate-400">Reference</label>
                                <input v-model="split.reference" type="text" placeholder="Ref" class="w-full rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                            </div>
                            <button v-if="splits.length > 1" @click="removeSplit(index)" class="rounded border border-white/10 bg-transparent px-2 py-1.5 text-slate-400 hover:bg-red-500/10 hover:text-red-400">
                                ×
                            </button>
                        </div>
                        <button @click="addSplit" class="flex items-center gap-2 rounded border border-dashed border-white/10 px-3 py-1.5 text-sm font-semibold text-slate-400 hover:border-orange-400 hover:text-orange-400">
                            + Add Split
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                        <h2 class="font-medium mb-4">Summary</h2>
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Total Amount</span>
                                <span class="font-semibold">₹{{ form.amount || '0.00' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Splits</span>
                                <span class="font-semibold">{{ splits.length }}</span>
                            </div>
                            <div class="border-t border-white/10 pt-3">
                                <button @click="submit" class="w-full rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                                    Record Payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
