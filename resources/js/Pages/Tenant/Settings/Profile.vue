<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tenant: Object,
});

const businessTypes = {
    gym: 'Gym',
    yoga_studio: 'Yoga Studio',
    crossfit: 'CrossFit',
    martial_arts: 'Martial Arts',
    dance: 'Dance Studio',
    sports_club: 'Sports Club',
    other: 'Other',
};

const form = useForm({
    gym_name: props.tenant.gym_name,
    business_type: props.tenant.business_type,
    logo: null,
    cover_photo: null,
    address: props.tenant.address,
    city: props.tenant.city,
    state: props.tenant.state,
    pincode: props.tenant.pincode,
    phone: props.tenant.phone,
    email: props.tenant.email,
    gstin: props.tenant.gstin,
    website: props.tenant.website,
});

const submit = () => {
    form.put('/settings/profile', {
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Profile Settings" />
        
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-xl font-semibold">Settings</h1>
                <p class="mt-0.5 text-sm text-slate-400">Manage your gym profile and business information.</p>
            </div>

            <div class="flex gap-2">
                <Link href="/settings/account" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Account</Link>
                <Link href="/settings/profile" class="rounded-lg bg-orange-500 px-3 py-1.5 text-sm font-medium text-slate-950">Profile</Link>
                <Link href="/settings/integrations" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Integrations</Link>
                <Link href="/settings/language" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Language</Link>
                <Link href="/settings/data" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Data</Link>
            </div>

            <form @submit.prevent="submit" enctype="multipart/form-data" class="flex flex-col gap-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold">Logo & Cover</h2>
                    <div class="flex flex-col gap-6 sm:flex-row">
                        <div>
                            <label class="mb-2 block text-xs text-slate-400">Logo</label>
                            <img v-if="tenant.logo_url" :src="tenant.logo_url" alt="Logo" class="mb-2 h-16 w-16 rounded-xl border border-white/10 object-cover">
                            <input type="file" @input="form.logo = $event.target.files[0]" accept=".jpg,.jpeg,.png,.svg" class="block text-xs text-slate-400">
                            <p class="mt-1 text-xs text-slate-400">JPG/PNG/SVG · max 2 MB</p>
                        </div>
                        <div class="flex-1">
                            <label class="mb-2 block text-xs text-slate-400">Cover Photo</label>
                            <img v-if="tenant.cover_photo_url" :src="tenant.cover_photo_url" alt="Cover" class="mb-2 h-20 w-full max-w-xs rounded-xl border border-white/10 object-cover">
                            <input type="file" @input="form.cover_photo = $event.target.files[0]" accept=".jpg,.jpeg,.png" class="block text-xs text-slate-400">
                            <p class="mt-1 text-xs text-slate-400">JPG/PNG · max 5 MB</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold">Basic Info</h2>
                    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-slate-400">Gym Name <span class="text-red-400">*</span></label>
                            <input v-model="form.gym_name" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">Business Type <span class="text-red-400">*</span></label>
                            <select v-model="form.business_type" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                                <option v-for="(label, value) in businessTypes" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">Phone</label>
                            <input v-model="form.phone" type="text" placeholder="+91XXXXXXXXXX" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">Email</label>
                            <input v-model="form.email" type="email" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">Website</label>
                            <input v-model="form.website" type="url" placeholder="https://" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-slate-400">Address</label>
                            <input v-model="form.address" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">City</label>
                            <input v-model="form.city" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">State</label>
                            <input v-model="form.state" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">Pincode</label>
                            <input v-model="form.pincode" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">GSTIN</label>
                            <input v-model="form.gstin" type="text" placeholder="22AAAAA0000A1Z5" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">Save Changes</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
