<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    staff: Object,
    tab: String,
    canManage: Boolean,
});

const roleColors = {
    receptionist: 'bg-cyan-500/10 text-cyan-300 border-cyan-400/20',
    trainer: 'bg-purple-500/10 text-purple-300 border-purple-400/20',
    accountant: 'bg-amber-500/10 text-amber-300 border-amber-400/20',
    pos: 'bg-blue-500/10 text-blue-300 border-blue-400/20',
    branch_manager: 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20',
};

const getRoleClass = (role) => {
    return roleColors[role] || 'bg-orange-500/10 text-orange-300 border-orange-400/20';
};

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};
</script>

<template>
    <AppLayout>
        <Head :title="staff.name" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="mt-2 text-3xl font-semibold">{{ staff.name }}</h1>
                    <p class="mt-1 text-slate-300">{{ staff.role_label }} Â· {{ staff.branch?.name || 'â€”' }}</p>
                </div>
                <div class="flex gap-3">
                    <Link v-if="canManage" :href="`/tenant/staff/${staff.id}/edit`" class="rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400">Edit</Link>
                    <Link href="/tenant/staff" class="rounded-2xl border border-white/10 bg-slate-950/50 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-white/5">Back</Link>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-5 rounded-2xl border border-white/10 bg-white/5 p-6">
                <div v-if="staff.photo_url" class="h-20 w-20 shrink-0 rounded-full border-4 border-white/10 object-cover bg-slate-800">
                    <img :src="staff.photo_url" :alt="staff.name" class="h-full w-full rounded-full object-cover">
                </div>
                <div v-else class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-orange-500/15 text-2xl font-bold text-orange-400">
                    {{ getInitials(staff.name) }}
                </div>
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-xl font-semibold">{{ staff.name }}</h2>
                        <span class="rounded-full border px-3 py-1 text-xs font-semibold" :class="getRoleClass(staff.role)">
                            {{ staff.role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                        </span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="staff.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-400/20' : 'bg-red-500/10 text-red-300 border border-red-400/20'">
                            {{ staff.status.charAt(0).toUpperCase() + staff.status.slice(1) }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-400">{{ staff.email }} Â· {{ staff.phone }}</p>
                    <p class="text-sm text-slate-400">{{ staff.branch?.name || 'â€”' }} Â· Joined {{ staff.join_date }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link :href="`/tenant/staff/${staff.id}?tab=details`" class="rounded-full px-5 py-2 text-sm font-semibold transition-colors" :class="tab === 'details' ? 'bg-orange-500 text-slate-950' : 'border border-white/10 bg-slate-950/50 text-slate-300 hover:bg-white/5'">Details</Link>
                <Link :href="`/tenant/staff/${staff.id}?tab=logins`" class="rounded-full px-5 py-2 text-sm font-semibold transition-colors" :class="tab === 'logins' ? 'bg-orange-500 text-slate-950' : 'border border-white/10 bg-slate-950/50 text-slate-300 hover:bg-white/5'">Logins</Link>
                <Link :href="`/tenant/staff/${staff.id}?tab=attendance`" class="rounded-full px-5 py-2 text-sm font-semibold transition-colors" :class="tab === 'attendance' ? 'bg-orange-500 text-slate-950' : 'border border-white/10 bg-slate-950/50 text-slate-300 hover:bg-white/5'">Attendance</Link>
                <Link :href="`/tenant/staff/${staff.id}?tab=documents`" class="rounded-full px-5 py-2 text-sm font-semibold transition-colors" :class="tab === 'documents' ? 'bg-orange-500 text-slate-950' : 'border border-white/10 bg-slate-950/50 text-slate-300 hover:bg-white/5'">Documents</Link>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <div v-if="tab === 'details'">
                    <h3 class="mb-4 text-sm font-semibold">Personal Information</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs text-slate-400">Full Name</p>
                            <p class="text-sm font-medium">{{ staff.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Email</p>
                            <p class="text-sm font-medium">{{ staff.email }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Phone</p>
                            <p class="text-sm font-medium">{{ staff.phone }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Role</p>
                            <p class="text-sm font-medium">{{ staff.role_label }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Branch</p>
                            <p class="text-sm font-medium">{{ staff.branch?.name || 'â€”' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Join Date</p>
                            <p class="text-sm font-medium">{{ staff.join_date }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs text-slate-400">Address</p>
                            <p class="text-sm font-medium">{{ staff.address || 'â€”' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Emergency Contact</p>
                            <p class="text-sm font-medium">{{ staff.emergency_contact || 'â€”' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Emergency Phone</p>
                            <p class="text-sm font-medium">{{ staff.emergency_phone || 'â€”' }}</p>
                        </div>
                    </div>
                </div>
                <div v-else-if="tab === 'logins'">
                    <h3 class="mb-4 text-sm font-semibold">Login History</h3>
                    <p class="text-sm text-slate-400">View recent login attempts and activity.</p>
                </div>
                <div v-else-if="tab === 'attendance'">
                    <h3 class="mb-4 text-sm font-semibold">Attendance Records</h3>
                    <p class="text-sm text-slate-400">View staff attendance and check-in records.</p>
                </div>
                <div v-else-if="tab === 'documents'">
                    <h3 class="mb-4 text-sm font-semibold">Documents</h3>
                    <p class="text-sm text-slate-400">View and manage staff documents.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

