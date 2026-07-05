<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    registrations: {
        type: Object,
        default: () => ({}),
    },
    status: {
        type: String,
        default: 'pending',
    },
    counts: {
        type: Object,
        default: () => ({}),
    },
    plans: {
        type: Array,
        default: () => [],
    },
    registrationUrl: {
        type: String,
        default: '',
    },
});

const page = usePage();
const rows = computed(() => props.registrations?.data || []);
const paginationLinks = computed(() => (props.registrations?.links || []).filter((link) => link.url || link.active));
const today = new Date().toISOString().slice(0, 10);
const activeRegistration = ref(null);
const confirmModalOpen = ref(false);
const rejectModalOpen = ref(false);
const linkModalOpen = ref(false);
const copyLabel = ref('Copy');
const emailFlashVisible = ref(false);
let emailFlashTimer = null;

const confirmForm = useForm({
    plan_id: '',
    start_date: today,
});

const rejectForm = useForm({
    reason: '',
});

const emailForm = useForm({
    email: '',
});

onUnmounted(() => {
    window.clearTimeout(emailFlashTimer);
});

const tabs = computed(() => ([
    { key: 'pending', label: 'Pending', count: props.counts?.pending || 0 },
    { key: 'confirmed', label: 'Confirmed', count: props.counts?.confirmed || 0 },
    { key: 'rejected', label: 'Rejected', count: props.counts?.rejected || 0 },
]));

const formatDate = (date, options = {}) => {
    if (!date) return '-';

    const value = new Date(date);
    if (options.withTime) {
        return value.toLocaleString('en-GB', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).replace(',', '').replaceAll('/', '-');
    }

    return value.toLocaleDateString('en-GB').replaceAll('/', '-');
};

const formatPrice = (paise) => `Rs. ${((Number(paise) || 0) / 100).toFixed(2)}`;

const planLabel = (plan) => {
    const total = plan.total_price_paise ?? plan.price_paise;
    const gstText = Number(plan.gst_amount_paise || 0) > 0 ? ' incl. GST' : '';
    return `${plan.name} - ${formatPrice(total)} / ${plan.duration_label || 'plan'}${gstText}`;
};

const confirmedByName = (registration) => {
    if (registration.confirmed_by?.name) {
        return registration.confirmed_by.name;
    }

    if (registration.confirmed_by_name) {
        return registration.confirmed_by_name;
    }

    return '-';
};

const openConfirmModal = (registration) => {
    activeRegistration.value = registration;
    confirmForm.reset();
    confirmForm.clearErrors();
    confirmForm.start_date = today;
    confirmModalOpen.value = true;
};

const closeConfirmModal = () => {
    confirmModalOpen.value = false;
    activeRegistration.value = null;
    confirmForm.reset();
    confirmForm.clearErrors();
};

const submitConfirm = () => {
    if (!activeRegistration.value) return;

    confirmForm.post(`/members/registrations/${activeRegistration.value.id}/confirm`, {
        preserveScroll: true,
        onSuccess: closeConfirmModal,
    });
};

const openRejectModal = (registration) => {
    activeRegistration.value = registration;
    rejectForm.reset();
    rejectForm.clearErrors();
    rejectModalOpen.value = true;
};

const closeRejectModal = () => {
    rejectModalOpen.value = false;
    activeRegistration.value = null;
    rejectForm.reset();
    rejectForm.clearErrors();
};

const submitReject = () => {
    if (!activeRegistration.value) return;

    rejectForm.post(`/members/registrations/${activeRegistration.value.id}/reject`, {
        preserveScroll: true,
        onSuccess: closeRejectModal,
    });
};

const copyRegistrationUrl = async () => {
    if (!props.registrationUrl) return;

    try {
        await navigator.clipboard.writeText(props.registrationUrl);
    } catch (error) {
        const input = document.getElementById('registration-url');
        input?.select();
        document.execCommand('copy');
    }

    copyLabel.value = 'Copied';
    window.setTimeout(() => {
        copyLabel.value = 'Copy';
    }, 1600);
};

const sendRegistrationEmail = () => {
    emailForm.post('/members/registration-link/email', {
        preserveScroll: true,
        onSuccess: () => {
            emailForm.reset();
            linkModalOpen.value = true;
        },
    });
};

watch(
    () => page.props.flash?.email_sent,
    (message) => {
        window.clearTimeout(emailFlashTimer);
        emailFlashVisible.value = Boolean(message);
        if (message) {
            linkModalOpen.value = true;
            emailFlashTimer = window.setTimeout(() => {
                emailFlashVisible.value = false;
            }, 5000);
        }
    },
    { immediate: true },
);
</script>

<template>
    <AppLayout>
        <Head title="Member Registrations" />

        <div class="flex flex-col gap-4 text-slate-900">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-[26px] font-semibold leading-tight text-slate-900">Online Registrations</h1>
                    <p class="mt-1 text-sm text-slate-600">Review and confirm members who registered via the online form.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link href="/members" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300">
                        Members
                    </Link>
                    <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-600 transition hover:border-orange-300" @click="linkModalOpen = true">
                        Registration Link
                    </button>
                </div>
            </div>

            <div v-if="page.props.errors?.phone" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600">
                {{ page.props.errors.phone }}
            </div>

            <div class="inline-flex w-fit flex-wrap gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
                <Link
                    v-for="tab in tabs"
                    :key="tab.key"
                    :href="`/members/registrations?status=${tab.key}`"
                    :class="[
                        'inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-semibold transition',
                        status === tab.key ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:text-slate-900',
                    ]"
                >
                    {{ tab.label }}
                    <span :class="['rounded-full px-1.5 py-0.5 text-[11px]', status === tab.key ? 'bg-orange-100 text-orange-600' : 'bg-slate-100 text-slate-500']">
                        {{ tab.count }}
                    </span>
                </Link>
            </div>

            <div v-if="rows.length === 0" class="flex flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-white py-16 text-center shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
                <p class="text-sm font-semibold">No {{ status }} registrations</p>
                <p class="text-sm text-slate-500">Share the registration link to get people to sign up.</p>
                <button type="button" class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-500" @click="linkModalOpen = true">
                    Registration Link
                </button>
            </div>

            <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[940px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Gender / DOB</th>
                                <th class="px-4 py-3">Submitted</th>
                                <th v-if="status === 'pending'" class="px-4 py-3 text-right">Actions</th>
                                <th v-else-if="status === 'confirmed'" class="px-4 py-3">Confirmed By</th>
                                <th v-else class="px-4 py-3">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="reg in rows" :key="reg.id" class="transition hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-orange-100 text-xs font-bold text-orange-700">
                                            {{ reg.name.charAt(0).toUpperCase() }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ reg.name }}</p>
                                            <p v-if="reg.notes" class="max-w-[240px] truncate text-xs text-slate-500">{{ reg.notes }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ reg.phone }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ reg.email || '-' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500">
                                    {{ reg.gender ? reg.gender.charAt(0).toUpperCase() + reg.gender.slice(1) : '-' }}
                                    <span v-if="reg.dob"> - {{ formatDate(reg.dob) }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ formatDate(reg.created_at, { withTime: true }) }}</td>
                                <td v-if="status === 'pending'" class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition hover:border-emerald-300" @click="openConfirmModal(reg)">
                                            Confirm
                                        </button>
                                        <button type="button" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-500 transition hover:border-red-300" @click="openRejectModal(reg)">
                                            Reject
                                        </button>
                                    </div>
                                </td>
                                <td v-else-if="status === 'confirmed'" class="px-4 py-3 text-xs text-slate-500">
                                    <p class="font-semibold text-slate-700">{{ confirmedByName(reg) }}</p>
                                    <p>{{ formatDate(reg.confirmed_at) }}</p>
                                </td>
                                <td v-else class="max-w-[260px] px-4 py-3 text-xs text-slate-500">
                                    {{ reg.rejected_reason || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="paginationLinks.length > 1" class="flex flex-wrap items-center gap-1">
                <Link
                    v-for="link in paginationLinks"
                    :key="`${link.label}-${link.url}`"
                    :href="link.url || '#'"
                    preserve-scroll
                    :class="[
                        'rounded-lg border px-3 py-1.5 text-xs font-semibold',
                        link.active ? 'border-orange-500 bg-orange-500 text-white' : 'border-slate-200 bg-white text-slate-600',
                        !link.url && !link.active ? 'pointer-events-none opacity-50' : '',
                    ]"
                    v-html="link.label"
                />
            </div>

            <div v-if="confirmModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4" @click.self="closeConfirmModal">
                <form class="w-full max-w-[460px] rounded-2xl border border-slate-200 bg-white p-5 shadow-xl" @submit.prevent="submitConfirm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Confirm Registration</h2>
                            <p class="mt-1 text-sm text-slate-500">Confirming: {{ activeRegistration?.name }}</p>
                        </div>
                        <button type="button" class="text-slate-400 transition hover:text-slate-700" @click="closeConfirmModal">x</button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Membership Plan <span class="text-red-500">*</span></label>
                            <select v-model="confirmForm.plan_id" :class="['w-full rounded-xl border bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-orange-400', confirmForm.errors.plan_id ? 'field-invalid' : 'border-slate-200']" required>
                                <option value="">Select a plan...</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                    {{ planLabel(plan) }}
                                </option>
                            </select>
                            <p v-if="confirmForm.errors.plan_id" class="field-error field-error-light">{{ confirmForm.errors.plan_id }}</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Start Date <span class="text-red-500">*</span></label>
                            <input v-model="confirmForm.start_date" type="date" :class="['w-full rounded-xl border bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-orange-400', confirmForm.errors.start_date ? 'field-invalid' : 'border-slate-200']" required>
                            <p v-if="confirmForm.errors.start_date" class="field-error field-error-light">{{ confirmForm.errors.start_date }}</p>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300" @click="closeConfirmModal">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-500 disabled:opacity-60" :disabled="confirmForm.processing">
                            Confirm & Add
                        </button>
                    </div>
                </form>
            </div>

            <div v-if="rejectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4" @click.self="closeRejectModal">
                <form class="w-full max-w-[460px] rounded-2xl border border-slate-200 bg-white p-5 shadow-xl" @submit.prevent="submitReject">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Reject Registration</h2>
                            <p class="mt-1 text-sm text-slate-500">Rejecting: {{ activeRegistration?.name }}</p>
                        </div>
                        <button type="button" class="text-slate-400 transition hover:text-slate-700" @click="closeRejectModal">x</button>
                    </div>

                    <div class="mt-4">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Reason <span class="font-normal text-slate-400">(optional)</span></label>
                        <textarea v-model="rejectForm.reason" rows="3" maxlength="500" placeholder="e.g. Duplicate registration, incomplete information..." :class="['w-full rounded-xl border bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-orange-400', rejectForm.errors.reason ? 'field-invalid' : 'border-slate-200']"></textarea>
                        <p v-if="rejectForm.errors.reason" class="field-error field-error-light">{{ rejectForm.errors.reason }}</p>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300" @click="closeRejectModal">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-400 disabled:opacity-60" :disabled="rejectForm.processing">
                            Reject Registration
                        </button>
                    </div>
                </form>
            </div>

            <div v-if="linkModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4" @click.self="linkModalOpen = false">
                <div class="w-full max-w-[500px] rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Registration Link</h2>
                            <p class="mt-1 text-sm text-slate-500">Share this link so people can register online.</p>
                        </div>
                        <button type="button" class="text-slate-400 transition hover:text-slate-700" @click="linkModalOpen = false">x</button>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <input id="registration-url" :value="registrationUrl" readonly class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-xs text-slate-600 outline-none">
                        <button type="button" class="rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-600 transition hover:border-orange-300" @click="copyRegistrationUrl">
                            {{ copyLabel }}
                        </button>
                    </div>

                    <div class="my-4 border-t border-slate-200"></div>
                    <p class="mb-2 text-xs font-semibold text-slate-600">Share via Email</p>
                    <p v-if="emailFlashVisible && page.props.flash?.email_sent" class="mb-2 text-xs text-emerald-600">{{ page.props.flash.email_sent }}</p>

                    <form class="flex flex-col gap-2 sm:flex-row" @submit.prevent="sendRegistrationEmail">
                        <div class="min-w-0 flex-1">
                            <input v-model="emailForm.email" type="email" placeholder="recipient@example.com" :class="['w-full rounded-xl border bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-orange-400', emailForm.errors.email ? 'field-invalid' : 'border-slate-200']" required>
                            <p v-if="emailForm.errors.email" class="field-error field-error-light">{{ emailForm.errors.email }}</p>
                        </div>
                        <button type="submit" class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-500 disabled:opacity-60" :disabled="emailForm.processing">
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

