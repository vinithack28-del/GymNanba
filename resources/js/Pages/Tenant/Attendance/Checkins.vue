<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    logs: {
        type: Object,
        default: () => ({}),
    },
    branches: {
        type: [Array, Object],
        default: () => [],
    },
    methods: {
        type: [Array, Object],
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    canCheckin: Boolean,
});

const checkinRows = computed(() => props.logs?.data || []);
const paginationLinks = computed(() => (props.logs?.links || []).filter((link) => link.url || link.active));
const branchOptions = computed(() => Object.values(props.branches || {}));
const methodOptions = computed(() => Object.values(props.methods || {}));
const filterPerPage = ref(props.filters?.per_page || props.logs?.per_page || 25);

const memberSearch = ref('');
const memberResults = ref([]);
const searchingMembers = ref(false);
const memberSearchError = ref('');

const checkinForm = useForm({
    member_id: '',
    branch_id: props.filters?.branch_id || '',
    method: 'manual',
    reason: '',
});

const selectedMember = ref(null);

const perPageUrl = computed(() => {
    const params = new URLSearchParams();

    Object.entries(props.filters || {}).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            params.set(key, value);
        }
    });

    params.set('per_page', filterPerPage.value);
    params.delete('page');

    return `/attendance/checkins?${params.toString()}`;
});

const exportUrl = computed(() => {
    const params = new URLSearchParams();

    Object.entries(props.filters || {}).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            params.set(key, value);
        }
    });

    return `/attendance/checkins/export?${params.toString()}`;
});

const formatDateTime = (value) => {
    if (!value) {
        return '-';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return date.toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).replace(',', '').replaceAll('/', '-');
};

const formatMethod = (method) => String(method || '-').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

const getMethodBadge = (method) => {
    const badges = {
        manual: 'bg-sky-500/15 text-sky-400',
        qr: 'bg-emerald-500/15 text-emerald-400',
        biometric: 'bg-purple-500/15 text-purple-400',
    };

    return badges[method] || 'bg-slate-500/15 text-slate-400';
};

const searchMembers = async () => {
    const query = memberSearch.value.trim();
    memberSearchError.value = '';

    if (query.length < 2) {
        memberResults.value = [];
        return;
    }

    searchingMembers.value = true;

    try {
        const response = await fetch(`/attendance/member-search?q=${encodeURIComponent(query)}`, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to search members.');
        }

        memberResults.value = await response.json();
    } catch (error) {
        memberSearchError.value = error.message || 'Unable to search members.';
    } finally {
        searchingMembers.value = false;
    }
};

let memberSearchTimer = null;
const queueMemberSearch = () => {
    selectedMember.value = null;
    checkinForm.member_id = '';
    window.clearTimeout(memberSearchTimer);
    memberSearchTimer = window.setTimeout(searchMembers, 250);
};

const chooseMember = (member) => {
    selectedMember.value = member;
    checkinForm.member_id = member.id;
    memberSearch.value = `${member.name} ${member.member_code ? `(${member.member_code})` : ''}`.trim();
    memberResults.value = [];
};

const submitCheckin = () => {
    checkinForm.post('/attendance/checkins', {
        preserveScroll: true,
        onSuccess: () => {
            checkinForm.reset('member_id', 'reason');
            selectedMember.value = null;
            memberSearch.value = '';
            memberResults.value = [];
        },
    });
};

const checkout = (log) => {
    router.patch(`/attendance/checkins/${log.id}/checkout`, {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Daily Check-ins" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold md:text-2xl">Daily Check-ins</h1>
                    <p class="app-muted mt-0.5 text-sm">Track member attendance for the selected day.</p>
                </div>

                <Link :href="exportUrl" class="app-panel rounded-lg border px-3 py-2 text-xs font-semibold transition hover:opacity-80">Export</Link>
            </div>

            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Total Today</p>
                    <p class="mt-1 text-lg font-semibold">{{ stats?.total || 0 }}</p>
                </div>
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Active Now</p>
                    <p class="mt-1 text-lg font-semibold text-emerald-400">{{ stats?.active || 0 }}</p>
                </div>
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Checked Out</p>
                    <p class="mt-1 text-lg font-semibold text-orange-400">{{ stats?.checked_out || 0 }}</p>
                </div>
            </div>

            <form method="GET" action="/attendance/checkins" class="app-panel flex flex-wrap items-center gap-2 rounded-xl border p-3">
                <input
                    type="date"
                    name="date"
                    :value="filters?.date"
                    class="app-panel-strong rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400"
                >

                <input
                    name="search"
                    :value="filters?.search"
                    placeholder="Search by name, code, phone..."
                    class="app-panel-strong min-w-[180px] flex-1 rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400"
                >

                <select name="branch_id" class="app-panel-strong min-w-[150px] rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                    <option value="">All Branches</option>
                    <option v-for="branch in branchOptions" :key="branch.id" :value="branch.id" :selected="Number(filters?.branch_id) === Number(branch.id)">{{ branch.name }}</option>
                </select>

                <select name="method" class="app-panel-strong min-w-[140px] rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                    <option value="">All Methods</option>
                    <option v-for="method in methodOptions" :key="method" :value="method" :selected="filters?.method === method">{{ formatMethod(method) }}</option>
                </select>

                <button type="submit" class="rounded-lg bg-orange-500 px-3 py-2 text-xs font-semibold text-slate-950 transition hover:bg-orange-400">Apply</button>
            </form>

            <form v-if="canCheckin" @submit.prevent="submitCheckin" class="app-panel rounded-xl border p-3">
                <div class="grid gap-2 lg:grid-cols-[minmax(240px,1fr)_150px_150px_auto]">
                    <div class="relative">
                        <label class="mb-1 block text-xs font-semibold">Member</label>
                        <input
                            v-model="memberSearch"
                            type="search"
                            placeholder="Search member to check in..."
                            class="app-panel-strong w-full rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400"
                            :class="{ 'field-invalid': checkinForm.errors.member_id }"
                            @input="queueMemberSearch"
                        >

                        <div v-if="memberResults.length || searchingMembers || memberSearchError" class="app-panel-strong absolute z-20 mt-1 max-h-64 w-full overflow-auto rounded-lg border shadow-xl">
                            <div v-if="searchingMembers" class="app-muted px-3 py-2 text-xs">Searching...</div>
                            <button
                                v-for="member in memberResults"
                                :key="member.id"
                                type="button"
                                class="block w-full px-3 py-2 text-left text-sm transition hover:bg-white/5"
                                @click="chooseMember(member)"
                            >
                                <span class="font-semibold">{{ member.name }}</span>
                                <span class="app-muted ml-2 text-xs">{{ member.member_code || '-' }}</span>
                                <span class="app-muted block text-xs">{{ member.phone || '-' }} - {{ member.plan_name || '-' }}</span>
                            </button>
                            <div v-if="memberSearchError" class="field-error px-3 py-2">{{ memberSearchError }}</div>
                        </div>
                        <p v-if="checkinForm.errors.member_id" class="field-error">{{ checkinForm.errors.member_id }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold">Method</label>
                        <select v-model="checkinForm.method" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                            <option v-for="method in methodOptions" :key="method" :value="method">{{ formatMethod(method) }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold">Branch</label>
                        <select v-model="checkinForm.branch_id" class="app-panel-strong w-full rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                            <option value="">Auto</option>
                            <option v-for="branch in branchOptions" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-lg bg-orange-500 px-3 py-2 text-xs font-semibold text-slate-950 transition hover:bg-orange-400 disabled:opacity-60" :disabled="checkinForm.processing || !checkinForm.member_id">
                            Check In
                        </button>
                    </div>
                </div>

                <p v-if="selectedMember" class="app-muted mt-2 text-xs">
                    Selected: {{ selectedMember.name }} - {{ selectedMember.member_code || selectedMember.phone || '-' }}
                </p>
            </form>

            <div class="app-panel overflow-hidden rounded-xl border">
                <div v-if="checkinRows.length === 0" class="p-6 text-center text-sm app-muted">No check-ins found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[780px] text-left text-sm">
                        <thead class="app-table-head text-[11px] font-bold uppercase tracking-[0.08em] app-muted">
                            <tr>
                                <th class="px-3 py-2">Member</th>
                                <th class="px-3 py-2">Check In</th>
                                <th class="px-3 py-2">Method</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Branch</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr v-for="checkin in checkinRows" :key="checkin.id" class="transition hover:bg-white/5">
                                <td class="px-3 py-2">
                                    <p class="font-semibold">{{ checkin.member?.name || 'Unknown' }}</p>
                                    <p class="app-muted text-xs">{{ checkin.member?.member_code || '-' }}</p>
                                </td>
                                <td class="px-3 py-2 app-muted">{{ formatDateTime(checkin.checked_in_at) }}</td>
                                <td class="px-3 py-2">
                                    <span class="rounded-full px-2 py-1 text-[11px] font-semibold" :class="getMethodBadge(checkin.method)">
                                        {{ formatMethod(checkin.method) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <span :class="checkin.checked_out_at ? 'app-muted' : 'text-emerald-400'">
                                        {{ checkin.checked_out_at ? 'Checked Out' : 'Active' }}
                                    </span>
                                    <p v-if="checkin.checked_out_at" class="app-muted text-xs">{{ formatDateTime(checkin.checked_out_at) }}</p>
                                </td>
                                <td class="px-3 py-2 app-muted">{{ checkin.branch?.name || '-' }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex justify-end">
                                        <button
                                            v-if="!checkin.checked_out_at"
                                            type="button"
                                            class="app-panel rounded-lg border px-2.5 py-1.5 text-xs font-semibold transition hover:opacity-80"
                                            @click="checkout(checkin)"
                                        >
                                            Check Out
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="logs?.links?.length" class="app-panel flex flex-col items-center justify-between gap-3 rounded-xl border px-4 py-3 sm:flex-row">
                <p class="app-muted text-xs">
                    Showing {{ logs.from || 0 }} to {{ logs.to || 0 }} of {{ logs.total || 0 }} records
                </p>

                <div class="flex flex-wrap items-center justify-center gap-2">
                    <select v-model="filterPerPage" @change="$inertia.visit(perPageUrl, { preserveScroll: true, preserveState: true })" class="app-panel-strong rounded-lg border px-2.5 py-1.5 text-xs outline-none focus:border-orange-400">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                        <option value="100">100 / page</option>
                    </select>

                    <div v-if="paginationLinks.length > 1" class="flex flex-wrap items-center gap-1">
                        <Link
                            v-for="link in paginationLinks"
                            :key="link.label"
                            :href="link.url || '#'"
                            preserve-scroll
                            preserve-state
                            :class="[
                                'rounded-lg px-2.5 py-1.5 text-xs font-semibold transition',
                                link.active ? 'bg-orange-500 text-slate-950' : 'app-panel border hover:opacity-80',
                                !link.url && !link.active ? 'pointer-events-none opacity-40' : ''
                            ]"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
