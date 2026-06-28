<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    member: Object,
    prefill: Object,
    plans: Array,
});

const editing = !!props.member;
const pageTitle = editing ? 'Edit member' : 'Add new member';
const pageSub = editing ? `Update details for ${props.member?.name}.` : 'Register a new member at your gym.';

const form = useForm({
    name: props.member?.name || props.prefill?.name || '',
    phone: props.member?.phone || props.prefill?.phone || '',
    email: props.member?.email || '',
    gender: props.member?.gender || '',
    dob: props.member?.dob || '',
    address: props.member?.address || '',
    plan_id: props.member?.plan_id || '',
    branch_id: props.member?.branch_id || '',
});

const submit = () => {
    if (editing) {
        form.put(`/tenant/members/${props.member.id}`);
    } else {
        form.post('/tenant/members');
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="pageTitle" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Members</p>
                    <h1 class="mt-2 text-3xl font-semibold">{{ pageTitle }}</h1>
                    <p class="mt-1 text-slate-300">{{ pageSub }}</p>
                </div>
                <Link href="/tenant/members" class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">
                    ← Back to members
                </Link>
            </div>

            <div v-if="form.errors" class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                {{ Object.values(form.errors)[0] }}
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h3 class="text-lg font-bold mb-4">Personal information</h3>
                    
                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Full name <span class="text-red-400">*</span></label>
                            <input v-model="form.name" type="text" placeholder="e.g. Priya Sharma" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required maxlength="100">
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium">Phone <span class="text-red-400">*</span></label>
                                <input v-model="form.phone" type="tel" placeholder="+91 98000 00000" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required maxlength="20">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium">Email</label>
                                <input v-model="form.email" type="email" placeholder="Optional" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" maxlength="255">
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium">Gender</label>
                                <div class="flex gap-4 pt-1">
                                    <label class="flex items-center gap-2">
                                        <input v-model="form.gender" type="radio" value="male" class="accent-orange-500">
                                        <span class="text-sm">Male</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input v-model="form.gender" type="radio" value="female" class="accent-orange-500">
                                        <span class="text-sm">Female</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input v-model="form.gender" type="radio" value="other" class="accent-orange-500">
                                        <span class="text-sm">Other</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium">Date of Birth</label>
                                <input v-model="form.dob" type="date" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400">
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Address</label>
                            <textarea v-model="form.address" rows="3" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400"></textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h3 class="text-lg font-bold mb-4">Membership details</h3>
                    
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Plan</label>
                            <select v-model="form.plan_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400">
                                <option value="">Select a plan</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Branch</label>
                            <select v-model="form.branch_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400">
                                <option value="">Select a branch</option>
                                <option value="1">Main Branch</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <Link href="/tenant/members" class="rounded-2xl border border-white/10 bg-slate-950/50 px-6 py-3 text-sm font-semibold text-slate-300 hover:bg-white/5">
                        Cancel
                    </Link>
                    <button type="submit" class="rounded-2xl bg-orange-500 px-6 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        {{ editing ? 'Update Member' : 'Create Member' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>