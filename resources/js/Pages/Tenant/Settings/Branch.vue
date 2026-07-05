<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    branches: Object,
    defaultBranch: Object,
});

const form = useForm({
    default_branch_id: props.defaultBranch?.id || '',
});

const submit = () => {
    form.put('/settings/profile');
};
</script>

<template>
    <AppLayout>
        <Head title="Branch Settings" />
        
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-xl font-semibold">Settings</h1>
                <p class="mt-0.5 text-sm text-slate-400">Manage default branch and multi-location settings.</p>
            </div>

            <div class="flex gap-2">
                <Link href="/settings/account" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Account</Link>
                <Link href="/settings/profile" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Profile</Link>
                <Link href="/settings/integrations" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Integrations</Link>
                <Link href="/settings/language" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Language</Link>
                <Link href="/settings/data" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Data</Link>
            </div>

            <div class="flex flex-col gap-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold">Default Branch</h2>
                    <p class="mb-4 text-sm text-slate-400">Select the default branch for new members and operations.</p>
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label class="mb-1 block text-xs font-medium text-slate-400">Default Branch</label>
                            <select v-model="form.default_branch_id" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                <option value="">Select a branchâ€¦</option>
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                            </select>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">Save</button>
                        </div>
                    </form>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold">Branch Management</h2>
                    <p class="mb-4 text-sm text-slate-400">Manage multiple gym branches and locations.</p>
                    <div v-if="!branches || branches.length === 0" class="text-sm text-slate-400">No branches configured.</div>
                    <div v-else class="space-y-3">
                        <div v-for="branch in branches" :key="branch.id" class="flex items-center justify-between rounded-xl border border-white/10 bg-slate-950/50 p-4">
                            <div>
                                <p class="text-sm font-medium">{{ branch.name }}</p>
                                <p class="text-xs text-slate-400">{{ branch.address }}</p>
                            </div>
                            <span v-if="branch.id === defaultBranch?.id" class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400">Default</span>
                        </div>
                    </div>
                    <Link href="/tenant/branches" class="mt-4 inline-flex items-center gap-2 rounded-xl border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">
                    Manage Branches ->
                </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

