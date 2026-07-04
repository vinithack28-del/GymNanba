<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { computed, reactive, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    branch: Object,
    states: {
        type: Array,
        default: () => [],
    },
    amenityOpts: {
        type: Object,
        default: () => ({}),
    },
});

const editing = !!props.branch;
const pageTitle = editing ? 'Edit Branch' : 'Add Branch';

const days = {
    mon: 'Mon',
    tue: 'Tue',
    wed: 'Wed',
    thu: 'Thu',
    fri: 'Fri',
    sat: 'Sat',
    sun: 'Sun',
};

const amenityIcons = {
    pool: 'ðŸŠ',
    steam: 'ðŸ’¨',
    parking: 'ðŸ…¿',
    locker: 'ðŸ”’',
    cafeteria: 'â˜•',
    ac: 'â„',
    wifi: 'ðŸ“¶',
};

const defaultHours = {
    mon: { open: '06:00', close: '22:00', closed: false },
    tue: { open: '06:00', close: '22:00', closed: false },
    wed: { open: '06:00', close: '22:00', closed: false },
    thu: { open: '06:00', close: '22:00', closed: false },
    fri: { open: '06:00', close: '22:00', closed: false },
    sat: { open: '07:00', close: '20:00', closed: false },
    sun: { open: '08:00', close: '14:00', closed: false },
};

const savedHours = Object.fromEntries(
    Object.entries(defaultHours).map(([day, fallback]) => [
        day,
        {
            open: props.branch?.operating_hours?.[day]?.open || fallback.open,
            close: props.branch?.operating_hours?.[day]?.close || fallback.close,
            closed: Boolean(props.branch?.operating_hours?.[day]?.closed ?? fallback.closed),
        },
    ]),
);

const form = useForm({
    name: props.branch?.name || '',
    phone: props.branch?.phone || '',
    email: props.branch?.email || '',
    manager_name: props.branch?.manager_name || '',
    gst_number: props.branch?.gst_number || '',
    status: props.branch?.status || 'active',
    address1: props.branch?.address1 || '',
    address2: props.branch?.address2 || '',
    city: props.branch?.city || '',
    pin: props.branch?.pin || '',
    state: props.branch?.state || '',
    amenities: Array.isArray(props.branch?.amenities) ? [...props.branch.amenities] : [],
    operating_hours: savedHours,
});

const steps = [
    { number: 1, title: 'Basic Info' },
    { number: 2, title: 'Address' },
    { number: 3, title: 'Amenities' },
    { number: 4, title: 'Operating Hours' },
];

const currentStep = ref(1);
const completedSteps = ref(editing ? [1, 2, 3, 4] : []);
const clientErrors = reactive({});

const currentStepLabel = computed(() => `Step ${currentStep.value} of ${steps.length}`);
const isLastStep = computed(() => currentStep.value === steps.length);

const isCompleted = (stepNumber) => completedSteps.value.includes(stepNumber);
const canOpenStep = (stepNumber) => editing || stepNumber === currentStep.value || isCompleted(stepNumber);

const setClientError = (field, message) => {
    clientErrors[field] = message;
};

const clearClientError = (field) => {
    delete clientErrors[field];
};

const errorFor = (field) => form.errors[field] || clientErrors[field] || '';

const validateBasicInfo = () => {
    let ok = true;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    clearClientError('name');
    clearClientError('phone');
    clearClientError('email');
    clearClientError('manager_name');
    clearClientError('gst_number');
    clearClientError('status');

    if (form.name.trim().length < 2) {
        setClientError('name', 'Branch name must be at least 2 characters.');
        ok = false;
    }

    if (!form.phone.trim()) {
        setClientError('phone', 'Phone is required.');
        ok = false;
    } else if (form.phone.trim().length > 20) {
        setClientError('phone', 'Phone must be 20 characters or fewer.');
        ok = false;
    }

    if (form.email && !emailRegex.test(form.email)) {
        setClientError('email', 'Enter a valid email address.');
        ok = false;
    }

    if (form.manager_name && form.manager_name.trim().length > 100) {
        setClientError('manager_name', 'Manager name must be 100 characters or fewer.');
        ok = false;
    }

    if (form.gst_number && form.gst_number.trim().length > 15) {
        setClientError('gst_number', 'GST number must be 15 characters or fewer.');
        ok = false;
    }

    if (!['active', 'inactive'].includes(form.status)) {
        setClientError('status', 'Select a valid status.');
        ok = false;
    }

    return ok;
};

const validateAddress = () => {
    let ok = true;

    clearClientError('address1');
    clearClientError('address2');
    clearClientError('city');
    clearClientError('pin');
    clearClientError('state');

    if (form.address1.trim().length < 5) {
        setClientError('address1', 'Address line 1 must be at least 5 characters.');
        ok = false;
    }

    if (form.address2 && form.address2.trim().length > 100) {
        setClientError('address2', 'Address line 2 must be 100 characters or fewer.');
        ok = false;
    }

    if (form.city.trim().length < 2) {
        setClientError('city', 'City must be at least 2 characters.');
        ok = false;
    }

    if (!/^\d{6}$/.test(form.pin)) {
        setClientError('pin', 'PIN code must be 6 digits.');
        ok = false;
    }

    if (!form.state) {
        setClientError('state', 'State is required.');
        ok = false;
    }

    return ok;
};

const validateAmenities = () => {
    clearClientError('amenities');
    return true;
};

const validateOperatingHours = () => {
    let ok = true;

    clearClientError('operating_hours');

    Object.entries(form.operating_hours).forEach(([day, hours]) => {
        if (hours.closed) {
            return;
        }

        if (!hours.open || !hours.close) {
            setClientError('operating_hours', `Set both open and close times for ${days[day]}.`);
            ok = false;
            return;
        }

        if (hours.open >= hours.close) {
            setClientError('operating_hours', `${days[day]} closing time must be later than opening time.`);
            ok = false;
        }
    });

    return ok;
};

const validateStep = (stepNumber) => {
    if (stepNumber === 1) return validateBasicInfo();
    if (stepNumber === 2) return validateAddress();
    if (stepNumber === 3) return validateAmenities();
    if (stepNumber === 4) return validateOperatingHours();

    return true;
};

const goToStep = (stepNumber) => {
    if (!canOpenStep(stepNumber)) return;
    currentStep.value = stepNumber;
};

const goNext = () => {
    if (!validateStep(currentStep.value)) return;

    if (!isCompleted(currentStep.value)) {
        completedSteps.value.push(currentStep.value);
    }

    if (currentStep.value < steps.length) {
        currentStep.value += 1;
    }
};

const goBack = () => {
    if (currentStep.value > 1) {
        currentStep.value -= 1;
    }
};

const submit = () => {
    if (!validateStep(currentStep.value)) return;

    if (editing) {
        form.put(`/branches/${props.branch.id}`);
    } else {
        form.post('/branches');
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="pageTitle" />

        <div class="branch-form-page">
            <div class="branch-form-header">
                <div>
                    <p class="branch-form-eyebrow">Branches</p>
                    <h1 class="branch-form-title">{{ pageTitle }}</h1>
                    <p class="branch-form-subtitle">{{ editing ? `Update details for ${branch.name}.` : 'Set up a new location for your gym.' }}</p>
                </div>
                <Link href="/branches" class="branch-form-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5" />
                        <path d="M12 5 5 12l7 7" />
                    </svg>
                    Back to Branches
                </Link>
            </div>

            <form @submit.prevent="submit" class="branch-wizard">
                <div class="branch-wizard__steps">
                    <button
                        v-for="step in steps"
                        :key="step.number"
                        type="button"
                        class="branch-step"
                        :class="{
                            'is-active': currentStep === step.number,
                            'is-complete': isCompleted(step.number) && currentStep !== step.number,
                            'is-locked': !canOpenStep(step.number),
                        }"
                        @click="goToStep(step.number)"
                    >
                        <span class="branch-step__number">{{ step.number }}</span>
                        <span class="branch-step__title">{{ step.title }}</span>
                    </button>
                </div>

                <div class="branch-wizard__body">
                    <section v-show="currentStep === 1" class="branch-step-panel">
                        <div class="branch-grid-form">
                            <div class="branch-field branch-field--full">
                                <label>Branch Name <span>*</span></label>
                                <input v-model="form.name" type="text" placeholder="e.g. OMR Branch">
                                <p v-if="errorFor('name')" class="branch-field-error">{{ errorFor('name') }}</p>
                            </div>

                            <div class="branch-field">
                                <label>Phone <span>*</span></label>
                                <input v-model="form.phone" type="text" placeholder="+91 44 2200 0000">
                                <p v-if="errorFor('phone')" class="branch-field-error">{{ errorFor('phone') }}</p>
                            </div>

                            <div class="branch-field">
                                <label>Email <em>(optional)</em></label>
                                <input v-model="form.email" type="email" placeholder="branch@yourgym.in">
                                <p v-if="errorFor('email')" class="branch-field-error">{{ errorFor('email') }}</p>
                            </div>

                            <div class="branch-field">
                                <label>Branch Manager <em>(optional)</em></label>
                                <input v-model="form.manager_name" type="text" placeholder="Manager name">
                                <p v-if="errorFor('manager_name')" class="branch-field-error">{{ errorFor('manager_name') }}</p>
                            </div>

                            <div class="branch-field">
                                <label>GST Number <em>(optional)</em></label>
                                <input v-model="form.gst_number" type="text" placeholder="15-char GSTIN">
                                <p v-if="errorFor('gst_number')" class="branch-field-error">{{ errorFor('gst_number') }}</p>
                            </div>

                            <div class="branch-field branch-field--full">
                                <label>Status <span>*</span></label>
                                <div class="branch-radio-group">
                                    <label class="branch-radio" :class="{ 'is-selected': form.status === 'active' }">
                                        <input v-model="form.status" type="radio" value="active">
                                        <span>Active</span>
                                    </label>
                                    <label class="branch-radio" :class="{ 'is-selected': form.status === 'inactive' }">
                                        <input v-model="form.status" type="radio" value="inactive">
                                        <span>Inactive</span>
                                    </label>
                                </div>
                                <p v-if="errorFor('status')" class="branch-field-error">{{ errorFor('status') }}</p>
                            </div>
                        </div>
                    </section>

                    <section v-show="currentStep === 2" class="branch-step-panel">
                        <div class="branch-grid-form">
                            <div class="branch-field branch-field--full">
                                <label>Address Line 1 <span>*</span></label>
                                <input v-model="form.address1" type="text" placeholder="Street, area, landmark">
                                <p v-if="errorFor('address1')" class="branch-field-error">{{ errorFor('address1') }}</p>
                            </div>

                            <div class="branch-field branch-field--full">
                                <label>Address Line 2 <em>(optional)</em></label>
                                <input v-model="form.address2" type="text" placeholder="Floor, building, near landmark">
                                <p v-if="errorFor('address2')" class="branch-field-error">{{ errorFor('address2') }}</p>
                            </div>

                            <div class="branch-field">
                                <label>City <span>*</span></label>
                                <input v-model="form.city" type="text" placeholder="City">
                                <p v-if="errorFor('city')" class="branch-field-error">{{ errorFor('city') }}</p>
                            </div>

                            <div class="branch-field">
                                <label>PIN Code <span>*</span></label>
                                <input v-model="form.pin" type="text" placeholder="6 digits" maxlength="6">
                                <p v-if="errorFor('pin')" class="branch-field-error">{{ errorFor('pin') }}</p>
                            </div>

                            <div class="branch-field branch-field--full">
                                <label>State <span>*</span></label>
                                <select v-model="form.state">
                                    <option value="">Select state...</option>
                                    <option v-for="state in states" :key="state" :value="state">{{ state }}</option>
                                </select>
                                <p v-if="errorFor('state')" class="branch-field-error">{{ errorFor('state') }}</p>
                            </div>
                        </div>
                    </section>

                    <section v-show="currentStep === 3" class="branch-step-panel">
                        <div class="branch-amenities-grid">
                            <label
                                v-for="(label, key) in amenityOpts"
                                :key="key"
                                class="branch-amenity-pill"
                                :class="{ 'is-selected': form.amenities.includes(key) }"
                            >
                                <input v-model="form.amenities" type="checkbox" :value="key">
                                <span class="branch-amenity-pill__icon">{{ amenityIcons[key] || 'âœ“' }}</span>
                                <span>{{ label }}</span>
                            </label>
                        </div>
                        <p v-if="errorFor('amenities')" class="branch-field-error">{{ errorFor('amenities') }}</p>
                    </section>

                    <section v-show="currentStep === 4" class="branch-step-panel">
                        <div class="branch-hours-list">
                            <div v-for="(label, key) in days" :key="key" class="branch-hours-row">
                                <span class="branch-hours-row__day">{{ label }}</span>

                                <label class="branch-hours-row__closed">
                                    <input v-model="form.operating_hours[key].closed" type="checkbox">
                                    <span>Closed</span>
                                </label>

                                <div v-if="!form.operating_hours[key].closed" class="branch-hours-row__times">
                                    <input v-model="form.operating_hours[key].open" type="time">
                                    <span>to</span>
                                    <input v-model="form.operating_hours[key].close" type="time">
                                </div>

                                <span v-else class="branch-hours-row__off">Day off</span>
                            </div>
                        </div>
                        <p v-if="errorFor('operating_hours')" class="branch-field-error">{{ errorFor('operating_hours') }}</p>
                    </section>
                </div>

                <div class="branch-wizard__footer">
                    <div>
                        <Link v-if="currentStep === 1" href="/branches" class="branch-footer-btn branch-footer-btn--ghost">Cancel</Link>
                        <button v-else type="button" class="branch-footer-btn branch-footer-btn--ghost" @click="goBack">Back</button>
                    </div>

                    <div class="branch-wizard__footer-right">
                        <span class="branch-wizard__footer-label">{{ currentStepLabel }}</span>
                        <button
                            v-if="!isLastStep"
                            type="button"
                            class="branch-footer-btn branch-footer-btn--primary"
                            @click="goNext"
                        >
                            Next
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M9 18l6-6-6-6" />
                            </svg>
                        </button>
                        <button
                            v-else
                            type="submit"
                            class="branch-footer-btn branch-footer-btn--primary"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Saving...' : (editing ? 'Save Changes' : 'Create Branch') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.branch-form-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.branch-form-header {
    align-items: flex-start;
    display: flex;
    gap: 1rem;
    justify-content: space-between;
}

.branch-form-eyebrow {
    color: #e59b72;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.22em;
    margin: 0;
    text-transform: uppercase;
}

.branch-form-title {
    color: var(--app-text);
    font-size: clamp(2rem, 2.8vw, 2.5rem);
    font-weight: 700;
    margin: 0.35rem 0 0;
}

.branch-form-subtitle {
    color: var(--app-text-muted);
    margin: 0.45rem 0 0;
}

.branch-form-back {
    align-items: center;
    background: color-mix(in srgb, var(--app-panel-strong) 88%, transparent);
    border: 1px solid var(--app-border);
    border-radius: 999px;
    color: var(--app-text-muted);
    display: inline-flex;
    gap: 0.5rem;
    padding: 0.8rem 1rem;
    text-decoration: none;
}

.branch-form-back svg {
    height: 1rem;
    width: 1rem;
}

.branch-wizard {
    background: var(--app-panel);
    border: 1px solid var(--app-border);
    border-radius: 2rem;
    overflow: hidden;
}

.branch-wizard__steps {
    border-bottom: 1px solid var(--app-border);
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    justify-content: center;
    padding: 1.2rem 1.5rem;
}

.branch-step {
    align-items: center;
    background: transparent;
    border: none;
    border-radius: 1rem;
    color: var(--app-text-muted);
    cursor: pointer;
    display: inline-flex;
    flex-direction: column;
    gap: 0.45rem;
    padding: 0.45rem 0.85rem;
}

.branch-step.is-active {
    background: color-mix(in srgb, var(--app-brand) 8%, transparent);
    color: var(--app-text);
}

.branch-step.is-complete .branch-step__number {
    background: color-mix(in srgb, var(--app-brand) 18%, transparent);
    color: var(--app-brand);
}

.branch-step.is-locked {
    cursor: default;
    opacity: 0.45;
}

.branch-step__number {
    align-items: center;
    background: var(--app-panel-strong);
    border-radius: 999px;
    color: var(--app-text-muted);
    display: inline-flex;
    font-size: 0.8rem;
    font-weight: 800;
    height: 2rem;
    justify-content: center;
    width: 2rem;
}

.branch-step.is-active .branch-step__number {
    background: var(--app-brand);
    color: #0f172a;
}

.branch-step__title {
    font-size: 0.78rem;
    font-weight: 700;
}

.branch-wizard__body {
    padding: 1.5rem;
}

.branch-server-error {
    background: rgba(226, 75, 74, 0.1);
    border: 1px solid rgba(226, 75, 74, 0.24);
    border-radius: 1rem;
    color: #ff9e9c;
    margin-bottom: 1rem;
    padding: 0.9rem 1rem;
}

.branch-grid-form {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.branch-field {
    display: flex;
    flex-direction: column;
}

.branch-field--full {
    grid-column: 1 / -1;
}

.branch-field label {
    color: var(--app-text);
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 0.55rem;
}

.branch-field label span {
    color: #fb7185;
}

.branch-field label em {
    color: var(--app-text-muted);
    font-size: 0.9rem;
    font-style: normal;
    font-weight: 400;
}

.branch-field input,
.branch-field select {
    background: var(--app-panel-strong);
    border: 1px solid var(--app-border);
    border-radius: 1rem;
    color: var(--app-text);
    min-height: 3.3rem;
    outline: none;
    padding: 0.9rem 1rem;
}

.branch-field input:focus,
.branch-field select:focus {
    border-color: var(--app-brand);
}

.branch-field:has(.branch-field-error) input,
.branch-field:has(.branch-field-error) select {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
}

.branch-field-error {
    color: #ff8b89;
    font-size: 0.82rem;
    margin: 0.45rem 0 0;
}

.branch-radio-group {
    display: grid;
    gap: 0.8rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.branch-radio {
    align-items: center;
    background: var(--app-panel-strong);
    border: 1px solid var(--app-border);
    border-radius: 1rem;
    cursor: pointer;
    display: flex;
    gap: 0.75rem;
    min-height: 3.3rem;
    padding: 0.9rem 1rem;
}

.branch-radio.is-selected {
    background: color-mix(in srgb, var(--app-brand) 8%, transparent);
    border-color: var(--app-brand);
}

.branch-amenities-grid {
    display: grid;
    gap: 0.9rem;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.branch-amenity-pill {
    align-items: center;
    background: var(--app-panel-strong);
    border: 1px solid var(--app-border);
    border-radius: 1rem;
    cursor: pointer;
    display: flex;
    gap: 0.7rem;
    min-height: 3.4rem;
    padding: 0.85rem 1rem;
}

.branch-amenity-pill input {
    display: none;
}

.branch-amenity-pill.is-selected {
    background: color-mix(in srgb, var(--app-brand) 10%, transparent);
    border-color: var(--app-brand);
    color: var(--app-brand);
}

.branch-amenity-pill__icon {
    font-size: 1.1rem;
}

.branch-hours-list {
    background: color-mix(in srgb, var(--app-panel-strong) 90%, transparent);
    border: 1px solid var(--app-border);
    border-radius: 1.25rem;
    overflow: hidden;
}

.branch-hours-row {
    align-items: center;
    border-bottom: 1px solid var(--app-border);
    display: flex;
    gap: 1rem;
    padding: 1rem;
}

.branch-hours-row:last-child {
    border-bottom: none;
}

.branch-hours-row__day {
    color: var(--app-text);
    font-weight: 700;
    width: 3rem;
}

.branch-hours-row__closed {
    align-items: center;
    color: var(--app-text-muted);
    display: inline-flex;
    gap: 0.45rem;
}

.branch-hours-row__times {
    align-items: center;
    display: inline-flex;
    gap: 0.75rem;
    margin-left: auto;
}

.branch-hours-row__times input {
    background: var(--app-panel);
    border: 1px solid var(--app-border);
    border-radius: 0.85rem;
    color: var(--app-text);
    min-height: 2.8rem;
    padding: 0.65rem 0.8rem;
}

.branch-hours-row__off {
    color: var(--app-text-muted);
    font-style: italic;
    margin-left: auto;
}

.branch-wizard__footer {
    align-items: center;
    border-top: 1px solid var(--app-border);
    display: flex;
    justify-content: space-between;
    padding: 1.35rem 1.5rem;
}

.branch-wizard__footer-right {
    align-items: center;
    display: flex;
    gap: 0.85rem;
}

.branch-wizard__footer-label {
    color: var(--app-text-muted);
    font-size: 0.84rem;
}

.branch-footer-btn {
    align-items: center;
    border-radius: 1rem;
    cursor: pointer;
    display: inline-flex;
    font-size: 0.95rem;
    font-weight: 700;
    gap: 0.5rem;
    justify-content: center;
    min-height: 3rem;
    padding: 0.8rem 1.1rem;
    text-decoration: none;
}

.branch-footer-btn--ghost {
    background: color-mix(in srgb, var(--app-panel-strong) 88%, transparent);
    border: 1px solid var(--app-border);
    color: var(--app-text-muted);
}

.branch-footer-btn--primary {
    background: var(--app-brand);
    border: 1px solid transparent;
    color: #0f172a;
}

.branch-footer-btn--primary svg {
    height: 1rem;
    width: 1rem;
}

.branch-footer-btn:disabled {
    cursor: wait;
    opacity: 0.6;
}

@media (max-width: 900px) {
    .branch-amenities-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 720px) {
    .branch-form-header,
    .branch-wizard__footer,
    .branch-wizard__footer-right {
        align-items: stretch;
        flex-direction: column;
    }

    .branch-grid-form,
    .branch-radio-group,
    .branch-amenities-grid {
        grid-template-columns: 1fr;
    }

    .branch-hours-row {
        align-items: flex-start;
        flex-direction: column;
    }

    .branch-hours-row__times,
    .branch-hours-row__off {
        margin-left: 0;
    }

    .branch-hours-row__times {
        width: 100%;
    }

    .branch-hours-row__times input {
        flex: 1;
        min-width: 0;
    }
}
</style>

