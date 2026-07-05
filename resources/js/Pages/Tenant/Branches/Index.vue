<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    branches: {
        type: Array,
        default: () => [],
    },
    activeCount: Number,
    planLimit: Number,
    planName: String,
    credentials: Object,
    amenityOpts: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

const atLimit = computed(() => props.planLimit > 0 && props.activeCount >= props.planLimit);
const inactiveCount = computed(() => props.branches.filter((branch) => branch.status === 'inactive').length);
const flashError = computed(() => page.props.flash?.error || null);

const amenityIcons = {
    pool: 'Ã°Å¸ÂÅ ',
    steam: 'Ã°Å¸â€™Â¨',
    parking: 'Ã°Å¸...Â¿',
    locker: 'Ã°Å¸â€â€™',
    cafeteria: 'Ã¢Ëœ*',
    ac: 'Ã¢Ââ€ž',
    wifi: 'Ã°Å¸â€œÂ¶',
};

const deactivateModalOpen = ref(false);
const targetBranch = ref(null);
const availableReassignBranches = ref([]);
const deactivateForm = useForm({
    reassign_branch_id: '',
});

const usageLabel = computed(() => {
    if (props.planLimit > 0) {
        return `${props.activeCount} of ${props.planLimit} branches used`;
    }

    return `${props.activeCount} branches active`;
});

const formatStatus = (status) => {
    if (!status) return 'Unknown';

    return status.charAt(0).toUpperCase() + status.slice(1);
};

const branchAmenities = (branch) => {
    if (Array.isArray(branch.amenities_list) && branch.amenities_list.length) {
        return branch.amenities_list;
    }

    return Array.isArray(branch.amenities) ? branch.amenities : [];
};

const activeMembersCount = (branch) => Number(branch.active_members_count ?? 0);
const totalMembersCount = (branch) => Number(branch.members_count ?? 0);

const openDeactivateDialog = (branch) => {
    targetBranch.value = branch;
    availableReassignBranches.value = props.branches.filter(
        (candidate) => candidate.status === 'active' && candidate.id !== branch.id,
    );
    deactivateForm.reset();
    deactivateForm.clearErrors();
    deactivateModalOpen.value = true;
};

const closeDeactivateDialog = () => {
    deactivateModalOpen.value = false;
    targetBranch.value = null;
    availableReassignBranches.value = [];
    deactivateForm.reset();
    deactivateForm.clearErrors();
};

const submitDeactivate = () => {
    if (!targetBranch.value) return;

    deactivateForm.patch(`/branches/${targetBranch.value.id}/deactivate`, {
        preserveScroll: true,
        onSuccess: () => closeDeactivateDialog(),
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Branches" />

        <div class="branch-page">
            <div v-if="atLimit" class="branch-alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="9" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <p>You've reached your plan limit of {{ planLimit }} branches on the {{ planName }} plan.</p>
            </div>

            <div v-if="flashError" class="branch-error">
                {{ flashError }}
            </div>

            <div v-if="credentials" class="branch-credentials">
                <div class="branch-credentials__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="11" width="18" height="11" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>
                <div class="branch-credentials__content">
                    <p class="branch-credentials__title">Branch Admin Credentials</p>
                    <p class="branch-credentials__text">Save these credentials for the newly created branch admin login.</p>
                    <div class="branch-credentials__grid">
                        <div>
                            <span>Email</span>
                            <code>{{ credentials.email }}</code>
                        </div>
                        <div>
                            <span>Password</span>
                            <code>{{ credentials.password }}</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="branch-toolbar">
                <div class="branch-pill-wrap">
                    <span class="branch-pill">{{ usageLabel }}</span>
                    <span v-if="inactiveCount" class="branch-pill branch-pill--muted">{{ inactiveCount }} inactive</span>
                </div>
                <Link
                    v-if="!atLimit"
                    href="/branches/create"
                    class="branch-add-btn"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Add branch
                </Link>
                <span
                    v-else
                    class="branch-add-btn is-disabled"
                    title="Upgrade your plan to add more branches."
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Add branch
                </span>
            </div>

            <div v-if="!branches.length" class="branch-empty">
                <div class="branch-empty__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                        <path d="M3 21h18" />
                        <path d="M5 21V7l7-4 7 4v14" />
                        <path d="M9 9h.01" />
                        <path d="M9 13h.01" />
                        <path d="M15 9h.01" />
                        <path d="M15 13h.01" />
                    </svg>
                </div>
                <p class="branch-empty__title">No branches yet</p>
                <p class="branch-empty__text">Add your first branch to start organizing members and operations by location.</p>
                <Link href="/branches/create" class="branch-add-btn">Add Branch</Link>
            </div>

            <div v-else class="branch-grid">
                <article
                    v-for="branch in branches"
                    :key="branch.id"
                    class="branch-card"
                    :class="{ 'branch-card--inactive': branch.status !== 'active' }"
                >
                    <div class="branch-card__header">
                        <div class="branch-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path d="M3 21h18" />
                                <path d="M5 21V7l7-4 7 4v14" />
                                <path d="M9 9h.01" />
                                <path d="M9 13h.01" />
                                <path d="M15 9h.01" />
                                <path d="M15 13h.01" />
                            </svg>
                        </div>

                        <div class="branch-card__body">
                            <div class="branch-card__topline">
                                <div>
                                    <h2 class="branch-card__name">
                                        {{ branch.name }}
                                        <span v-if="branch.is_primary" class="branch-card__primary">Primary</span>
                                    </h2>
                                    <p class="branch-card__address">
                                        {{ branch.address1 }}<template v-if="branch.address2">, {{ branch.address2 }}</template>
                                    </p>
                                    <p class="branch-card__city">
                                        {{ branch.city }}, {{ branch.state }}<template v-if="branch.pin"> - {{ branch.pin }}</template>
                                    </p>
                                </div>

                                <span
                                    class="branch-card__status"
                                    :class="branch.status === 'active' ? 'branch-card__status--active' : 'branch-card__status--inactive'"
                                >
                                    <span class="branch-card__status-dot" />
                                    {{ formatStatus(branch.status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="branch-card__info">
                        <a v-if="branch.phone" :href="`tel:${branch.phone}`" class="branch-info-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M22 16.9v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.8 19.8 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.2h3a2 2 0 0 1 2 1.72c.127.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.79a16 16 0 0 0 6.3 6.3l.96-.96a2 2 0 0 1 2.11-.45c.91.34 1.85.573 2.81.7A2 2 0 0 1 22 16.9z" />
                            </svg>
                            {{ branch.phone }}
                        </a>
                        <span class="branch-info-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" />
                                <circle cx="9.5" cy="7" r="3" />
                            </svg>
                            {{ branch.manager_name || 'No manager assigned' }}
                        </span>
                    </div>

                    <div class="branch-card__stats">
                        <div class="branch-stat">
                            <span class="branch-stat__value">{{ totalMembersCount(branch) }}</span>
                            <span class="branch-stat__label">Members</span>
                        </div>
                        <div class="branch-stat">
                            <span class="branch-stat__value branch-stat__value--success">{{ activeMembersCount(branch) }}</span>
                            <span class="branch-stat__label">Active</span>
                        </div>
                        <div class="branch-stat">
                            <span class="branch-stat__value branch-stat__value--muted">-</span>
                            <span class="branch-stat__label">Check-ins Today</span>
                        </div>
                        <div class="branch-stat">
                            <span class="branch-stat__value branch-stat__value--muted">-</span>
                            <span class="branch-stat__label">Revenue / Mo</span>
                        </div>
                    </div>

                    <div class="branch-card__amenities" :class="{ 'branch-card__amenities--empty': !branchAmenities(branch).length }">
                        <span
                            v-for="amenity in branchAmenities(branch)"
                            :key="amenity"
                            class="branch-amenity"
                        >
                            {{ amenityIcons[amenity] || '*' }} {{ amenityOpts[amenity] || amenity }}
                        </span>
                        <span v-if="!branchAmenities(branch).length" class="branch-amenity branch-amenity--empty">
                            No amenities
                        </span>
                    </div>

                    <div class="branch-card__actions">
                        <Link :href="`/branches/${branch.id}/edit`" class="branch-action">Edit</Link>
                        <Link :href="`/members?branch_id=${branch.id}`" class="branch-action">Members</Link>
                        <button
                            v-if="branch.status === 'active'"
                            type="button"
                            class="branch-action branch-action--danger"
                            @click="openDeactivateDialog(branch)"
                        >
                            Deactivate
                        </button>
                        <Link
                            v-else
                            :href="`/branches/${branch.id}/reactivate`"
                            method="patch"
                            as="button"
                            class="branch-action branch-action--success"
                        >
                            Reactivate
                        </Link>
                    </div>
                </article>
            </div>
        </div>

        <div
            v-if="deactivateModalOpen"
            class="branch-modal-overlay"
            @click.self="closeDeactivateDialog"
        >
            <div class="branch-modal">
                <h3 class="branch-modal__title">Deactivate Branch</h3>
                <p class="branch-modal__text">
                    Deactivate <strong>{{ targetBranch?.name }}</strong>? Members can optionally be reassigned to another active branch.
                </p>

                <div v-if="availableReassignBranches.length" class="branch-modal__field">
                    <label for="reassign_branch_id">Reassign members to</label>
                    <select
                        id="reassign_branch_id"
                        v-model="deactivateForm.reassign_branch_id"
                        class="branch-modal__select"
                    >
                        <option value="">Leave unassigned</option>
                        <option
                            v-for="branch in availableReassignBranches"
                            :key="branch.id"
                            :value="branch.id"
                        >
                            {{ branch.name }}
                        </option>
                    </select>
                </div>

                <p v-if="deactivateForm.errors.reassign_branch_id" class="branch-modal__error">
                    {{ deactivateForm.errors.reassign_branch_id }}
                </p>

                <div class="branch-modal__actions">
                    <button type="button" class="branch-modal__btn branch-modal__btn--ghost" @click="closeDeactivateDialog">
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="branch-modal__btn branch-modal__btn--danger"
                        :disabled="deactivateForm.processing"
                        @click="submitDeactivate"
                    >
                        {{ deactivateForm.processing ? 'Deactivating...' : 'Confirm' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.branch-page {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    max-width: 980px;
}

.branch-header {
    align-items: flex-start;
    display: flex;
    gap: 1rem;
    justify-content: space-between;
}

.branch-eyebrow {
    color: #e59b72;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    margin: 0;
    text-transform: uppercase;
}

.branch-title {
    color: var(--app-text);
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin: 0.15rem 0 0;
}

.branch-subtitle {
    color: var(--app-text-muted);
    font-size: 0.88rem;
    margin: 0.35rem 0 0;
    max-width: 42rem;
}

.branch-toolbar {
    align-items: center;
    display: flex;
    gap: 0.75rem;
    justify-content: space-between;
}

.branch-pill-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.branch-pill {
    background: color-mix(in srgb, #f5d6c8 55%, transparent);
    border: 1px solid color-mix(in srgb, #d9a789 65%, transparent);
    border-radius: 999px;
    color: var(--app-text);
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.5rem 0.9rem;
}

.branch-pill--muted {
    background: color-mix(in srgb, var(--app-panel-strong) 85%, transparent);
    border-color: var(--app-border);
    color: var(--app-text-muted);
}

.branch-alert,
.branch-error,
.branch-credentials {
    border-radius: 1.5rem;
    display: flex;
    gap: 0.85rem;
    padding: 1rem 1.1rem;
}

.branch-alert {
    background: rgba(249, 115, 22, 0.08);
    border: 1px solid rgba(249, 115, 22, 0.25);
    color: #f29c5a;
}

.branch-alert svg {
    flex: none;
    height: 1rem;
    width: 1rem;
}

.branch-error {
    background: rgba(226, 75, 74, 0.1);
    border: 1px solid rgba(226, 75, 74, 0.24);
    color: #ff9e9c;
}

.branch-credentials {
    background: rgba(55, 138, 221, 0.1);
    border: 1px solid rgba(55, 138, 221, 0.28);
}

.branch-credentials__icon {
    align-items: center;
    background: rgba(55, 138, 221, 0.18);
    border-radius: 0.9rem;
    color: #7fc0ff;
    display: inline-flex;
    flex: none;
    height: 2.5rem;
    justify-content: center;
    width: 2.5rem;
}

.branch-credentials__icon svg {
    height: 1.1rem;
    width: 1.1rem;
}

.branch-credentials__title {
    color: var(--app-text);
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0;
}

.branch-credentials__text {
    color: var(--app-text-muted);
    font-size: 0.9rem;
    margin: 0.25rem 0 0.8rem;
}

.branch-credentials__grid {
    display: grid;
    gap: 0.85rem;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.branch-credentials__grid span {
    color: var(--app-text-muted);
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    margin-bottom: 0.3rem;
    text-transform: uppercase;
}

.branch-credentials__grid code {
    background: rgba(15, 23, 42, 0.35);
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 0.75rem;
    color: #dbeafe;
    display: inline-block;
    font-size: 0.92rem;
    padding: 0.45rem 0.7rem;
}

.branch-add-btn {
    align-items: center;
    background: #de7429;
    border: 1px solid transparent;
    border-radius: 0.9rem;
    color: #171717;
    display: inline-flex;
    font-size: 0.86rem;
    font-weight: 700;
    gap: 0.45rem;
    justify-content: center;
    min-height: 2.55rem;
    padding: 0.6rem 0.95rem;
    text-decoration: none;
    transition: transform 140ms ease, opacity 140ms ease;
    white-space: nowrap;
}

.branch-add-btn:hover {
    opacity: 0.92;
    transform: translateY(-1px);
}

.branch-add-btn svg {
    height: 0.82rem;
    width: 0.82rem;
}

.branch-add-btn.is-disabled {
    cursor: not-allowed;
    opacity: 0.45;
}

.branch-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(320px, 430px));
    justify-content: start;
    align-items: start;
}

.branch-card {
    background:
        radial-gradient(circle at top right, rgba(229, 155, 114, 0.07), transparent 34%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01)),
        var(--app-panel);
    border: 1px solid color-mix(in srgb, var(--app-border) 82%, transparent);
    border-radius: 1.75rem;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    max-width: 430px;
    width: 100%;
    min-height: 0;
}

.branch-card--inactive {
    opacity: 0.72;
}

.branch-card__header {
    display: flex;
    gap: 0.85rem;
    padding: 1rem 1rem 0.85rem;
}

.branch-card__icon {
    align-items: center;
    background: color-mix(in srgb, #f8e1d5 45%, transparent);
    border: 1px solid color-mix(in srgb, #d9a789 68%, transparent);
    border-radius: 0.9rem;
    color: #dd7f47;
    display: inline-flex;
    flex: none;
    height: 3.2rem;
    justify-content: center;
    width: 3.2rem;
}

.branch-card__icon svg {
    height: 1.2rem;
    width: 1.2rem;
}

.branch-card__body {
    flex: 1;
    min-width: 0;
}

.branch-card__topline {
    align-items: flex-start;
    display: flex;
    gap: 0.75rem;
    justify-content: space-between;
}

.branch-card__topline > div {
    flex: 1;
    min-width: 0;
}

.branch-card__name {
    color: var(--app-text);
    font-size: 0.95rem;
    font-weight: 700;
    line-height: 1.25;
    margin: 0;
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

.branch-card__primary {
    border: 1px solid color-mix(in srgb, #d9a789 68%, transparent);
    border-radius: 999px;
    color: #dd8a58;
    display: inline-flex;
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    margin-left: 0.45rem;
    padding: 0.22rem 0.5rem;
    text-transform: uppercase;
    vertical-align: middle;
}

.branch-card__address,
.branch-card__city {
    color: var(--app-text-muted);
    font-size: 0.78rem;
    margin: 0.22rem 0 0;
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
}

.branch-card__address {
    -webkit-line-clamp: 2;
}

.branch-card__city {
    -webkit-line-clamp: 1;
}

.branch-card__status {
    align-items: center;
    border-radius: 999px;
    display: inline-flex;
    flex: none;
    font-size: 0.72rem;
    font-weight: 700;
    gap: 0.35rem;
    padding: 0.38rem 0.7rem;
    white-space: nowrap;
}

.branch-card__status--active {
    background: rgba(29, 158, 117, 0.13);
    color: #6cbc96;
}

.branch-card__status--inactive {
    background: rgba(148, 163, 184, 0.12);
    color: #94a3b8;
}

.branch-card__status-dot {
    background: currentColor;
    border-radius: 999px;
    height: 0.42rem;
    width: 0.42rem;
}

.branch-card__info,
.branch-card__amenities,
.branch-card__actions {
    border-top: 1px solid color-mix(in srgb, var(--app-border) 78%, transparent);
}

.branch-card__info {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    padding: 0.75rem 1rem;
}

.branch-info-item {
    align-items: center;
    color: var(--app-text-muted);
    display: inline-flex;
    font-size: 0.78rem;
    gap: 0.4rem;
    text-decoration: none;
}

.branch-info-item svg {
    height: 0.82rem;
    width: 0.82rem;
}

.branch-card__stats {
    border-top: 1px solid color-mix(in srgb, var(--app-border) 78%, transparent);
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.branch-stat {
    border-right: 1px solid color-mix(in srgb, var(--app-border) 78%, transparent);
    padding: 0.65rem 0.45rem 0.75rem;
    text-align: center;
}

.branch-stat:last-child {
    border-right: none;
}

.branch-stat__value {
    color: var(--app-text);
    display: block;
    font-size: 1.15rem;
    font-weight: 800;
    line-height: 1;
}

.branch-stat__value--success {
    color: #6cbc96;
}

.branch-stat__value--muted {
    color: var(--app-text-muted);
}

.branch-stat__label {
    color: var(--app-text);
    display: block;
    font-size: 0.58rem;
    letter-spacing: 0.08em;
    margin-top: 0.35rem;
    text-transform: uppercase;
}

.branch-card__amenities {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    padding: 0.75rem 1rem;
    min-height: 3.2rem;
}

.branch-amenity {
    background: color-mix(in srgb, var(--app-panel-strong) 88%, transparent);
    border: 1px solid color-mix(in srgb, var(--app-border) 88%, transparent);
    border-radius: 999px;
    color: var(--app-text-muted);
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.28rem 0.58rem;
}

.branch-amenity--empty {
    background: transparent;
    border-style: dashed;
    color: var(--app-text-muted);
    opacity: 0.8;
}

.branch-card__actions {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.branch-action {
    align-items: center;
    background: transparent;
    border: none;
    border-right: 1px solid color-mix(in srgb, var(--app-border) 78%, transparent);
    color: var(--app-text-muted);
    cursor: pointer;
    display: inline-flex;
    font-size: 0.8rem;
    font-weight: 600;
    justify-content: center;
    min-height: 2.9rem;
    padding: 0.7rem;
    text-decoration: none;
    transition: background 140ms ease, color 140ms ease;
}

.branch-action:last-child {
    border-right: none;
}

.branch-action:hover {
    background: color-mix(in srgb, var(--app-border) 52%, transparent);
    color: var(--app-text);
}

.branch-action--danger:hover {
    background: rgba(226, 75, 74, 0.08);
    color: #ff8b89;
}

.branch-action--success:hover {
    background: rgba(29, 158, 117, 0.08);
    color: #6cbc96;
}

.branch-empty {
    align-items: center;
    background: var(--app-panel);
    border: 1px solid var(--app-border);
    border-radius: 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    padding: 4rem 1.5rem;
    text-align: center;
}

.branch-empty__icon {
    align-items: center;
    background: color-mix(in srgb, var(--app-panel-strong) 88%, transparent);
    border: 1px solid var(--app-border);
    border-radius: 999px;
    color: var(--app-text-muted);
    display: inline-flex;
    height: 4.75rem;
    justify-content: center;
    width: 4.75rem;
}

.branch-empty__icon svg {
    height: 2rem;
    width: 2rem;
}

.branch-empty__title {
    color: var(--app-text);
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0;
}

.branch-empty__text {
    color: var(--app-text-muted);
    margin: 0;
    max-width: 28rem;
}

.branch-modal-overlay {
    align-items: center;
    background: rgba(15, 23, 42, 0.58);
    backdrop-filter: blur(3px);
    display: flex;
    inset: 0;
    justify-content: center;
    padding: 1.25rem;
    position: fixed;
    z-index: 80;
}

.branch-modal {
    background: var(--app-panel-strong);
    border: 1px solid var(--app-border);
    border-radius: 1.5rem;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.32);
    max-width: 30rem;
    padding: 1.4rem;
    width: 100%;
}

.branch-modal__title {
    color: var(--app-text);
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0 0 0.5rem;
}

.branch-modal__text {
    color: var(--app-text-muted);
    margin: 0;
}

.branch-modal__field {
    margin-top: 1rem;
}

.branch-modal__field label {
    color: var(--app-text-muted);
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.45rem;
}

.branch-modal__select {
    background: var(--app-panel);
    border: 1px solid var(--app-border);
    border-radius: 0.9rem;
    color: var(--app-text);
    min-height: 2.9rem;
    padding: 0.75rem 0.9rem;
    width: 100%;
}

.branch-modal__error {
    color: #ff8b89;
    font-size: 0.82rem;
    margin: 0.65rem 0 0;
}

.branch-modal__actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    margin-top: 1.2rem;
}

.branch-modal__btn {
    border-radius: 0.9rem;
    cursor: pointer;
    font-size: 0.92rem;
    font-weight: 700;
    min-height: 2.9rem;
    padding: 0.7rem 1rem;
}

.branch-modal__btn--ghost {
    background: transparent;
    border: 1px solid var(--app-border);
    color: var(--app-text-muted);
}

.branch-modal__btn--danger {
    background: rgba(226, 75, 74, 0.14);
    border: 1px solid rgba(226, 75, 74, 0.3);
    color: #ff9e9c;
}

.branch-modal__btn:disabled {
    cursor: wait;
    opacity: 0.6;
}

@media (max-width: 900px) {
    .branch-card__stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .branch-stat:nth-child(2) {
        border-right: none;
    }

    .branch-stat:nth-child(-n + 2) {
        border-bottom: 1px solid color-mix(in srgb, var(--app-border) 78%, transparent);
    }
}

@media (max-width: 720px) {
    .branch-page {
        max-width: 100%;
    }

    .branch-toolbar {
        align-items: stretch;
        flex-direction: column;
        gap: 0.7rem;
    }

    .branch-grid {
        grid-template-columns: 1fr;
    }

    .branch-card__header {
        padding: 0.95rem;
    }

    .branch-card__topline {
        flex-direction: column;
    }

    .branch-card__name {
        font-size: 0.92rem;
    }

    .branch-card__primary {
        display: inline-flex;
        margin-left: 0;
        margin-top: 0.6rem;
    }

    .branch-card__info,
    .branch-card__amenities {
        padding-left: 0.95rem;
        padding-right: 0.95rem;
    }

    .branch-card__actions {
        grid-template-columns: 1fr;
    }

    .branch-action {
        border-right: none;
        border-top: 1px solid color-mix(in srgb, var(--app-border) 78%, transparent);
        min-height: 3.5rem;
    }

    .branch-action:first-child {
        border-top: none;
    }
}
</style>

