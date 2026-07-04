<script setup>
import { computed } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    member: Object,
    payments: {
        type: Array,
        default: () => [],
    },
});

const paymentRows = computed(() => props.payments || []);

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const formatAmount = (paise) => `â‚¹${Number((paise || 0) / 100).toFixed(0)}`;

const statusClass = computed(() => {
    const styles = {
        active: 'bg-emerald-100 text-emerald-800',
        inactive: 'bg-slate-100 text-slate-600',
        expired: 'bg-red-100 text-red-700',
        frozen: 'bg-sky-100 text-sky-700',
    };

    return styles[props.member?.effective_status] || styles.inactive;
});

const membershipPeriod = computed(() => {
    if (!props.member?.start_date) {
        return 'â€”';
    }

    return `${formatDate(props.member.start_date)} â†’ ${props.member.expiry_date ? formatDate(props.member.expiry_date) : 'â€”'}`;
});

const expiryMeta = computed(() => {
    if (!props.member?.expiry_date) {
        return { label: '', className: 'text-slate-500' };
    }

    const exp = new Date(props.member.expiry_date);
    const today = new Date();
    const daysLeft = Math.ceil((exp - today) / 86400000);

    if (daysLeft < 0) {
        return { label: `(expired ${Math.abs(daysLeft)}d ago)`, className: 'text-red-700' };
    }

    if (daysLeft === 0) {
        return { label: '(expires today)', className: 'text-amber-700' };
    }

    if (daysLeft <= 7) {
        return { label: `(${daysLeft}d left)`, className: 'text-amber-700' };
    }

    return { label: `(${daysLeft}d left)`, className: 'text-emerald-700' };
});

const paymentTimeline = computed(() => {
    const sortedAsc = [...paymentRows.value].sort((a, b) => {
        const dateDiff = new Date(a.payment_date) - new Date(b.payment_date);
        if (dateDiff !== 0) return dateDiff;
        return a.id - b.id;
    });

    const chained = new Map();
    let prevEnd = null;

    sortedAsc.forEach((payment) => {
        const payDate = payment.payment_date;

        if (payment.plan) {
            const start = prevEnd && prevEnd > payDate ? prevEnd : payDate;
            const end = computePlanEnd(start, payment.plan);
            chained.set(payment.id, { start, end });
            prevEnd = end;
        } else {
            chained.set(payment.id, { start: payDate, end: null });
        }
    });

    return paymentRows.value.map((payment) => ({
        ...payment,
        timeline: chained.get(payment.id) || { start: payment.payment_date, end: null },
    }));
});

function computePlanEnd(startDate, plan) {
    const date = new Date(startDate);
    const durationValue = Number(plan.duration_value || plan.duration_days || 0);

    if (plan.duration_type === 'months') {
        date.setMonth(date.getMonth() + durationValue);
    } else {
        date.setDate(date.getDate() + durationValue);
    }

    return date.toISOString().slice(0, 10);
}

function paymentBadge(payment) {
    if (payment.status === 'voided') {
        return 'bg-red-100 text-red-700';
    }

    if (payment.is_partial && payment.due_paise > 0) {
        return 'bg-yellow-100 text-yellow-800';
    }

    return 'bg-emerald-100 text-emerald-700';
}

function paymentBadgeText(payment) {
    if (payment.status === 'voided') return 'Voided';
    if (payment.is_partial && payment.due_paise > 0) return 'Partial';
    return 'Paid';
}

function paymentMethodLabel(payment) {
    const method = payment.method === 'split' ? 'Split' : (payment.method || 'â€”');
    const refs = (payment.splits || [])
        .map((split) => split.reference)
        .filter(Boolean)
        .join(', ');

    return {
        method: method.charAt(0).toUpperCase() + method.slice(1),
        reference: refs,
    };
}
</script>

<template>
    <AppLayout>
        <Head :title="member.name" />

        <div class="flex flex-col gap-5 text-slate-900">
            <Link href="/members" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-orange-600">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                    <path d="M19 12H5" />
                    <path d="M12 19l-7-7 7-7" />
                </svg>
                Back to Members
            </Link>

            <div class="grid items-start gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
                <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col items-center gap-3 border-b border-slate-200 px-6 py-6">
                        <div class="flex h-[72px] w-[72px] items-center justify-center rounded-full bg-orange-100 text-2xl font-bold text-orange-700">
                            {{ member.initials }}
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-slate-900">{{ member.name }}</p>
                            <p class="font-mono text-xs font-semibold text-slate-500">{{ member.member_code }}</p>
                        </div>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase" :class="statusClass">
                            {{ member.status_label }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-4 px-6 py-5">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 text-slate-500">ðŸ“ž</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Phone</p>
                                <p class="mt-1 text-sm text-slate-900">{{ member.phone }}</p>
                            </div>
                        </div>

                        <div v-if="member.email" class="flex items-start gap-3">
                            <span class="mt-0.5 text-slate-500">âœ‰ï¸</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Email</p>
                                <p class="mt-1 text-sm text-slate-900">{{ member.email }}</p>
                            </div>
                        </div>

                        <div v-if="member.gender || member.dob" class="flex items-start gap-3">
                            <span class="mt-0.5 text-slate-500">ðŸ‘¤</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Personal</p>
                                <p class="mt-1 text-sm text-slate-900">
                                    <span v-if="member.gender">{{ member.gender.charAt(0).toUpperCase() + member.gender.slice(1) }}</span>
                                    <span v-if="member.gender && member.dob"> Â· </span>
                                    <span v-if="member.dob">{{ formatDate(member.dob) }}</span>
                                </p>
                            </div>
                        </div>

                        <div v-if="member.branch" class="flex items-start gap-3">
                            <span class="mt-0.5 text-slate-500">ðŸ¢</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Branch</p>
                                <p class="mt-1 text-sm text-slate-900">{{ member.branch.name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 text-slate-500">ðŸ“‹</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Current Plan</p>
                                <p class="mt-1">
                                    <span v-if="member.plan_name" class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-1 text-sm font-semibold text-slate-800">
                                        {{ member.plan_name }}
                                    </span>
                                    <span v-else class="text-sm text-slate-500">â€”</span>
                                </p>
                            </div>
                        </div>

                        <div v-if="member.start_date || member.expiry_date" class="flex items-start gap-3">
                            <span class="mt-0.5 text-slate-500">ðŸ“…</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Membership Period</p>
                                <p class="mt-1 text-sm text-slate-900">{{ membershipPeriod }}</p>
                                <p v-if="member.expiry_date" class="mt-1 text-xs" :class="expiryMeta.className">{{ expiryMeta.label }}</p>
                            </div>
                        </div>

                        <div v-if="member.address" class="flex items-start gap-3">
                            <span class="mt-0.5 text-slate-500">ðŸ“</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Address</p>
                                <p class="mt-1 text-sm text-slate-900">{{ member.address }}</p>
                            </div>
                        </div>

                        <div v-if="member.notes" class="flex items-start gap-3">
                            <span class="mt-0.5 text-slate-500">ðŸ“</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Notes</p>
                                <p class="mt-1 text-sm text-slate-900">{{ member.notes }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 text-slate-500">ðŸ•’</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Member Since</p>
                                <p class="mt-1 text-sm text-slate-900">{{ formatDate(member.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 border-t border-slate-200 px-6 py-4">
                        <Link :href="`/payments/collect?member_id=${member.id}`" class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
                            Collect Payment
                        </Link>
                        <Link :href="`/members/${member.id}/edit`" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                            Edit Member
                        </Link>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <h3 class="text-[15px] font-semibold text-slate-900">Payment History</h3>
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500">
                            {{ paymentRows.length }} {{ paymentRows.length === 1 ? 'record' : 'records' }}
                        </span>
                    </div>

                    <div v-if="paymentRows.length === 0" class="px-6 py-14 text-center text-sm text-slate-500">
                        No payment records found.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[860px] text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Receipt</th>
                                    <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Plan</th>
                                    <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Duration</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Amount</th>
                                    <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Method</th>
                                    <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Status</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="payment in paymentTimeline" :key="payment.id" class="border-t border-slate-100">
                                    <td class="px-6 py-3">
                                        <Link :href="`/payments/${payment.id}/receipt`" class="font-mono text-xs font-semibold text-orange-600 hover:underline">
                                            {{ payment.receipt_number }}
                                        </Link>
                                        <div class="mt-0.5 text-xs text-slate-500">{{ formatDate(payment.payment_date) }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600">
                                        {{ payment.plan?.name || 'â€”' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <template v-if="payment.timeline.end">
                                            <div class="text-xs text-slate-700">
                                                {{ formatDate(payment.timeline.start) }} â†’ {{ formatDate(payment.timeline.end) }}
                                            </div>
                                            <div class="mt-0.5 text-xs text-slate-500">
                                                {{ payment.plan?.duration_label || 'Membership plan' }}
                                            </div>
                                        </template>
                                        <span v-else class="text-xs text-slate-500">{{ formatDate(payment.timeline.start) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="text-sm font-semibold text-slate-900">{{ formatAmount(payment.paid_paise) }}</div>
                                        <div v-if="payment.is_partial && payment.due_paise > 0" class="text-xs text-orange-600">
                                            {{ formatAmount(payment.due_paise) }} due
                                        </div>
                                        <div v-else-if="payment.gst_paise > 0" class="text-xs text-slate-500">
                                            +{{ formatAmount(payment.gst_paise) }} GST
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600">
                                        <div>{{ paymentMethodLabel(payment).method }}</div>
                                        <div v-if="paymentMethodLabel(payment).reference" class="font-mono text-[11px] text-slate-500">
                                            {{ paymentMethodLabel(payment).reference }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="paymentBadge(payment)">
                                            {{ paymentBadgeText(payment) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <Link :href="`/payments/${payment.id}/receipt`" class="inline-flex rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                                            Receipt
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

