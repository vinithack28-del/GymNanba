<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const props = defineProps({
    equipment: {
        type: Object,
        default: () => ({}),
    },
    summary: Object,
    types: {
        type: Object,
        default: () => ({}),
    },
    statuses: {
        type: Object,
        default: () => ({}),
    },
    serviceTypes: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
    canServiceRecord: Boolean,
});

const rows = computed(() => props.equipment?.data || []);
const filterSearch = ref(props.filters?.search || '');
const filterType = ref(props.filters?.type || '');
const filterStatus = ref(props.filters?.status || '');
const filterPerPage = ref(props.filters?.per_page || props.equipment?.per_page || 25);
const paginationLinks = computed(() => (props.equipment?.links || []).filter((link) => link.url || link.active));
const hasActiveFilters = computed(() => filterSearch.value || filterType.value || filterStatus.value);

const formDrawerOpen = ref(false);
const detailsDrawerOpen = ref(false);
const formMode = ref('add');
const selectedEquipment = ref(null);
const selectedDetails = ref(null);
const actionError = ref('');
const formErrors = reactive({});
const saving = ref(false);
const detailLoading = ref(false);
let searchTimer = null;

const form = reactive({
    name: '',
    type: '',
    status: 'operational',
    brand: '',
    model: '',
    location: '',
    purchase_date: '',
    warranty_expiry: '',
    purchase_price: '',
    notes: '',
});

const serviceForm = reactive({
    service_date: new Date().toISOString().slice(0, 10),
    service_type: '',
    cost: '',
    service_provider: '',
    notes: '',
});
const serviceErrors = reactive({});
const serviceSaving = ref(false);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const statusClass = (status) => ({
    'bg-emerald-500/10 text-emerald-400': status === 'operational',
    'bg-amber-500/10 text-amber-400': status === 'maintenance',
    'bg-red-500/10 text-red-400': status === 'broken',
});

const statusColor = (status) => {
    const colors = {
        operational: { bg: 'rgba(34,197,94,0.12)', fg: '#22c55e' },
        maintenance: { bg: 'rgba(245,158,11,0.12)', fg: '#f59e0b' },
        broken: { bg: 'rgba(239,68,68,0.12)', fg: '#ef4444' },
    };
    return colors[status] || colors.operational;
};

const resetErrors = (target) => {
    Object.keys(target).forEach((key) => delete target[key]);
};

const firstError = (errors) => Object.values(errors || {})[0] || '';

const parseErrors = async (response) => {
    const fallback = 'Something went wrong. Please try again.';
    try {
        const payload = await response.json();
        if (payload?.errors) {
            return Object.fromEntries(
                Object.entries(payload.errors).map(([field, messages]) => [
                    field,
                    Array.isArray(messages) ? messages[0] : messages,
                ])
            );
        }
        return { general: payload?.message || fallback };
    } catch {
        return { general: fallback };
    }
};

const refreshEquipment = () => {
    router.reload({
        only: ['equipment', 'summary', 'filters'],
        preserveScroll: true,
    });
};

const applyFilters = () => {
    router.get('/equipment', {
        search: filterSearch.value.trim() || undefined,
        type: filterType.value || undefined,
        status: filterStatus.value || undefined,
        per_page: filterPerPage.value || undefined,
        page: undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['equipment', 'summary', 'filters'],
    });
};

const applySearch = () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => applyFilters(), 250);
};

const clearFilters = () => {
    filterSearch.value = '';
    filterType.value = '';
    filterStatus.value = '';
    filterPerPage.value = props.equipment?.per_page || 25;
    applyFilters();
};

const clearForm = () => {
    Object.assign(form, {
        name: '',
        type: '',
        status: 'operational',
        brand: '',
        model: '',
        location: '',
        purchase_date: '',
        warranty_expiry: '',
        purchase_price: '',
        notes: '',
    });
    resetErrors(formErrors);
    actionError.value = '';
};

const openAddDrawer = () => {
    formMode.value = 'add';
    selectedEquipment.value = null;
    clearForm();
    formDrawerOpen.value = true;
};

const openEditDrawer = (item) => {
    formMode.value = 'edit';
    selectedEquipment.value = item;
    resetErrors(formErrors);
    actionError.value = '';
    Object.assign(form, {
        name: item.name || '',
        type: item.type || '',
        status: item.status || 'operational',
        brand: item.brand || '',
        model: item.model || '',
        location: item.location || '',
        purchase_date: item.purchase_date || '',
        warranty_expiry: item.warranty_expiry || '',
        purchase_price: item.purchase_price_paise != null ? Number(item.purchase_price_paise / 100).toFixed(0) : '',
        notes: item.notes || '',
    });
    formDrawerOpen.value = true;
};

const closeFormDrawer = () => {
    formDrawerOpen.value = false;
    saving.value = false;
    resetErrors(formErrors);
};

const submitEquipment = async () => {
    resetErrors(formErrors);
    actionError.value = '';
    saving.value = true;

    const payload = {
        ...form,
        brand: form.brand || null,
        model: form.model || null,
        location: form.location || null,
        purchase_date: form.purchase_date || null,
        warranty_expiry: form.warranty_expiry || null,
        purchase_price: form.purchase_price === '' ? null : form.purchase_price,
        notes: form.notes || null,
    };

    try {
        const url = formMode.value === 'edit' ? `/equipment/${selectedEquipment.value.id}` : '/equipment';
        const response = await fetch(url, {
            method: formMode.value === 'edit' ? 'PUT' : 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            Object.assign(formErrors, await parseErrors(response));
            return;
        }

        closeFormDrawer();
        refreshEquipment();
    } catch {
        actionError.value = 'Unable to save equipment right now.';
    } finally {
        saving.value = false;
    }
};

const openDetails = async (item) => {
    selectedEquipment.value = item;
    selectedDetails.value = null;
    detailLoading.value = true;
    detailsDrawerOpen.value = true;
    actionError.value = '';
    resetErrors(serviceErrors);

    try {
        const response = await fetch(`/equipment/${item.id}/details`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to load equipment details.');
        }

        selectedDetails.value = await response.json();
    } catch (error) {
        actionError.value = error.message || 'Unable to load equipment details.';
    } finally {
        detailLoading.value = false;
    }
};

const closeDetails = () => {
    detailsDrawerOpen.value = false;
    selectedDetails.value = null;
    resetErrors(serviceErrors);
};

const resetServiceForm = () => {
    Object.assign(serviceForm, {
        service_date: new Date().toISOString().slice(0, 10),
        service_type: '',
        cost: '',
        service_provider: '',
        notes: '',
    });
};

const submitServiceRecord = async () => {
    if (!selectedDetails.value) return;
    resetErrors(serviceErrors);
    serviceSaving.value = true;

    try {
        const response = await fetch(`/equipment/${selectedDetails.value.id}/service-records`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                ...serviceForm,
                service_provider: serviceForm.service_provider || null,
                notes: serviceForm.notes || null,
            }),
        });

        if (!response.ok) {
            Object.assign(serviceErrors, await parseErrors(response));
            return;
        }

        const record = await response.json();
        selectedDetails.value.service_records = [record, ...(selectedDetails.value.service_records || [])];
        resetServiceForm();
    } catch {
        serviceErrors.general = 'Unable to add service record right now.';
    } finally {
        serviceSaving.value = false;
    }
};

const deleteServiceRecord = async (record) => {
    if (!selectedDetails.value || !window.confirm('Delete this service record?')) return;

    try {
        const response = await fetch(`/equipment/${selectedDetails.value.id}/service-records/${record.id}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error();
        }

        selectedDetails.value.service_records = selectedDetails.value.service_records.filter((item) => item.id !== record.id);
    } catch {
        actionError.value = 'Unable to delete service record.';
    }
};

const deleteEquipment = async (item) => {
    if (!window.confirm(`Delete equipment "${item.name}" and all service history?`)) return;

    try {
        const response = await fetch(`/equipment/${item.id}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error();
        }

        if (selectedDetails.value?.id === item.id) {
            closeDetails();
        }
        refreshEquipment();
    } catch {
        actionError.value = 'Unable to delete equipment.';
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Equipment" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold md:text-2xl">Equipment</h1>
                    <p class="mt-0.5 text-sm text-slate-300">Track gym equipment, status, and maintenance history.</p>
                </div>
                <button v-if="canAdd" type="button" @click="openAddDrawer" class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                    Add Equipment
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Total Equipment</p>
                    <p class="mt-0.5 text-2xl font-bold">{{ summary?.total || 0 }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Operational</p>
                    <p class="mt-0.5 text-2xl font-bold text-emerald-400">{{ summary?.operational || 0 }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Maintenance</p>
                    <p class="mt-0.5 text-2xl font-bold text-amber-400">{{ summary?.maintenance || 0 }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Broken</p>
                    <p class="mt-0.5 text-2xl font-bold text-red-400">{{ summary?.broken || 0 }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="flex min-w-[190px] flex-1 items-center gap-2 rounded-lg border border-white/10 bg-slate-950/50 px-2.5 py-2">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                    <input v-model="filterSearch" @input="applySearch" type="text" placeholder="Search name, brand, model..." class="w-full bg-transparent text-sm text-slate-300 outline-none">
                </div>
                <select v-model="filterType" @change="applyFilters" class="rounded-lg border border-white/10 bg-slate-950/50 px-2.5 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <option value="">All Types</option>
                    <option v-for="(label, value) in types" :key="value" :value="value">{{ label }}</option>
                </select>
                <select v-model="filterStatus" @change="applyFilters" class="rounded-lg border border-white/10 bg-slate-950/50 px-2.5 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <option value="">All Status</option>
                    <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                </select>
                <button v-if="hasActiveFilters" type="button" @click="clearFilters" class="rounded-lg border border-white/10 bg-slate-950/50 px-2.5 py-2 text-sm font-medium text-slate-400 hover:bg-white/5">
                    Clear
                </button>
            </div>

            <div v-if="actionError" class="rounded-xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-500">
                {{ actionError }}
            </div>

            <div class="overflow-hidden rounded-xl border border-white/10 bg-white/5">
                <div v-if="rows.length === 0" class="p-6 text-center">
                    <p class="font-semibold">No equipment found.</p>
                    <p class="app-muted mt-1 text-sm">Add your first equipment item or adjust filters.</p>
                    <button v-if="canAdd" type="button" @click="openAddDrawer" class="mt-3 rounded-lg bg-orange-500 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                        Add Equipment
                    </button>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-[13px]">
                        <thead class="bg-slate-950/60 text-[11px] font-bold uppercase tracking-[0.06em] text-slate-400">
                            <tr>
                                <th class="px-3 py-2.5 text-left">Equipment</th>
                                <th class="px-3 py-2.5 text-left">Type</th>
                                <th class="px-3 py-2.5 text-left">Brand / Model</th>
                                <th class="px-3 py-2.5 text-left">Status</th>
                                <th class="px-3 py-2.5 text-left">Purchase</th>
                                <th class="px-3 py-2.5 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="item in rows" :key="item.id" class="hover:bg-white/5">
                                <td class="px-3 py-2.5">
                                    <button type="button" @click="openDetails(item)" class="font-semibold text-orange-400 hover:underline">{{ item.name }}</button>
                                    <p class="text-xs text-slate-400">{{ item.location || item.branch_name || 'No location added' }}</p>
                                </td>
                                <td class="px-3 py-2.5 text-slate-400">{{ types[item.type] || item.type }}</td>
                                <td class="px-3 py-2.5">
                                    <p>{{ item.brand || '-' }}</p>
                                    <p class="text-xs text-slate-400">{{ item.model || '-' }}</p>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="statusClass(item.status)">
                                        {{ statuses[item.status] || item.status }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-slate-400">{{ item.purchase_date_fmt || '-' }}</td>
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" @click="openDetails(item)" class="locker-icon-btn locker-icon-btn-view" title="View equipment" aria-label="View equipment">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                        <button v-if="canEdit" type="button" @click="openEditDrawer(item)" class="locker-icon-btn locker-icon-btn-edit" title="Edit equipment" aria-label="Edit equipment">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </button>
                                        <button v-if="canDelete" type="button" @click="deleteEquipment(item)" class="locker-icon-btn text-red-400" title="Delete equipment" aria-label="Delete equipment">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="equipment?.links?.length" class="flex flex-col items-center justify-between gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 sm:flex-row">
                <p class="text-xs text-slate-400">
                    Showing {{ equipment.from || 0 }} to {{ equipment.to || 0 }} of {{ equipment.total || 0 }} equipment
                </p>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <select v-model="filterPerPage" @change="applyFilters()" class="rounded-lg border border-white/10 bg-slate-950/50 px-2.5 py-1.5 text-xs outline-none focus:border-orange-400">
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
                                link.active ? 'bg-orange-500 text-slate-950' : 'border border-white/10 bg-white/5 text-slate-400 hover:bg-white/10',
                                !link.url && !link.active ? 'pointer-events-none opacity-40' : ''
                            ]"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="formDrawerOpen || detailsDrawerOpen" class="locker-drawer-backdrop fixed inset-0 z-40" @click="formDrawerOpen ? closeFormDrawer() : closeDetails()"></div>

        <aside
            class="app-panel-strong fixed right-0 top-0 z-50 flex h-full w-[460px] max-w-full flex-col border-l shadow-2xl transition-transform duration-200"
            :class="formDrawerOpen ? 'translate-x-0' : 'translate-x-full'"
        >
            <div class="flex items-center justify-between border-b px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold">{{ formMode === 'edit' ? 'Edit Equipment' : 'Add Equipment' }}</h2>
                    <p class="app-muted mt-1 text-xs">{{ formMode === 'edit' ? 'Update equipment details.' : 'Capture equipment details and purchase information.' }}</p>
                </div>
                <button type="button" @click="closeFormDrawer" class="rounded-lg border px-2.5 py-1.5 text-sm app-muted transition hover:opacity-80">x</button>
            </div>

            <form @submit.prevent="submitEquipment" class="flex flex-1 flex-col overflow-y-auto">
                <div class="flex-1 space-y-4 p-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Equipment Name <span class="text-red-400">*</span></label>
                        <input v-model="form.name" type="text" maxlength="150" required placeholder="e.g. Treadmill Pro 3000" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': formErrors.name }">
                        <p v-if="formErrors.name" class="field-error">{{ formErrors.name }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Type <span class="text-red-400">*</span></label>
                            <select v-model="form.type" required class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': formErrors.type }">
                                <option value="">Select type</option>
                                <option v-for="(label, value) in types" :key="value" :value="value">{{ label }}</option>
                            </select>
                            <p v-if="formErrors.type" class="field-error">{{ formErrors.type }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Status</label>
                            <select v-model="form.status" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': formErrors.status }">
                                <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                            </select>
                            <p v-if="formErrors.status" class="field-error">{{ formErrors.status }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Brand</label>
                            <input v-model="form.brand" type="text" maxlength="100" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Model</label>
                            <input v-model="form.model" type="text" maxlength="100" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Location</label>
                        <input v-model="form.location" type="text" maxlength="200" placeholder="e.g. Cardio Zone" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Purchase Date</label>
                            <input v-model="form.purchase_date" type="date" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': formErrors.purchase_date }">
                            <p v-if="formErrors.purchase_date" class="field-error">{{ formErrors.purchase_date }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Warranty Expiry</label>
                            <input v-model="form.warranty_expiry" type="date" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Purchase Price</label>
                        <input v-model="form.purchase_price" type="number" min="0" step="1" placeholder="0" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Notes</label>
                        <textarea v-model="form.notes" rows="3" maxlength="1000" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400"></textarea>
                    </div>

                    <p v-if="formErrors.general || actionError" class="field-error">{{ formErrors.general || actionError }}</p>
                </div>

                <div class="flex justify-end gap-3 border-t px-5 py-4">
                    <button type="button" @click="closeFormDrawer" class="rounded-xl border px-4 py-2 text-sm font-semibold app-muted transition hover:opacity-80">Cancel</button>
                    <button type="submit" class="rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400 disabled:cursor-not-allowed disabled:opacity-60" :disabled="saving">
                        {{ saving ? 'Saving...' : (formMode === 'edit' ? 'Save Changes' : 'Add Equipment') }}
                    </button>
                </div>
            </form>
        </aside>

        <aside
            class="app-panel-strong fixed right-0 top-0 z-50 flex h-full w-[620px] max-w-full flex-col border-l shadow-2xl transition-transform duration-200"
            :class="detailsDrawerOpen ? 'translate-x-0' : 'translate-x-full'"
        >
            <div class="flex items-center justify-between border-b px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold">Equipment Details</h2>
                    <p class="app-muted mt-1 text-xs">Details and service history.</p>
                </div>
                <button type="button" @click="closeDetails" class="rounded-lg border px-2.5 py-1.5 text-sm app-muted transition hover:opacity-80">x</button>
            </div>

            <div class="flex-1 overflow-y-auto p-5">
                <div v-if="detailLoading" class="rounded-xl border px-4 py-8 text-center app-muted">Loading equipment details...</div>

                <div v-else-if="selectedDetails" class="space-y-4">
                    <div class="rounded-xl border p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide app-muted">{{ selectedDetails.type_label }}</p>
                                <h3 class="mt-1 text-xl font-semibold">{{ selectedDetails.name }}</h3>
                                <p class="app-muted mt-1">{{ selectedDetails.brand || 'No brand' }} - {{ selectedDetails.model || 'No model' }}</p>
                            </div>
                            <span class="rounded-full px-2 py-1 text-xs font-bold" :style="{ background: statusColor(selectedDetails.status).bg, color: statusColor(selectedDetails.status).fg }">
                                {{ selectedDetails.status_label }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-lg border px-3 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide app-muted">Location</p>
                                <p class="mt-1 font-semibold">{{ selectedDetails.location || '-' }}</p>
                            </div>
                            <div class="rounded-lg border px-3 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide app-muted">Branch</p>
                                <p class="mt-1 font-semibold">{{ selectedDetails.branch_name || '-' }}</p>
                            </div>
                            <div class="rounded-lg border px-3 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide app-muted">Purchase</p>
                                <p class="mt-1 font-semibold">{{ selectedDetails.purchase_date_fmt || '-' }}</p>
                            </div>
                            <div class="rounded-lg border px-3 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide app-muted">Price</p>
                                <p class="mt-1 font-semibold">{{ selectedDetails.purchase_price_fmt || '-' }}</p>
                            </div>
                            <div class="rounded-lg border px-3 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide app-muted">Warranty</p>
                                <p class="mt-1 font-semibold" :class="selectedDetails.warranty_expired ? 'text-red-400' : ''">{{ selectedDetails.warranty_expiry_fmt || '-' }}</p>
                            </div>
                            <div class="rounded-lg border px-3 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide app-muted">Added</p>
                                <p class="mt-1 font-semibold">{{ selectedDetails.created_at || '-' }}</p>
                            </div>
                        </div>

                        <p v-if="selectedDetails.notes" class="app-muted mt-4 text-sm">{{ selectedDetails.notes }}</p>
                    </div>

                    <div v-if="canServiceRecord" class="rounded-xl border p-4">
                        <h3 class="font-semibold">Add Service Record</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold app-muted">Service Date</label>
                                <input v-model="serviceForm.service_date" type="date" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': serviceErrors.service_date }">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold app-muted">Service Type</label>
                                <select v-model="serviceForm.service_type" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': serviceErrors.service_type }">
                                    <option value="">Select type</option>
                                    <option v-for="(label, value) in serviceTypes" :key="value" :value="value">{{ label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold app-muted">Cost</label>
                                <input v-model="serviceForm.cost" type="number" min="0" step="1" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400" :class="{ 'field-invalid': serviceErrors.cost }">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold app-muted">Provider</label>
                                <input v-model="serviceForm.service_provider" type="text" maxlength="200" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold app-muted">Notes</label>
                                <textarea v-model="serviceForm.notes" rows="2" maxlength="1000" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400"></textarea>
                            </div>
                        </div>
                        <p v-if="firstError(serviceErrors)" class="field-error">{{ firstError(serviceErrors) }}</p>
                        <button type="button" @click="submitServiceRecord" class="mt-3 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400 disabled:opacity-60" :disabled="serviceSaving">
                            {{ serviceSaving ? 'Adding...' : 'Add Service Record' }}
                        </button>
                    </div>

                    <div class="overflow-hidden rounded-xl border">
                        <div class="flex items-center justify-between border-b px-4 py-3">
                            <h3 class="font-semibold">Service History</h3>
                            <span class="rounded-full border px-2 py-1 text-xs font-semibold app-muted">{{ selectedDetails.service_records?.length || 0 }} records</span>
                        </div>
                        <div v-if="!selectedDetails.service_records?.length" class="px-4 py-8 text-center app-muted">No service records yet.</div>
                        <div v-else class="divide-y divide-white/10">
                            <div v-for="record in selectedDetails.service_records" :key="record.id" class="flex items-start justify-between gap-3 px-4 py-3">
                                <div>
                                    <p class="font-semibold">{{ record.service_date_fmt }} - {{ record.service_type_label }}</p>
                                    <p class="app-muted mt-1 text-xs">{{ record.cost_fmt }}<span v-if="record.service_provider"> - {{ record.service_provider }}</span></p>
                                    <p v-if="record.notes" class="app-muted mt-1 text-xs">{{ record.notes }}</p>
                                </div>
                                <button v-if="canServiceRecord" type="button" @click="deleteServiceRecord(record)" class="locker-icon-btn text-red-400" title="Delete service record" aria-label="Delete service record">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </AppLayout>
</template>


