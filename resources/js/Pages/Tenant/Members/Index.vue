<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    members: Object,
    stats: Object,
    selectedBranch: Object,
    registrationUrl: String,
    pendingRegistrationCount: Number,
});

const memberRows = computed(() => props.members?.data || []);
const openMenuId = ref(null);
const activeMenuMember = ref(null);
const menuPosition = ref({ top: 0, right: 0 });
const freezeTarget = ref(null);
const page = usePage();

const queryParams = computed(() => new URL(page.url || '/members', 'http://localhost').searchParams);
const currentSearch = computed(() => queryParams.value.get('search') || '');
const currentStatus = computed(() => queryParams.value.get('status') || '');
const currentGender = computed(() => queryParams.value.get('gender') || '');
const currentSortBy = computed(() => queryParams.value.get('sort_by') || 'created_at');
const currentSortDir = computed(() => queryParams.value.get('sort_dir') || 'desc');
const currentPerPage = computed(() => Number(queryParams.value.get('per_page') || props.members?.per_page || 25));
const filterSearch = ref('');
const filterStatus = ref('');
const filterGender = ref('');

const statCards = computed(() => ([
    { label: 'Total Members', value: props.stats?.total ?? 0, filter: '', color: 'text-slate-900' },
    { label: 'Active', value: props.stats?.active ?? 0, filter: 'active', color: 'text-emerald-500' },
    { label: 'Inactive', value: props.stats?.inactive ?? 0, filter: 'inactive', color: 'text-slate-500' },
    { label: 'Expired', value: props.stats?.expired ?? 0, filter: 'expired', color: 'text-red-500' },
]));

const columns = [
    { key: 'member_code', label: 'ID' },
    { key: 'name', label: 'Member' },
    { key: null, label: 'Phone' },
    { key: 'plan_name', label: 'Plan' },
    { key: 'created_at', label: 'Joined' },
    { key: 'expiry_date', label: 'Expires' },
    { key: 'status', label: 'Status' },
    { key: 'balance_paise', label: 'Balance' },
    { key: null, label: '' },
];

const buildUrl = (updates = {}) => {
    const params = new URLSearchParams(queryParams.value);

    Object.entries(updates).forEach(([key, value]) => {
        if (value === '' || value === null || value === undefined) {
            params.delete(key);
        } else {
            params.set(key, String(value));
        }
    });

    if (!('page' in updates)) {
        params.delete('page');
    }

    const qs = params.toString();
    return qs ? `/members?${qs}` : '/members';
};

const visitMembers = (updates = {}) => {
    closeMenu();
    router.get(buildUrl(updates), {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const applyFilters = () => {
    visitMembers({
        search: filterSearch.value.trim() || null,
        status: filterStatus.value || null,
        gender: filterGender.value || null,
        page: 1,
    });
};

const clearFilters = () => {
    filterSearch.value = '';
    filterStatus.value = '';
    filterGender.value = '';
    visitMembers({
        search: null,
        status: null,
        gender: null,
        page: 1,
    });
};

const changePerPage = (perPage) => {
    visitMembers({
        per_page: perPage,
        page: 1,
    });
};

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const getStatusConfig = (status) => {
    const colors = {
        active: {
            text: 'text-emerald-600',
            soft: 'bg-emerald-50 text-emerald-600 border-emerald-100',
        },
        inactive: {
            text: 'text-slate-500',
            soft: 'bg-slate-100 text-slate-600 border-slate-200',
        },
        expired: {
            text: 'text-red-500',
            soft: 'bg-red-50 text-red-500 border-red-100',
        },
        frozen: {
            text: 'text-sky-500',
            soft: 'bg-sky-50 text-sky-500 border-sky-100',
        },
    };

    return colors[status] || colors.inactive;
};

const copyRegistrationLink = async () => {
    if (!props.registrationUrl || typeof navigator === 'undefined' || !navigator.clipboard) {
        return;
    }

    await navigator.clipboard.writeText(props.registrationUrl);
};

const toggleStatus = (member) => {
    closeMenu();
    useForm({}).patch(`/members/${member.id}/toggle-status`, {
        preserveScroll: true,
    });
};

const freezeForm = useForm({
    freeze_days: 1,
});

const deleteForm = useForm({});

const paymentCollectUrl = (memberId) => `/payments/collect?member_id=${memberId}`;

const closeMenu = () => {
    openMenuId.value = null;
    activeMenuMember.value = null;
};

const openActionMenu = (event, member) => {
    const rect = event.currentTarget.getBoundingClientRect();
    openMenuId.value = openMenuId.value === member.id ? null : member.id;

    if (openMenuId.value === null) {
        activeMenuMember.value = null;
        return;
    }

    activeMenuMember.value = member;
    menuPosition.value = {
        top: rect.bottom + 4,
        right: window.innerWidth - rect.right,
    };
};

const openFreezeModal = (member) => {
    freezeTarget.value = member;
    freezeForm.reset();
    freezeForm.freeze_days = member.plan?.max_freeze_days || 1;
    closeMenu();
};

const closeFreezeModal = () => {
    freezeTarget.value = null;
    freezeForm.reset();
};

const submitFreeze = () => {
    if (!freezeTarget.value) {
        return;
    }

    freezeForm.patch(`/members/${freezeTarget.value.id}/freeze`, {
        preserveScroll: true,
        onSuccess: closeFreezeModal,
    });
};

const unfreezeMember = (member) => {
    closeMenu();
    useForm({}).patch(`/members/${member.id}/unfreeze`, {
        preserveScroll: true,
    });
};

const deleteMember = (member) => {
    if (!window.confirm(`Delete ${member.name}?`)) {
        return;
    }

    closeMenu();
    deleteForm.delete(`/members/${member.id}`, {
        preserveScroll: true,
    });
};

const handleWindowClick = () => {
    closeMenu();
};

onMounted(() => {
    window.addEventListener('click', handleWindowClick);
});

onBeforeUnmount(() => {
    window.removeEventListener('click', handleWindowClick);
});

watch(
    () => page.url,
    () => {
        filterSearch.value = currentSearch.value;
        filterStatus.value = currentStatus.value;
        filterGender.value = currentGender.value;
    },
    { immediate: true },
);
</script>

<template>
    <AppLayout>
        <Head title="Members" />

        <div class="flex flex-col gap-4 text-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h1 class="text-[28px] font-semibold leading-none text-slate-900">Members</h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        v-if="registrationUrl"
                        href="/members/registrations"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        <span>Registrations</span>
                        <span
                            v-if="pendingRegistrationCount > 0"
                            class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-orange-500 px-1.5 text-[11px] font-bold text-white"
                        >
                            {{ pendingRegistrationCount }}
                        </span>
                    </Link>

                    <button
                        v-if="registrationUrl"
                        type="button"
                        @click="copyRegistrationLink"
                        class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-orange-50 px-4 py-2.5 text-sm font-semibold text-orange-600 transition hover:border-orange-300"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                        Registration Link
                    </button>
                </div>
            </div>

            <div v-if="selectedBranch" class="flex items-center gap-2 pt-1">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-semibold text-slate-800">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5">
                        <path d="M3 21h18" />
                        <path d="M5 21V7l7-4 7 4v14" />
                        <path d="M9 9h.01" />
                        <path d="M9 13h.01" />
                        <path d="M15 9h.01" />
                        <path d="M15 13h.01" />
                    </svg>
                    {{ selectedBranch.name }}
                </span>
                <span class="text-xs text-slate-600">Showing members for this branch only</span>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <Link
                    v-for="card in statCards"
                    :key="card.label"
                    :href="buildUrl({ status: card.filter, page: 1 })"
                    class="rounded-[22px] border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:border-slate-300"
                >
                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-700">{{ card.label }}</p>
                    <p class="mt-2 text-[18px] font-semibold leading-none" :class="card.color">{{ card.value }}</p>
                </Link>
            </div>

            <div class="rounded-[22px] border border-slate-200 bg-white p-3 shadow-sm">
                <form class="flex flex-wrap items-center gap-2" @submit.prevent="applyFilters">
                    <div class="flex min-w-[260px] flex-1 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5">
                        <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input
                            v-model="filterSearch"
                            type="text"
                            placeholder="Search name, phone, email, ID..."
                            class="w-full bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400"
                        >
                    </div>

                    <select v-model="filterStatus" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="expired">Expired</option>
                        <option value="frozen">Frozen</option>
                    </select>

                    <select v-model="filterGender" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none">
                        <option value="">All genders</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>

                    <div class="ml-auto flex items-center gap-2">
                        <button
                            v-if="currentSearch || currentStatus || currentGender"
                            type="button"
                            @click="clearFilters"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:border-slate-300"
                        >
                            Clear filters
                        </button>
                        <button type="submit" class="rounded-2xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
                            Apply
                        </button>
                        <Link href="/members/create" class="inline-flex items-center gap-2 rounded-2xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
                            <span>+</span>
                            <span>Add member</span>
                        </Link>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                <div v-if="memberRows.length === 0" class="flex flex-col items-center gap-4 py-20 text-center">
                    <div class="flex h-[76px] w-[76px] items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-500">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" />
                            <circle cx="9.5" cy="7" r="3" />
                            <path d="M20 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 4.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <p class="text-base font-semibold text-slate-900">
                        {{ currentSearch || currentStatus || currentGender ? 'No members found' : 'No members yet.' }}
                    </p>
                    <p class="text-sm text-slate-500">
                        {{ currentSearch || currentStatus || currentGender ? 'Try adjusting the filters.' : 'Add your first member to get started.' }}
                    </p>
                    <button
                        v-if="currentSearch || currentStatus || currentGender"
                        type="button"
                        @click="clearFilters"
                        class="rounded-2xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500"
                    >
                        Clear all
                    </button>
                    <Link
                        v-else
                        href="/members/create"
                        class="rounded-2xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500"
                    >
                        Add member
                    </Link>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-left">
                        <thead class="border-b border-slate-200 bg-slate-50/80">
                            <tr>
                                <th
                                    v-for="column in columns"
                                    :key="column.label"
                                    class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600"
                                >
                                    <Link
                                        v-if="column.key"
                                        :href="buildUrl({
                                            sort_by: column.key,
                                            sort_dir: currentSortBy === column.key && currentSortDir === 'asc' ? 'desc' : 'asc',
                                            page: 1,
                                        })"
                                        class="inline-flex items-center gap-1.5"
                                    >
                                        <span>{{ column.label }}</span>
                                        <span class="text-[10px]" :class="currentSortBy === column.key ? 'opacity-100' : 'opacity-25'">â–¾</span>
                                    </Link>
                                    <span v-else>{{ column.label }}</span>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="member in memberRows" :key="member.id" class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50/60">
                                <td class="px-5 py-4 text-xs font-mono text-slate-500">{{ member.member_code }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-orange-100 text-[11px] font-bold text-orange-700">
                                            {{ member.initials }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ member.name }}</p>
                                            <p v-if="member.email" class="text-xs text-slate-500">{{ member.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ member.phone }}</td>
                                <td class="px-5 py-4 text-sm text-slate-800">{{ member.plan_name || 'â€”' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ formatDate(member.created_at) }}</td>
                                <td class="px-5 py-4 text-sm" :class="member.effective_status === 'expired' ? 'text-red-500' : 'text-slate-600'">
                                    {{ member.expiry_date ? formatDate(member.expiry_date) : 'â€”' }}
                                </td>
                                <td class="px-5 py-4">
                                    <button
                                        v-if="member.status === 'active' || member.status === 'inactive'"
                                        type="button"
                                        @click="toggleStatus(member)"
                                        class="inline-flex items-center gap-2"
                                    >
                                        <span
                                            class="relative h-5 w-9 rounded-full transition"
                                            :class="member.status === 'active' ? 'bg-emerald-500' : 'bg-slate-300'"
                                        >
                                            <span
                                                class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition"
                                                :class="member.status === 'active' ? 'left-[18px]' : 'left-0.5'"
                                            />
                                        </span>
                                        <span class="text-xs font-semibold" :class="getStatusConfig(member.effective_status).text">
                                            {{ member.status_label }}
                                        </span>
                                    </button>

                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold"
                                        :class="getStatusConfig(member.effective_status).soft"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-current" />
                                        {{ member.status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    <span v-if="member.balance_paise < 0" class="font-semibold text-red-500">{{ member.balance_rupees }}</span>
                                    <span v-else class="text-slate-500">â‚¹0.00</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="relative inline-flex">
                                        <button
                                            type="button"
                                            @click.stop="openActionMenu($event, member)"
                                            class="inline-flex items-center rounded-lg border border-transparent px-2 py-1.5 text-slate-500 transition hover:border-slate-200 hover:bg-slate-50 hover:text-slate-800"
                                        >
                                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                                                <circle cx="5" cy="12" r="1.5" />
                                                <circle cx="12" cy="12" r="1.5" />
                                                <circle cx="19" cy="12" r="1.5" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="memberRows.length > 0" class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 px-5 py-3 sm:flex-row">
                    <p class="text-xs text-slate-500">
                        Showing {{ members.from || 0 }} to {{ members.to || 0 }} of {{ members.total || memberRows.length }} records
                    </p>

                    <div class="flex items-center gap-3">
                        <select
                            :value="currentPerPage"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 outline-none"
                            @change="changePerPage($event.target.value)"
                        >
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                            <option value="100">100 / page</option>
                        </select>
                    </div>
                </div>
            </div>

            <div
                v-if="openMenuId && activeMenuMember"
                class="fixed z-30 min-w-[180px] rounded-2xl border border-slate-200 bg-white p-2 text-left shadow-[0_16px_40px_rgba(15,23,42,0.14)]"
                :style="{ top: `${menuPosition.top}px`, right: `${menuPosition.right}px` }"
                @click.stop
            >
                <Link :href="`/members/${activeMenuMember.id}`" class="block rounded-xl px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50">
                    View profile
                </Link>
                <Link :href="`/members/${activeMenuMember.id}/edit`" class="block rounded-xl px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50">
                    Edit
                </Link>
                <Link :href="paymentCollectUrl(activeMenuMember.id)" class="block rounded-xl px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50">
                    Collect fee
                </Link>
                <div class="my-1 border-t border-slate-200"></div>

                <button
                    v-if="activeMenuMember.status === 'frozen'"
                    type="button"
                    @click="unfreezeMember(activeMenuMember)"
                    class="block w-full rounded-xl px-3 py-2 text-left text-sm text-emerald-600 transition hover:bg-emerald-50"
                >
                    Unfreeze membership
                </button>

                <button
                    v-else-if="activeMenuMember.effective_status === 'active' && activeMenuMember.plan?.allow_freeze"
                    type="button"
                    @click="openFreezeModal(activeMenuMember)"
                    class="block w-full rounded-xl px-3 py-2 text-left text-sm text-sky-600 transition hover:bg-sky-50"
                >
                    Freeze membership
                </button>

                <button
                    v-else-if="activeMenuMember.effective_status === 'active' && !activeMenuMember.plan?.allow_freeze"
                    type="button"
                    disabled
                    class="block w-full cursor-not-allowed rounded-xl px-3 py-2 text-left text-sm text-slate-400"
                >
                    Freeze membership
                </button>

                <div class="my-1 border-t border-slate-200"></div>
                <button
                    type="button"
                    @click="deleteMember(activeMenuMember)"
                    class="block w-full rounded-xl px-3 py-2 text-left text-sm text-red-500 transition hover:bg-red-50"
                >
                    Delete
                </button>
            </div>

            <div v-if="freezeTarget" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/45 px-4">
                <div class="w-full max-w-[420px] rounded-[28px] border border-slate-200 bg-white shadow-[0_24px_60px_rgba(15,23,42,0.18)]">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Freeze Membership</h3>
                            <p class="mt-1 text-sm text-slate-500">Pause {{ freezeTarget.name }} for a fixed number of days.</p>
                        </div>
                        <button type="button" @click="closeFreezeModal" class="text-slate-400 transition hover:text-slate-700">âœ•</button>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Freeze days</label>
                            <input
                                v-model="freezeForm.freeze_days"
                                type="number"
                                min="1"
                                :max="freezeTarget.plan?.max_freeze_days || 3650"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400"
                            >
                            <p v-if="freezeTarget.plan?.max_freeze_days" class="mt-2 text-xs text-slate-500">
                                Max allowed by plan: {{ freezeTarget.plan.max_freeze_days }} days
                            </p>
                            <p v-if="freezeForm.errors.freeze_days" class="mt-2 text-xs text-red-500">{{ freezeForm.errors.freeze_days }}</p>
                            <p v-if="freezeForm.errors.freeze" class="mt-2 text-xs text-red-500">{{ freezeForm.errors.freeze }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4">
                        <button type="button" @click="closeFreezeModal" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                            Cancel
                        </button>
                        <button type="button" @click="submitFreeze" class="rounded-2xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-500" :disabled="freezeForm.processing">
                            Freeze
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

