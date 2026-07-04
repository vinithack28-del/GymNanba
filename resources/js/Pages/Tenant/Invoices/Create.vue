<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    branches: Object,
    selectedBranchId: String,
});

const form = useForm({
    member_id: '',
    invoice_date: new Date().toISOString().split('T')[0],
    due_date: '',
    branch_id: props.selectedBranchId || '',
    line_items: [{ description: '', quantity: 1, rate_paise: '', gst_rate: 18 }],
    notes: '',
});

const selectedMember = ref(null);
const showMemberCard = ref(false);

const addLineItem = () => {
    form.line_items.push({ description: '', quantity: 1, rate_paise: '', gst_rate: 18 });
};

const removeLineItem = (index) => {
    if (form.line_items.length > 1) {
        form.line_items.splice(index, 1);
    }
};

const selectMember = (member) => {
    selectedMember.value = member;
    form.member_id = member.id;
    showMemberCard.value = true;
};

const clearMember = () => {
    selectedMember.value = null;
    form.member_id = '';
    showMemberCard.value = false;
};

const submit = () => {
    form.post('/tenant/invoices');
};
</script>

<template>
    <AppLayout>
        <Head title="Create Invoice" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Create Invoice</h1>
                    <p class="mt-0.5 text-sm text-slate-400">Create a new invoice for a member</p>
                </div>
                <Link href="/tenant/invoices" class="rounded-lg border border-white/10 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">
                    â† Invoices
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-5">
                <div class="lg:col-span-3 flex flex-col gap-5">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <h2 class="mb-3 font-medium">Member</h2>
                        <div class="relative">
                            <input type="text" placeholder="Search member by name or code..." class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div v-if="showMemberCard" class="mt-3 rounded-lg border border-white/10 bg-slate-950/50 p-3">
                            <p class="font-medium text-sm">{{ selectedMember?.name }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ selectedMember?.phone }} â€¢ {{ selectedMember?.member_code }}</p>
                            <button @click="clearMember" class="mt-2 text-xs text-slate-400">âœ• Change member</button>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <input type="hidden" v-model="form.member_id">
                        
                        <h2 class="mb-4 font-medium">Invoice Details</h2>
                        
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-400">Invoice Date</label>
                                <input v-model="form.invoice_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-400">Due Date</label>
                                <input v-model="form.due_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="mb-1 block text-xs font-medium text-slate-400">Branch</label>
                            <select v-model="form.branch_id" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                <option value="">â€” No Branch â€”</option>
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                            </select>
                        </div>

                        <div class="mt-4">
                            <div class="mb-2 flex items-center justify-between">
                                <h3 class="font-medium">Line Items</h3>
                                <button type="button" @click="addLineItem" class="text-xs text-orange-400 hover:text-orange-300">+ Add Item</button>
                            </div>
                            <div v-for="(item, index) in form.line_items" :key="index" class="mb-3 rounded-lg border border-white/10 bg-slate-950/50 p-3">
                                <div class="grid gap-3 md:grid-cols-4">
                                    <div class="md:col-span-2">
                                        <label class="mb-1 block text-xs font-medium text-slate-400">Description</label>
                                        <input v-model="item.description" type="text" placeholder="Item description" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-400">Qty</label>
                                        <input v-model="item.quantity" type="number" min="1" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-400">Rate (â‚¹)</label>
                                        <input v-model="item.rate_paise" type="number" step="0.01" placeholder="0.00" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center gap-3">
                                    <div class="flex-1">
                                        <label class="mb-1 block text-xs font-medium text-slate-400">GST Rate (%)</label>
                                        <select v-model="item.gst_rate" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                            <option value="0">0%</option>
                                            <option value="5">5%</option>
                                            <option value="12">12%</option>
                                            <option value="18">18%</option>
                                            <option value="28">28%</option>
                                        </select>
                                    </div>
                                    <button v-if="form.line_items.length > 1" type="button" @click="removeLineItem(index)" class="mt-4 text-xs text-red-400 hover:text-red-300">Remove</button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="mb-1 block text-xs font-medium text-slate-400">Notes</label>
                            <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400"></textarea>
                        </div>

                        <button type="submit" class="mt-6 w-full rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                            Create Invoice
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <h2 class="mb-4 font-medium">Summary</h2>
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Subtotal</span>
                                <span>â‚¹0.00</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">GST</span>
                                <span>â‚¹0.00</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span>â‚¹0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
