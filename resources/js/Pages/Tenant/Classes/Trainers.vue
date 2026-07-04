<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    trainers: Object,
    branches: Object,
    branchId: String,
});
</script>

<template>
    <AppLayout>
        <Head title="Trainers" />
        
        <div class="flex flex-col gap-5">
            <div>
                <h1 class="mt-2 text-3xl font-semibold">Trainers</h1>
                <p class="mt-1 text-slate-300">Manage your gym's class trainers and instructors.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select v-if="branches && branches.length > 0" class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 outline-none">
                    <option value="">All Branches</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                </select>
                <div class="ml-auto"></div>
                <Link href="/tenant/staff/create?role=trainer" class="flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    <span>+</span> Add Trainer
                </Link>
            </div>

            <div v-if="!trainers || trainers.length === 0" class="flex flex-col items-center gap-4 rounded-[2rem] border border-white/10 bg-white/5 py-20 text-center">
                <div class="flex h-[4.5rem] w-[4.5rem] items-center justify-center rounded-full border border-white/10 bg-slate-950/50 text-slate-400 text-2xl">ðŸ‘¨â€ðŸ«</div>
                <p class="text-lg font-bold">No trainers found</p>
                <p class="text-sm text-slate-400 max-w-xs">Add trainers to start managing your gym classes.</p>
            </div>

            <div v-else class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3">Name</th>
                                <th class="px-4 py-3">Specialisation</th>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3">Classes</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="trainer in trainers" :key="trainer.id" class="hover:bg-white/5">
                                <td class="whitespace-nowrap px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span v-if="!trainer.photo_url" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-orange-500/20 text-sm font-bold text-orange-300">
                                            {{ trainer.name.charAt(0).toUpperCase() }}
                                        </span>
                                        <div>
                                            <p class="font-semibold">{{ trainer.name }}</p>
                                            <p class="text-xs text-slate-400">{{ trainer.branch?.name || 'â€”' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-400">{{ trainer.specialisation || 'â€”' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-400">{{ trainer.phone || 'â€”' }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ trainer.classes_count || 0 }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="trainer.is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-500/15 text-slate-300'">
                                        {{ trainer.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <Link :href="`/tenant/staff/${trainer.id}`" class="text-orange-400 hover:text-orange-300">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

