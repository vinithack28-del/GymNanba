<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    integrations: Object,
});

const integrations = ref({
    whatsapp: {
        name: 'WhatsApp Business API',
        desc: 'Send automated messages and notifications via WhatsApp',
        icon: 'ðŸ’¬',
        connected: props.integrations?.whatsapp?.isConnected || false,
        config: props.integrations?.whatsapp?.config || {},
    },
    razorpay: {
        name: 'Razorpay',
        desc: 'Accept online payments securely',
        icon: 'â‚¹',
        connected: props.integrations?.razorpay?.isConnected || false,
        config: props.integrations?.razorpay?.config || {},
    },
    biometric: {
        name: 'Biometric Device',
        desc: 'Integrate with biometric attendance systems',
        icon: 'ðŸ”',
        connected: props.integrations?.biometric?.isConnected || false,
        config: props.integrations?.biometric?.config || {},
    },
    tally: {
        name: 'Tally',
        desc: 'Sync financial data with Tally accounting software',
        icon: 'ðŸ“Š',
        connected: props.integrations?.tally?.isConnected || false,
        config: props.integrations?.tally?.config || {},
    },
});

const openSections = ref({});

const toggleSection = (key) => {
    openSections.value[key] = !openSections.value[key];
};

const forms = ref({});

Object.keys(integrations.value).forEach(key => {
    forms.value[key] = useForm({
        ...integrations.value[key].config,
    });
});

const submitIntegration = (key) => {
    if (!forms.value[key]) return;
    forms.value[key].put(`/settings/integrations/${key}`);
};
</script>

<template>
    <AppLayout>
        <Head title="Integrations" />
        
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-xl font-semibold">Settings</h1>
                <p class="mt-0.5 text-sm text-slate-400">Manage third-party integrations and services.</p>
            </div>

            <div class="flex gap-2">
                <Link href="/settings/account" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Account</Link>
                <Link href="/settings/profile" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Profile</Link>
                <Link href="/settings/integrations" class="rounded-lg bg-orange-500 px-3 py-1.5 text-sm font-medium text-slate-950">Integrations</Link>
                <Link href="/settings/language" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Language</Link>
                <Link href="/settings/data" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">Data</Link>
            </div>

            <div class="flex flex-col gap-4">
                <div v-for="(int, key) in integrations" :key="key" class="rounded-2xl border border-white/10 bg-white/5">
                    <div class="flex cursor-pointer items-center justify-between p-5" @click="toggleSection(key)">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl leading-none">{{ int.icon }}</span>
                            <div>
                                <p class="text-sm font-semibold">{{ int.name }}</p>
                                <p class="text-xs text-slate-400">{{ int.desc }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="int.connected ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'">
                                {{ int.connected ? 'Connected' : 'Not Connected' }}
                            </span>
                            <svg class="h-4 w-4 text-slate-400 transition-transform" :class="openSections[key] && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div v-show="openSections[key]" class="border-t border-white/10 px-5 pb-5 pt-4">
                        <form @submit.prevent="submitIntegration(key)">
                            <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                                <div v-if="key === 'whatsapp'">
                                    <label class="mb-1 block text-xs font-medium text-slate-400">Phone Number ID</label>
                                    <input v-model="forms[key].phone_number_id" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                </div>
                                <div v-if="key === 'whatsapp'">
                                    <label class="mb-1 block text-xs font-medium text-slate-400">API Token</label>
                                    <input v-model="forms[key].api_token" type="password" :placeholder="int.connected ? 'â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢' : ''" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                </div>
                                <div v-if="key === 'razorpay'">
                                    <label class="mb-1 block text-xs font-medium text-slate-400">Key ID</label>
                                    <input v-model="forms[key].key_id" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                </div>
                                <div v-if="key === 'razorpay'">
                                    <label class="mb-1 block text-xs font-medium text-slate-400">Key Secret</label>
                                    <input v-model="forms[key].key_secret" type="password" :placeholder="int.connected ? 'â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢' : ''" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                </div>
                                <div v-if="key === 'biometric'">
                                    <label class="mb-1 block text-xs font-medium text-slate-400">Device IP</label>
                                    <input v-model="forms[key].device_ip" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                </div>
                                <div v-if="key === 'biometric'">
                                    <label class="mb-1 block text-xs font-medium text-slate-400">Port</label>
                                    <input v-model="forms[key].port" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                </div>
                                <div v-if="key === 'tally'">
                                    <label class="mb-1 block text-xs font-medium text-slate-400">Company Name</label>
                                    <input v-model="forms[key].company_name" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                </div>
                                <div v-if="key === 'tally'">
                                    <label class="mb-1 block text-xs font-medium text-slate-400">License Key</label>
                                    <input v-model="forms[key].license_key" type="password" :placeholder="int.connected ? 'â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢' : ''" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end gap-2">
                                <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

