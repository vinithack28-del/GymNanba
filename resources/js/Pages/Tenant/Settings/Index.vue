<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

const profileForm = useForm({
    name: props.user.name,
    phone: props.user.phone,
    avatar: null,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitProfile = () => {
    profileForm.put('/settings/account', {
        forceFormData: true,
    });
};

const submitPassword = () => {
    passwordForm.put('/settings/account/password');
};
</script>

<template>
    <AppLayout>
        <Head title="Account Settings" />
        
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-xl font-semibold">Settings</h1>
                <p class="mt-0.5 text-sm text-slate-400">Manage your account and preferences.</p>
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
                    <h2 class="mb-4 text-sm font-semibold">Profile Info</h2>
                    <form @submit.prevent="submitProfile" enctype="multipart/form-data">
                        <div class="mb-4 flex items-start gap-6">
                            <div class="flex-none">
                                <div v-if="user.avatar_url" class="h-16 w-16 rounded-full border-2 border-white/10 object-cover bg-slate-800">
                                    <img :src="user.avatar_url" alt="Avatar" class="h-full w-full rounded-full object-cover">
                                </div>
                                <div v-else class="flex h-16 w-16 items-center justify-center rounded-full bg-orange-500 text-xl font-bold text-white">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <label class="mb-1 block text-xs font-medium text-slate-400">Avatar</label>
                                <input type="file" @input="profileForm.avatar = $event.target.files[0]" accept=".jpg,.jpeg,.png" class="block text-xs text-slate-400">
                                <p class="mt-1 text-xs text-slate-400">JPG/PNG · max 2 MB</p>
                            </div>
                        </div>
                        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-slate-400">Name <span class="text-red-400">*</span></label>
                                <input v-model="profileForm.name" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-400">Email</label>
                                <input :value="user.email" type="email" disabled class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 opacity-60 cursor-not-allowed outline-none">
                                <p class="mt-1 text-xs text-slate-400">Email cannot be changed</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-400">Phone</label>
                                <input v-model="profileForm.phone" type="text" placeholder="+91XXXXXXXXXX" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="profileForm.processing">Save</button>
                        </div>
                    </form>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-sm font-semibold">Change Password</h2>
                    <form @submit.prevent="submitPassword" class="max-w-md space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">Current Password</label>
                            <input v-model="passwordForm.current_password" type="password" autocomplete="current-password" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">New Password</label>
                            <input v-model="passwordForm.password" type="password" autocomplete="new-password" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-400">Confirm Password</label>
                            <input v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="passwordForm.processing">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

