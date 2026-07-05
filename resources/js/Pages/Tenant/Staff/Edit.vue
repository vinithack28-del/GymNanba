<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    staff: Object,
    roles: {
        type: [Array, Object],
        default: () => [],
    },
    branches: {
        type: [Array, Object],
        default: () => [],
    },
    selectedBranchId: {
        type: [String, Number],
        default: null,
    },
});

const branchOptions = computed(() => Object.values(props.branches || {}));
const fixedBranchId = computed(() => {
    if (props.selectedBranchId) {
        return String(props.selectedBranchId);
    }

    return branchOptions.value.length === 1 ? String(branchOptions.value[0].id) : '';
});
const showBranchSelect = computed(() => branchOptions.value.length > 1 && !fixedBranchId.value);

const form = useForm({
    name: props.staff.name,
    phone: props.staff.phone,
    email: props.staff.email,
    role: props.staff.role,
    branch_id: fixedBranchId.value || props.staff.branch_id || '',
    join_date: props.staff.join_date,
    address: props.staff.address,
    emergency_contact: props.staff.emergency_contact,
    emergency_phone: props.staff.emergency_phone,
});

watch(fixedBranchId, (branchId) => {
    if (branchId) {
        form.branch_id = branchId;
    }
}, { immediate: true });

const submit = () => {
    if (fixedBranchId.value) {
        form.branch_id = fixedBranchId.value;
    }

    form.put(`/staff/${props.staff.id}`);
};
</script>

<template>
    <AppLayout>
        <Head title="Edit Staff" />
        
        <div class="flex flex-col gap-5">
            <div>
                <h1 class="mt-2 text-3xl font-semibold">Edit Staff</h1>
                <p class="mt-1 text-slate-300">Update staff member information.</p>
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-5 text-base font-semibold uppercase tracking-[0.16em] text-slate-400">Personal & Role</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Full Name <span class="text-red-400">*</span></label>
                            <input v-model="form.name" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Phone <span class="text-red-400">*</span></label>
                            <input v-model="form.phone" type="text" placeholder="+919876543210" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Email <span class="text-red-400">*</span></label>
                            <input v-model="form.email" type="email" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Role <span class="text-red-400">*</span></label>
                            <select v-model="form.role" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                                <option value="">Select Role</option>
                                <option v-for="role in roles" :key="role.role" :value="role.role">{{ role.display_name || role.role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</option>
                            </select>
                        </div>
                        <div v-if="showBranchSelect">
                            <label class="mb-2 block text-sm font-medium">Branch <span class="text-red-400">*</span></label>
                            <select v-model="form.branch_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                                <option value="">Select Branch</option>
                                <option v-for="branch in branchOptions" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Join Date <span class="text-red-400">*</span></label>
                            <input v-model="form.join_date" type="date" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-5 text-base font-semibold uppercase tracking-[0.16em] text-slate-400">Additional Info</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Address</label>
                            <input v-model="form.address" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Emergency Contact</label>
                            <input v-model="form.emergency_contact" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Emergency Phone</label>
                            <input v-model="form.emergency_phone" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link href="/staff" class="rounded-2xl border border-white/10 bg-slate-950/50 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-white/5">Cancel</Link>
                    <button type="submit" class="rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">Update Staff</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

