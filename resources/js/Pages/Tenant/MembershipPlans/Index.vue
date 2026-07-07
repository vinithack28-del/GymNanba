<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
    counts: {
        type: Object,
        default: () => ({}),
    },
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const page = usePage();
const archivePlanId = ref(null);
const viewMode = ref('card'); // 'card' or 'list'

const currentSearch = computed(() => {
    const url = new URL(page.url || '/plans', 'http://localhost');
    return url.searchParams.get('search') || '';
});

const currentStatus = computed(() => {
    const url = new URL(page.url || '/plans', 'http://localhost');
    return url.searchParams.get('status') || '';
});

const searchInput = ref(currentSearch.value);

const statusTabs = computed(() => ([
    { key: '', label: 'All', count: props.counts?.all || 0 },
    { key: 'active', label: 'Active', count: props.counts?.active || 0 },
    { key: 'inactive', label: 'Inactive', count: props.counts?.inactive || 0 },
    { key: 'archived', label: 'Archived', count: props.counts?.archived || 0 },
]));

const applySearch = () => {
    router.get('/plans', {
        ...(currentStatus.value ? { status: currentStatus.value } : {}),
        ...(searchInput.value.trim() ? { search: searchInput.value.trim() } : {}),
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const selectStatus = (status) => {
    router.get('/plans', {
        ...(status ? { status } : {}),
        ...(searchInput.value.trim() ? { search: searchInput.value.trim() } : {}),
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const archivePlan = (planId, confirm = false) => {
    router.post(`/plans/${planId}/archive`, confirm ? { confirm: 1 } : {}, {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.archive) {
                archivePlanId.value = planId;
            }
        },
    });
};

const duplicatePlan = (planId) => {
    router.post(`/plans/${planId}/duplicate`, {}, {
        preserveScroll: true,
    });
};

const formatPricePrecise = (paise) => `Rs. ${((Number(paise) || 0) / 100).toFixed(2)}`;
const totalPricePaise = (plan) => {
    if (typeof plan.total_price_paise !== 'undefined' && plan.total_price_paise !== null) {
        return Number(plan.total_price_paise);
    }

    const base = Number(plan.price_paise) || 0;
    const rate = plan.gst_applicable ? (Number(plan.gst_rate) || 0) : 0;
    return rate > 0 ? Math.round(base * (1 + (rate / 100))) : base;
};

const durationLabel = (plan) => {
    if (plan.session_limit) {
        const sessions = Number(plan.session_limit || 0);
        return `${sessions} ${sessions === 1 ? 'session' : 'sessions'}`;
    }

    if (plan.duration_label) return plan.duration_label;

    const value = Number(plan.duration_value || plan.duration_days || 0);
    const type = plan.duration_type === 'months' ? 'month' : 'day';
    return `${value} ${value === 1 ? type : `${type}s`}`;
};

const inclusionList = (plan) => {
    if (Array.isArray(plan.inclusions)) {
        return plan.inclusions.filter(Boolean);
    }

    if (typeof plan.inclusions === 'string') {
        return plan.inclusions.split(',').map((item) => item.trim()).filter(Boolean);
    }

    return [];
};

const effectiveArchivePlanId = computed(() => archivePlanId.value || page.props.errors?.archive_plan_id || null);

const statusStyle = (status) => {
    if (status === 'active') {
        return { background: 'rgba(29,158,117,0.12)', color: '#1D9E75' };
    }

    if (status === 'inactive') {
        return { background: 'rgba(136,135,128,0.12)', color: '#888780' };
    }

    return { background: 'rgba(226,75,74,0.10)', color: '#E24B4A' };
};

const toggleView = () => {
    viewMode.value = viewMode.value === 'card' ? 'list' : 'card';
};
</script>

<template>
    <AppLayout>
        <Head title="Gym Plans" />

        <div class="plan-page">
            <div class="plan-toolbar">
                <div class="plan-tabs">
                    <button
                        v-for="tab in statusTabs"
                        :key="tab.key || 'all'"
                        type="button"
                        class="plan-tab"
                        :class="{ 'plan-tab--active': currentStatus === tab.key }"
                        @click="selectStatus(tab.key)"
                    >
                        {{ tab.label }}
                        <span class="plan-tab__count">{{ tab.count }}</span>
                    </button>
                </div>

                <div class="plan-toolbar__actions">
                    <form class="plan-search" @submit.prevent="applySearch">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input v-model="searchInput" type="text" placeholder="Search plans...">
                    </form>

                    <button type="button" class="plan-view-toggle" @click="toggleView" :title="viewMode === 'card' ? 'Switch to list view' : 'Switch to card view'">
                        <svg v-if="viewMode === 'card'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <line x1="8" y1="6" x2="21" y2="6" />
                            <line x1="8" y1="12" x2="21" y2="12" />
                            <line x1="8" y1="18" x2="21" y2="18" />
                            <line x1="3" y1="6" x2="3.01" y2="6" />
                            <line x1="3" y1="12" x2="3.01" y2="12" />
                            <line x1="3" y1="18" x2="3.01" y2="18" />
                        </svg>
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="14" y="14" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                        </svg>
                    </button>

                    <Link v-if="canAdd" href="/plans/create" class="plan-create-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Create plan
                    </Link>
                </div>
            </div>

            <div v-if="page.props.errors?.archive" class="plan-archive-warning">
                <div class="plan-archive-warning__text">{{ page.props.errors.archive }}</div>
                <button
                    v-if="effectiveArchivePlanId"
                    type="button"
                    class="plan-archive-warning__btn"
                    @click="archivePlan(effectiveArchivePlanId, true)"
                >
                    Archive anyway
                </button>
            </div>

            <div v-if="!plans.length" class="plan-empty">
                <div class="plan-empty__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                        <rect x="5" y="3" width="14" height="18" rx="2" />
                        <path d="M9 7h6" />
                        <path d="M9 11h6" />
                        <path d="M9 15h4" />
                    </svg>
                </div>
                <p class="plan-empty__title">No plans found</p>
                <p class="plan-empty__text">Create your first membership plan to get started.</p>
                <Link v-if="canAdd" href="/plans/create" class="plan-create-btn">Create plan</Link>
            </div>

            <div v-else-if="viewMode === 'card'" class="plan-grid">
                <article
                    v-for="plan in plans"
                    :key="plan.id"
                    class="plan-card"
                    :class="{ 'plan-card--archived': plan.status === 'archived' }"
                >
                    <div class="plan-card__header">
                        <div class="plan-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <rect x="5" y="3" width="14" height="18" rx="2" />
                                <path d="M9 7h6" />
                                <path d="M9 11h6" />
                                <path d="M9 15h4" />
                            </svg>
                        </div>

                        <div class="plan-card__main">
                            <div class="plan-card__topline">
                                <h2 class="plan-card__name">{{ plan.name }}</h2>
                                <span class="plan-card__status" :style="statusStyle(plan.status)">
                                    {{ plan.status.charAt(0).toUpperCase() + plan.status.slice(1) }}
                                </span>
                            </div>

                            <span class="plan-card__duration">
                                {{ durationLabel(plan) }}
                            </span>
                        </div>
                    </div>

                    <div class="plan-card__price">
                        <span class="plan-card__amount">{{ formatPricePrecise(plan.price_paise) }}</span>
                        <span v-if="plan.gst_applicable && plan.gst_rate > 0" class="plan-card__gst">
                            +{{ Number(plan.gst_rate).toFixed(0) }}% GST -> {{ formatPricePrecise(totalPricePaise(plan)) }} total
                        </span>
                    </div>

                    <p v-if="plan.description" class="plan-card__description">{{ plan.description }}</p>

                    <div v-if="inclusionList(plan).length" class="plan-card__inclusions">
                        <span
                            v-for="inclusion in inclusionList(plan)"
                            :key="`${plan.id}-${inclusion}`"
                            class="plan-card__inclusion"
                        >
                            {{ inclusion }}
                        </span>
                    </div>

                    <div class="plan-card__stats">
                        <div class="plan-stat">
                            <span class="plan-stat__value plan-stat__value--success">{{ plan.active_members_count || 0 }}</span>
                            <span class="plan-stat__label">Active</span>
                        </div>
                        <div class="plan-stat">
                            <span class="plan-stat__value">{{ plan.total_members_count || 0 }}</span>
                            <span class="plan-stat__label">Total Members</span>
                        </div>
                    </div>

                    <div class="plan-card__freeze" :class="{ 'plan-card__freeze--off': !plan.allow_freeze }">
                        <svg v-if="plan.allow_freeze" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 2v20" />
                            <path d="M2 12h20" />
                            <path d="M4.93 4.93l14.14 14.14" />
                            <path d="M19.07 4.93 4.93 19.07" />
                        </svg>
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                        <span v-if="plan.allow_freeze">Freeze allowed Ã‚- {{ plan.max_freeze_days }}d/yr</span>
                        <span v-else>Freeze not allowed</span>
                    </div>

                    <div class="plan-card__actions">
                        <Link v-if="plan.status !== 'archived' && canEdit" :href="`/plans/${plan.id}/edit`" class="plan-action">
                            Edit
                        </Link>
                        <button v-else type="button" class="plan-action plan-action--disabled" disabled>
                            Edit
                        </button>

                        <button type="button" class="plan-action" @click="duplicatePlan(plan.id)">
                            Duplicate
                        </button>

                        <button
                            v-if="plan.status !== 'archived' && canDelete"
                            type="button"
                            class="plan-action plan-action--warn"
                            @click="archivePlan(plan.id)"
                        >
                            Archive
                        </button>
                        <button v-else type="button" class="plan-action plan-action--disabled" disabled>
                            Archive
                        </button>
                    </div>
                </article>
            </div>

            <div v-else class="plan-list">
                <div class="plan-list__header">
                    <div class="plan-list__cell plan-list__cell--name">Plan Name</div>
                    <div class="plan-list__cell plan-list__cell--duration">Duration</div>
                    <div class="plan-list__cell plan-list__cell--price">Price</div>
                    <div class="plan-list__cell plan-list__cell--status">Status</div>
                    <div class="plan-list__cell plan-list__cell--members">Members</div>
                    <div class="plan-list__cell plan-list__cell--freeze">Freeze</div>
                    <div class="plan-list__cell plan-list__cell--actions">Actions</div>
                </div>
                <article
                    v-for="plan in plans"
                    :key="plan.id"
                    class="plan-list__row"
                    :class="{ 'plan-list__row--archived': plan.status === 'archived' }"
                >
                    <div class="plan-list__cell plan-list__cell--name">
                        <div class="plan-list__name-wrapper">
                            <div class="plan-list__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <rect x="5" y="3" width="14" height="18" rx="2" />
                                    <path d="M9 7h6" />
                                    <path d="M9 11h6" />
                                    <path d="M9 15h4" />
                                </svg>
                            </div>
                            <div>
                                <div class="plan-list__name">{{ plan.name }}</div>
                                <p v-if="plan.description" class="plan-list__description">{{ plan.description }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="plan-list__cell plan-list__cell--duration">
                        <span class="plan-list__duration">{{ durationLabel(plan) }}</span>
                    </div>
                    <div class="plan-list__cell plan-list__cell--price">
                        <div class="plan-list__price-wrapper">
                            <span class="plan-list__amount">{{ formatPricePrecise(plan.price_paise) }}</span>
                            <span v-if="plan.gst_applicable && plan.gst_rate > 0" class="plan-list__gst">
                                +{{ Number(plan.gst_rate).toFixed(0) }}% GST
                            </span>
                        </div>
                    </div>
                    <div class="plan-list__cell plan-list__cell--status">
                        <span class="plan-list__status" :style="statusStyle(plan.status)">
                            {{ plan.status.charAt(0).toUpperCase() + plan.status.slice(1) }}
                        </span>
                    </div>
                    <div class="plan-list__cell plan-list__cell--members">
                        <div class="plan-list__members-wrapper">
                            <span class="plan-list__member-count plan-list__member-count--active">{{ plan.active_members_count || 0 }}</span>
                            <span class="plan-list__member-count">{{ plan.total_members_count || 0 }}</span>
                        </div>
                    </div>
                    <div class="plan-list__cell plan-list__cell--freeze">
                        <span class="plan-list__freeze" :class="{ 'plan-list__freeze--off': !plan.allow_freeze }">
                            {{ plan.allow_freeze ? `${plan.max_freeze_days}d/yr` : 'Not allowed' }}
                        </span>
                    </div>
                    <div class="plan-list__cell plan-list__cell--actions">
                        <div class="plan-list__actions">
                            <Link v-if="plan.status !== 'archived' && canEdit" :href="`/plans/${plan.id}/edit`" class="locker-icon-btn locker-icon-btn-edit" title="Edit plan" aria-label="Edit plan">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                            </Link>
                            <button v-else type="button" class="locker-icon-btn locker-icon-btn-edit" disabled title="Edit plan" aria-label="Edit plan">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                            </button>

                            <button type="button" class="locker-icon-btn" @click="duplicatePlan(plan.id)" title="Duplicate plan" aria-label="Duplicate plan">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                                </svg>
                            </button>

                            <button
                                v-if="plan.status !== 'archived' && canDelete"
                                type="button"
                                class="locker-icon-btn text-red-400"
                                @click="archivePlan(plan.id)"
                                title="Archive plan"
                                aria-label="Archive plan"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                    <path d="M10 11v6" />
                                    <path d="M14 11v6" />
                                </svg>
                            </button>
                            <button v-else type="button" class="locker-icon-btn text-red-400" disabled title="Archive plan" aria-label="Archive plan">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                    <path d="M10 11v6" />
                                    <path d="M14 11v6" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.plan-page {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.plan-toolbar {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
    justify-content: space-between;
}

.plan-tabs {
    align-items: center;
    background: var(--app-panel-strong);
    border: 1px solid var(--app-border);
    border-radius: 1rem;
    display: inline-flex;
    flex-wrap: wrap;
    gap: 0.15rem;
    padding: 0.2rem;
}

.plan-tab {
    align-items: center;
    background: transparent;
    border: none;
    border-radius: 0.8rem;
    color: var(--app-text-muted);
    cursor: pointer;
    display: inline-flex;
    font-size: 0.86rem;
    font-weight: 600;
    gap: 0.45rem;
    padding: 0.45rem 0.85rem;
}

.plan-tab--active {
    background: var(--app-panel);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    color: var(--app-text);
}

.plan-tab__count {
    background: color-mix(in srgb, var(--app-panel) 90%, transparent);
    border-radius: 999px;
    font-size: 0.7rem;
    min-width: 1.15rem;
    padding: 0.05rem 0.35rem;
    text-align: center;
}

.plan-toolbar__actions {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.plan-search {
    align-items: center;
    background: var(--app-panel-strong);
    border: 1px solid var(--app-border);
    border-radius: 0.9rem;
    display: flex;
    gap: 0.45rem;
    padding: 0.6rem 0.85rem;
}

.plan-search svg {
    color: var(--app-text-muted);
    height: 0.95rem;
    width: 0.95rem;
}

.plan-search input {
    background: transparent;
    border: none;
    color: var(--app-text);
    font-size: 0.88rem;
    min-width: 220px;
    outline: none;
}

.plan-create-btn {
    align-items: center;
    background: #de7429;
    border-radius: 0.9rem;
    color: #171717;
    display: inline-flex;
    font-size: 0.92rem;
    font-weight: 700;
    gap: 0.45rem;
    min-height: 2.8rem;
    padding: 0.65rem 1rem;
    text-decoration: none;
    white-space: nowrap;
}

.plan-create-btn svg {
    height: 0.9rem;
    width: 0.9rem;
}

.plan-view-toggle {
    align-items: center;
    background: var(--app-panel-strong);
    border: 1px solid var(--app-border);
    border-radius: 0.9rem;
    color: var(--app-text-muted);
    cursor: pointer;
    display: inline-flex;
    height: 2.8rem;
    justify-content: center;
    min-width: 2.8rem;
    padding: 0;
    transition: all 0.2s ease;
}

.plan-view-toggle:hover {
    background: var(--app-panel);
    color: var(--app-text);
}

.plan-view-toggle svg {
    height: 1rem;
    width: 1rem;
}

.plan-archive-warning {
    align-items: center;
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.25);
    border-radius: 1rem;
    display: flex;
    gap: 0.75rem;
    justify-content: space-between;
    padding: 0.8rem 1rem;
}

.plan-archive-warning__text {
    color: #ef4444;
    font-size: 0.88rem;
    font-weight: 600;
}

.plan-archive-warning__btn {
    background: rgba(226, 75, 74, 0.12);
    border: 1px solid rgba(226, 75, 74, 0.28);
    border-radius: 0.7rem;
    color: #e24b4a;
    cursor: pointer;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.45rem 0.7rem;
    white-space: nowrap;
}

.plan-grid {
    display: grid;
    gap: 1.1rem;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    align-items: start;
}

@media (min-width: 900px) {
    .plan-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

.plan-card {
    background:
        radial-gradient(circle at top right, rgba(229, 155, 114, 0.06), transparent 35%),
        var(--app-panel);
    border: 1px solid color-mix(in srgb, var(--app-border) 82%, transparent);
    border-radius: 1.55rem;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    max-width: none;
    min-height: 282px;
    width: 100%;
}

.plan-card--archived {
    opacity: 0.68;
}

.plan-card__header {
    display: flex;
    gap: 0.85rem;
    padding: 0.95rem 1rem 0.45rem;
}

.plan-card__icon {
    align-items: center;
    background: color-mix(in srgb, #f8e1d5 45%, transparent);
    border: 1px solid color-mix(in srgb, #d9a789 68%, transparent);
    border-radius: 0.85rem;
    color: #dd7f47;
    display: inline-flex;
    flex: none;
    height: 2.25rem;
    justify-content: center;
    width: 2.25rem;
}

.plan-card__icon svg {
    height: 0.95rem;
    width: 0.95rem;
}

.plan-card__main {
    flex: 1;
    min-width: 0;
}

.plan-card__topline {
    align-items: flex-start;
    display: flex;
    gap: 0.75rem;
    justify-content: space-between;
}

.plan-card__name {
    color: var(--app-text);
    font-size: 0.92rem;
    font-weight: 700;
    line-height: 1.3;
    margin: 0;
}

.plan-card__status {
    border-radius: 999px;
    font-size: 0.64rem;
    font-weight: 700;
    padding: 0.18rem 0.48rem;
    white-space: nowrap;
}

.plan-card__duration {
    background: color-mix(in srgb, var(--app-panel-strong) 90%, transparent);
    border: 1px solid var(--app-border);
    border-radius: 999px;
    color: var(--app-text-muted);
    display: inline-block;
    font-size: 0.66rem;
    font-weight: 700;
    margin-top: 0.3rem;
    padding: 0.12rem 0.42rem;
}

.plan-card__price {
    align-items: baseline;
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    padding: 0.35rem 1rem 0;
}

.plan-card__amount {
    color: var(--app-text);
    font-size: 1.02rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.plan-card__gst {
    color: var(--app-text-muted);
    font-size: 0.68rem;
}

.plan-card__description {
    color: var(--app-text-muted);
    font-size: 0.76rem;
    line-height: 1.45;
    margin: 0;
    padding: 0.42rem 1rem 0;
}

.plan-card__inclusions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    padding: 0.48rem 1rem 0;
}

.plan-card__inclusion {
    background: color-mix(in srgb, var(--app-panel-strong) 82%, transparent);
    border: 1px solid color-mix(in srgb, var(--app-border) 78%, transparent);
    border-radius: 999px;
    color: var(--app-text-muted);
    font-size: 0.66rem;
    font-weight: 600;
    line-height: 1.1;
    max-width: 100%;
    overflow: hidden;
    padding: 0.18rem 0.48rem;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.plan-card__stats {
    border-top: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent);
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-top: 0.75rem;
}

.plan-stat {
    border-right: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent);
    padding: 0.62rem 0.55rem;
    text-align: center;
}

.plan-stat:last-child {
    border-right: none;
}

.plan-stat__value {
    color: var(--app-text);
    display: block;
    font-size: 0.98rem;
    font-weight: 800;
    line-height: 1;
}

.plan-stat__value--success {
    color: #1d9e75;
}

.plan-stat__label {
    color: var(--app-text-muted);
    display: block;
    font-size: 0.58rem;
    letter-spacing: 0.05em;
    margin-top: 0.28rem;
    text-transform: uppercase;
}

.plan-card__freeze {
    align-items: center;
    color: #378add;
    display: flex;
    font-size: 0.7rem;
    font-weight: 600;
    gap: 0.35rem;
    min-height: 1.85rem;
    padding: 0.45rem 1rem 0;
}

.plan-card__freeze svg {
    height: 0.82rem;
    width: 0.82rem;
}

.plan-card__freeze--off {
    color: #e24b4a;
}

.plan-card__actions {
    border-top: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent);
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-top: auto;
}

.plan-action {
    align-items: center;
    background: transparent;
    border: none;
    border-right: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent);
    color: #516a8d;
    cursor: pointer;
    display: inline-flex;
    font-size: 0.78rem;
    font-weight: 700;
    justify-content: center;
    min-height: 2.45rem;
    padding: 0.62rem;
    text-decoration: none;
}

.plan-action:last-child {
    border-right: none;
}

.plan-action--warn {
    color: #516a8d;
}

.plan-action--disabled {
    cursor: default;
    opacity: 0.45;
}

.plan-empty {
    align-items: center;
    background: var(--app-panel);
    border: 1px solid var(--app-border);
    border-radius: 1.8rem;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    padding: 4rem 1.5rem;
    text-align: center;
}

.plan-empty__icon {
    align-items: center;
    background: color-mix(in srgb, var(--app-panel-strong) 88%, transparent);
    border: 1px solid var(--app-border);
    border-radius: 999px;
    color: var(--app-text-muted);
    display: inline-flex;
    height: 4.25rem;
    justify-content: center;
    width: 4.25rem;
}

.plan-empty__icon svg {
    height: 1.85rem;
    width: 1.85rem;
}

.plan-empty__title {
    color: var(--app-text);
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
}

.plan-empty__text {
    color: var(--app-text-muted);
    margin: 0;
}

@media (max-width: 720px) {
    .plan-toolbar,
    .plan-toolbar__actions {
        align-items: stretch;
        flex-direction: column;
    }

    .plan-search input {
        min-width: 0;
        width: 100%;
    }
}

/* List View Styles */
.plan-list {
    background: var(--app-panel);
    border: 1px solid var(--app-border);
    border-radius: 1.55rem;
    overflow: hidden;
}

.plan-list__header {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 0.8fr 0.8fr 0.8fr 1.2fr;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    background: var(--app-panel-strong);
    border-bottom: 1px solid var(--app-border);
}

.plan-list__cell {
    align-items: center;
    display: flex;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--app-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.plan-list__row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 0.8fr 0.8fr 0.8fr 1.2fr;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent);
    transition: background 0.2s ease;
}

.plan-list__row:last-child {
    border-bottom: none;
}

.plan-list__row:hover {
    background: color-mix(in srgb, var(--app-panel-strong) 50%, transparent);
}

.plan-list__row--archived {
    opacity: 0.68;
}

.plan-list__cell--name {
    align-items: flex-start;
}

.plan-list__name-wrapper {
    align-items: center;
    display: flex;
    gap: 0.75rem;
}

.plan-list__icon {
    align-items: center;
    background: color-mix(in srgb, #f8e1d5 45%, transparent);
    border: 1px solid color-mix(in srgb, #d9a789 68%, transparent);
    border-radius: 0.7rem;
    color: #dd7f47;
    display: inline-flex;
    flex: none;
    height: 1.85rem;
    justify-content: center;
    width: 1.85rem;
}

.plan-list__icon svg {
    height: 0.8rem;
    width: 0.8rem;
}

.plan-list__name {
    color: var(--app-text);
    font-size: 0.92rem;
    font-weight: 700;
    line-height: 1.3;
    text-transform: none;
}

.plan-list__description {
    color: var(--app-text-muted);
    font-size: 0.76rem;
    line-height: 1.4;
    margin: 0.15rem 0 0 0;
    text-transform: none;
}

.plan-list__duration {
    background: color-mix(in srgb, var(--app-panel-strong) 90%, transparent);
    border: 1px solid var(--app-border);
    border-radius: 999px;
    color: var(--app-text-muted);
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.15rem 0.5rem;
}

.plan-list__price-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.plan-list__amount {
    color: var(--app-text);
    font-size: 0.88rem;
    font-weight: 800;
}

.plan-list__gst {
    color: var(--app-text-muted);
    font-size: 0.65rem;
}

.plan-list__status {
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    white-space: nowrap;
}

.plan-list__members-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.plan-list__member-count {
    color: var(--app-text);
    font-size: 0.78rem;
    font-weight: 700;
}

.plan-list__member-count--active {
    color: #1d9e75;
}

.plan-list__freeze {
    color: #378add;
    font-size: 0.72rem;
    font-weight: 600;
}

.plan-list__freeze--off {
    color: #e24b4a;
}

.plan-list__actions {
    display: flex;
    gap: 0.35rem;
}

.plan-list__action {
    align-items: center;
    background: transparent;
    border: 1px solid var(--app-border);
    border-radius: 0.6rem;
    color: #516a8d;
    cursor: pointer;
    display: inline-flex;
    justify-content: center;
    min-height: 2rem;
    min-width: 2rem;
    padding: 0.4rem;
    text-decoration: none;
    transition: all 0.2s ease;
}

.plan-list__action svg {
    height: 0.95rem;
    width: 0.95rem;
}

.plan-list__action:hover {
    background: var(--app-panel-strong);
    border-color: var(--app-border);
}

.plan-list__action--warn {
    color: #516a8d;
}

.plan-list__action--disabled {
    cursor: default;
    opacity: 0.45;
}

/* Icon button styles matching Equipment view */
.locker-icon-btn {
    align-items: center;
    background: transparent;
    border: 1px solid color-mix(in srgb, var(--app-border) 70%, transparent);
    border-radius: 0.5rem;
    color: var(--app-text-muted);
    cursor: pointer;
    display: inline-flex;
    height: 2rem;
    justify-content: center;
    padding: 0;
    transition: all 0.15s ease;
    width: 2rem;
}

.locker-icon-btn svg {
    height: 0.9rem;
    width: 0.9rem;
}

.locker-icon-btn:hover:not(:disabled) {
    background: var(--app-panel-strong);
    border-color: var(--app-border);
    color: var(--app-text);
}

.locker-icon-btn:disabled {
    cursor: not-allowed;
    opacity: 0.4;
}

.locker-icon-btn-edit {
    color: #378add;
}

.locker-icon-btn-edit:hover:not(:disabled) {
    background: rgba(55, 138, 221, 0.1);
    border-color: rgba(55, 138, 221, 0.3);
    color: #378add;
}

.locker-icon-btn.text-red-400 {
    color: #ef4444;
}

.locker-icon-btn.text-red-400:hover:not(:disabled) {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

@media (max-width: 1024px) {
    .plan-list__header,
    .plan-list__row {
        grid-template-columns: 1.5fr 0.8fr 0.8fr 0.7fr 0.7fr 0.7fr 1fr;
        gap: 0.5rem;
        padding: 0.7rem 0.75rem;
    }

    .plan-list__cell {
        font-size: 0.68rem;
    }
}

@media (max-width: 768px) {
    .plan-list__header {
        display: none;
    }

    .plan-list__row {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        padding: 1rem;
    }

    .plan-list__cell {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: none;
        letter-spacing: normal;
    }

    .plan-list__cell--name {
        width: 100%;
    }

    .plan-list__actions {
        width: 100%;
        justify-content: flex-start;
    }

    .plan-list__action {
        flex: 1;
    }
}
</style>

