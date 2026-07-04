<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tenant: Object,
});

const form = useForm({});

const submit = () => {
    form.delete(`/admin/tenants/${props.tenant.id}`);
};
</script>

<template>
    <AppLayout>
        <Head title="Delete Tenant" />
        
        <div class="flex flex-col gap-6">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Tenant</p>
                <h1 class="mt-2 text-3xl font-semibold">Delete {{ tenant.gym_name }}</h1>
                <p class="mt-1 text-slate-300">This action permanently removes the tenant record, subscriptions, and payment entries currently stored in this app.</p>
            </div>

            <div class="max-w-3xl rounded-[2rem] border border-white/10 bg-white/5 p-6">
                <div class="rounded-2xl border border-red-400/20 bg-red-500/10 p-5">
                    <p class="text-lg font-semibold text-red-300">Confirm deletion</p>
                    <p class="mt-2 text-sm text-red-200">You are about to delete <strong>{{ tenant.gym_name }}</strong> owned by {{ tenant.owner_name }}.</p>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Owner email</p>
                        <p class="mt-2 font-semibold">{{ tenant.owner_email }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Subdomain</p>
                        <p class="mt-2 font-semibold">{{ tenant.subdomain }}.gymos.in</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="mt-6 flex gap-3">
                    <button type="submit" class="rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400">Delete tenant</button>
                    <Link :href="`/admin/tenants/${tenant.id}`" class="rounded-2xl border border-white/10 bg-slate-950/50 px-5 py-3 text-sm font-semibold hover:bg-white/10">Cancel</Link>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
