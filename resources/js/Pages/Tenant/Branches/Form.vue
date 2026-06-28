<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    branch: Object,
});

const editing = !!props.branch;
const pageTitle = editing ? 'Edit Branch' : 'Add Branch';

const amenityIcons = {
    pool: '🏊',
    steam: '💨',
    parking: '🅿',
    locker: '🔒',
    cafeteria: '☕',
    ac: '❄',
    wifi: '📶',
};

const defaultHours = {
    mon: { open: '06:00', close: '22:00', closed: false },
    tue: { open: '06:00', close: '22:00', closed: false },
    wed: { open: '06:00', close: '22:00', closed: false },
    thu: { open: '06:00', close: '22:00', closed: false },
    fri: { open: '06:00', close: '22:00', closed: false },
    sat: { open: '07:00', close: '20:00', closed: false },
    sun: { open: '08:00', close: '14:00', closed: false },
};

const form = useForm({
    name: props.branch?.name || '',
    phone: props.branch?.phone || '',
    email: props.branch?.email || '',
    manager_name: props.branch?.manager_name || '',
    gst_number: props.branch?.gst_number || '',
    status: props.branch?.status || 'active',
    address1: props.branch?.address1 || '',
    address2: props.branch?.address2 || '',
    city: props.branch?.city || '',
    pin: props.branch?.pin || '',
    state: props.branch?.state || '',
    amenities: props.branch?.amenities || [],
    operating_hours: props.branch?.operating_hours || defaultHours,
});

const submit = () => {
    if (editing) {
        form.put(`/tenant/branches/${props.branch.id}`);
    } else {
        form.post('/tenant/branches');
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="pageTitle" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Branches</p>
                    <h1 class="mt-2 text-3xl font-semibold">{{ pageTitle }}</h1>
                    <p class="mt-1 text-slate-300">{{ editing ? `Update details for ${branch.name}.` : 'Set up a new location for your gym.' }}</p>
                </div>
                <Link href="/tenant/branches" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7-7"/></svg>
                    Back to Branches
                </Link>
            </div>

            <form @submit.prevent="submit" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="mb-5 text-base font-semibold uppercase tracking-[0.16em] text-slate-400">Basic Info</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Branch Name <span class="text-red-400">*</span></label>
                        <input v-model="form.name" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Phone <span class="text-red-400">*</span></label>
                        <input v-model="form.phone" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Email</label>
                        <input v-model="form.email" type="email" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Manager Name <span class="text-red-400">*</span></label>
                        <input v-model="form.manager_name" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">GST Number</label>
                        <input v-model="form.gst_number" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Status <span class="text-red-400">*</span></label>
                        <select v-model="form.status" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <h2 class="mb-5 mt-6 text-base font-semibold uppercase tracking-[0.16em] text-slate-400">Address</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Address Line 1 <span class="text-red-400">*</span></label>
                        <input v-model="form.address1" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Address Line 2</label>
                        <input v-model="form.address2" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">City <span class="text-red-400">*</span></label>
                        <input v-model="form.city" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">PIN Code <span class="text-red-400">*</span></label>
                        <input v-model="form.pin" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">State <span class="text-red-400">*</span></label>
                        <input v-model="form.state" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                </div>

                <h2 class="mb-5 mt-6 text-base font-semibold uppercase tracking-[0.16em] text-slate-400">Amenities</h2>
                <div class="grid gap-3 md:grid-cols-4">
                    <label v-for="(icon, key) in amenityIcons" :key="key" class="flex items-center gap-3 rounded-xl border border-white/10 bg-slate-950/50 p-3 cursor-pointer hover:bg-white/5">
                        <input v-model="form.amenities" type="checkbox" :value="key" class="h-5 w-5 rounded border-white/10 bg-slate-950/50 text-orange-500 focus:ring-orange-400">
                        <span class="text-2xl">{{ icon }}</span>
                        <span class="text-sm font-medium">{{ key.charAt(0).toUpperCase() + key.slice(1) }}</span>
                    </label>
                </div>

                <h2 class="mb-5 mt-6 text-base font-semibold uppercase tracking-[0.16em] text-slate-400">Operating Hours</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="(hours, day) in form.operating_hours" :key="day" class="flex items-center gap-3 rounded-xl border border-white/10 bg-slate-950/50 p-3">
                        <input v-model="hours.closed" type="checkbox" class="h-5 w-5 rounded border-white/10 bg-slate-950/50 text-orange-500 focus:ring-orange-400">
                        <span class="w-16 text-sm font-medium">{{ day.charAt(0).toUpperCase() + day.slice(1) }}</span>
                        <input v-model="hours.open" type="time" :disabled="hours.closed" class="flex-1 rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400 disabled:opacity-50">
                        <span class="text-slate-400">to</span>
                        <input v-model="hours.close" type="time" :disabled="hours.closed" class="flex-1 rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400 disabled:opacity-50">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <Link href="/tenant/branches" class="rounded-2xl border border-white/10 bg-slate-950/50 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-white/5">Cancel</Link>
                    <button type="submit" class="rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">{{ editing ? 'Update Branch' : 'Create Branch' }}</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>