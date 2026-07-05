<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    sessions: Object,
    twoFactorEnabled: Boolean,
});
</script>

<template>
    <AppLayout>
        <Head title="Security Settings" />
        
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-xl font-semibold">Settings</h1>
                <p class="mt-0.5 text-sm text-slate-400">Manage account security and sessions.</p>
            </div>

            <div class="flex gap-2">
                <Link href="/settings/account" class="rounded-lg bg-orange-500 px-3 py-1.5 text-sm font-medium text-slate-950">Account</Link>
                <Link href="/settings/profile" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Profile</Link>
                <Link href="/settings/integrations" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Integrations</Link>
                <Link href="/settings/language" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Language</Link>
                <Link href="/settings/data" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Data</Link>
            </div>

            <div class="flex flex-col gap-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold">Two-Factor Authentication</h2>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">2FA Status</p>
                            <p class="text-xs text-slate-400">{{ twoFactorEnabled ? 'Enabled' : 'Disabled' }}</p>
                        </div>
                        <button class="rounded-xl border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">
                            {{ twoFactorEnabled ? 'Disable' : 'Enable' }}
                        </button>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold">Active Sessions</h2>
                    <div v-if="!sessions || sessions.length === 0" class="text-sm text-slate-400">No active sessions.</div>
                    <div v-else class="space-y-3">
                        <div v-for="session in sessions" :key="session.id" class="flex items-center justify-between rounded-xl border border-white/10 bg-slate-950/50 p-4">
                            <div>
                                <p class="text-sm font-medium">{{ session.device }}</p>
                                <p class="text-xs text-slate-400">{{ session.ip }} - {{ session.location }}</p>
                                <p class="text-xs text-slate-400">Last active: {{ session.last_active }}</p>
                            </div>
                            <button class="rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-1.5 text-xs font-medium text-red-400 hover:bg-red-500/20">Revoke</button>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold">Login History</h2>
                    <p class="text-sm text-slate-400">View recent login attempts and security events.</p>
                    <button class="mt-4 rounded-xl border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">View History</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

