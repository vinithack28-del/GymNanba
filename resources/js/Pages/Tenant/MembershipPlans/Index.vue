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

            <div v-else class="plan-grid">
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
</style>

