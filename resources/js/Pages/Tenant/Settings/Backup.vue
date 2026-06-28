<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    backups: Object,
    lastBackup: Object,
});
</script>

<template>
    <AppLayout>
        <Head title="Backup & Restore" />
        
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-xl font-semibold">Settings</h1>
                <p class="mt-0.5 text-sm text-slate-400">Backup and restore your gym data.</p>
            </div>

            <div class="flex gap-2">
                <Link href="/tenant/settings/account" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Account</Link>
                <Link href="/tenant/settings/profile" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Profile</Link>
                <Link href="/tenant/settings/integrations" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Integrations</Link>
                <Link href="/tenant/settings/notifications" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Notifications</Link>
                <Link href="/tenant/settings/security" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Security</Link>
            </div>

            <div class="flex flex-col gap-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold">Create Backup</h2>
                    <p class="mb-4 text-sm text-slate-400">Download a complete backup of your gym data including members, payments, and settings.</p>
                    <button class="rounded-xl bg-orange-500 px-5 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">Download Backup</button>
                </div>

                <div v-if="lastBackup" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold">Last Backup</h2>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">{{ lastBackup.filename }}</p>
                            <p class="text-xs text-slate-400">{{ lastBackup.date }} · {{ lastBackup.size }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400">Completed</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold">Restore from Backup</h2>
                    <p class="mb-4 text-sm text-slate-400">Restore your gym data from a previous backup file.</p>
                    <div class="flex items-center gap-4">
                        <input type="file" accept=".json,.sql" class="block text-sm text-slate-400">
                        <button class="rounded-xl border border-white/10 bg-slate-950/50 px-5 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">Restore</button>
                    </div>
                </div>

                <div class="rounded-2xl border border-red-500/20 bg-red-500/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold text-red-400">Danger Zone</h2>
                    <p class="mb-4 text-sm text-slate-400">Permanently delete all data. This action cannot be undone.</p>
                    <button class="rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-2 text-sm font-medium text-red-400 hover:bg-red-500/20">Delete All Data</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>