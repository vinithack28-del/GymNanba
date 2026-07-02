<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    email_notifications: props.settings?.email_notifications ?? true,
    sms_notifications: props.settings?.sms_notifications ?? false,
    whatsapp_notifications: props.settings?.whatsapp_notifications ?? false,
    payment_reminders: props.settings?.payment_reminders ?? true,
    expiry_reminders: props.settings?.expiry_reminders ?? true,
    birthday_reminders: props.settings?.birthday_reminders ?? false,
});

const submit = () => {
    form.put('/settings/integrations/whatsapp');
};
</script>

<template>
    <AppLayout>
        <Head title="Notification Settings" />
        
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-xl font-semibold">Settings</h1>
                <p class="mt-0.5 text-sm text-slate-400">Configure notification preferences.</p>
            </div>

            <div class="flex gap-2">
                <Link href="/settings/account" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Account</Link>
                <Link href="/settings/profile" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Profile</Link>
                <Link href="/settings/integrations" class="rounded-lg bg-orange-500 px-3 py-1.5 text-sm font-medium text-slate-950">Integrations</Link>
                <Link href="/settings/language" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Language</Link>
                <Link href="/settings/data" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Data</Link>
            </div>

            <form @submit.prevent="submit" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="mb-4 text-sm font-semibold">Notification Channels</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">Email Notifications</p>
                            <p class="text-xs text-slate-400">Receive notifications via email</p>
                        </div>
                        <input v-model="form.email_notifications" type="checkbox" class="h-5 w-5 rounded border-white/10 bg-slate-950/50 text-orange-500 focus:ring-orange-400">
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">SMS Notifications</p>
                            <p class="text-xs text-slate-400">Receive SMS alerts</p>
                        </div>
                        <input v-model="form.sms_notifications" type="checkbox" class="h-5 w-5 rounded border-white/10 bg-slate-950/50 text-orange-500 focus:ring-orange-400">
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">WhatsApp Notifications</p>
                            <p class="text-xs text-slate-400">Receive messages via WhatsApp</p>
                        </div>
                        <input v-model="form.whatsapp_notifications" type="checkbox" class="h-5 w-5 rounded border-white/10 bg-slate-950/50 text-orange-500 focus:ring-orange-400">
                    </div>
                </div>

                <h2 class="mb-4 mt-6 text-sm font-semibold">Notification Types</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">Payment Reminders</p>
                            <p class="text-xs text-slate-400">Remind members about due payments</p>
                        </div>
                        <input v-model="form.payment_reminders" type="checkbox" class="h-5 w-5 rounded border-white/10 bg-slate-950/50 text-orange-500 focus:ring-orange-400">
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">Membership Expiry Reminders</p>
                            <p class="text-xs text-slate-400">Alert before membership expires</p>
                        </div>
                        <input v-model="form.expiry_reminders" type="checkbox" class="h-5 w-5 rounded border-white/10 bg-slate-950/50 text-orange-500 focus:ring-orange-400">
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">Birthday Wishes</p>
                            <p class="text-xs text-slate-400">Send birthday greetings to members</p>
                        </div>
                        <input v-model="form.birthday_reminders" type="checkbox" class="h-5 w-5 rounded border-white/10 bg-slate-950/50 text-orange-500 focus:ring-orange-400">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">Save Preferences</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
