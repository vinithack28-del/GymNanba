<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    staffOptions: {
        type: [Array, Object],
        default: () => [],
    },
    selectedStaffId: {
        type: [String, Number],
        default: null,
    },
    today: {
        type: String,
        default: '',
    },
});

const staffList = computed(() => Object.values(props.staffOptions || {}));
const selectedStaff = computed(() => staffList.value.find((staff) => Number(staff.id) === Number(form.staff_id)));
const formatRole = (role) => String(role || '-').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

const form = useForm({
    staff_id: props.selectedStaffId || '',
    attendance_date: props.today,
    checked_in_at: '',
    checked_out_at: '',
    reason: '',
});

const staffSearch = ref('');
const pickerOpen = ref(false);

watch(selectedStaff, (staff) => {
    if (staff) {
        staffSearch.value = `${staff.name} ${staff.phone ? `(${staff.phone})` : ''}`.trim();
    }
}, { immediate: true });

const filteredStaff = computed(() => {
    const search = staffSearch.value.trim().toLowerCase();

    if (!search || selectedStaff.value?.name === staffSearch.value) {
        return staffList.value.slice(0, 20);
    }

    return staffList.value.filter((staff) => {
        return [staff.name, staff.phone, staff.email, staff.role, staff.branch?.name]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(search));
    }).slice(0, 20);
});

const chooseStaff = (staff) => {
    form.staff_id = staff.id;
    staffSearch.value = `${staff.name} ${staff.phone ? `(${staff.phone})` : ''}`.trim();
    pickerOpen.value = false;
};

const clearStaff = () => {
    form.staff_id = '';
    staffSearch.value = '';
    pickerOpen.value = true;
};

const fieldError = (field) => form.errors?.[field] || '';

const submit = () => {
    form.post('/staff/attendance');
};
</script>

<template>
    <AppLayout>
        <Head title="Mark Staff Attendance" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold md:text-2xl">Mark Attendance</h1>
                    <p class="app-muted mt-0.5 text-sm">Record staff check-in and check-out time.</p>
                </div>

                <Link href="/staff/attendance" class="app-panel rounded-lg border px-3 py-2 text-xs font-semibold transition hover:opacity-80">Back to Attendance</Link>
            </div>

            <form @submit.prevent="submit" class="app-panel max-w-3xl rounded-xl border p-4">
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="relative md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium">Staff Member <span class="text-red-400">*</span></label>
                        <div class="flex gap-2">
                            <input
                                v-model="staffSearch"
                                type="search"
                                placeholder="Search staff by name, phone, email..."
                                class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400"
                                :class="{ 'field-invalid': fieldError('staff_id') }"
                                @focus="pickerOpen = true"
                                @input="form.staff_id = ''; pickerOpen = true"
                            >
                            <button v-if="form.staff_id" type="button" class="app-panel rounded-lg border px-3 py-2 text-xs font-semibold transition hover:opacity-80" @click="clearStaff">Clear</button>
                        </div>

                        <div v-if="pickerOpen" class="app-panel-strong absolute z-20 mt-1 max-h-64 w-full overflow-auto rounded-lg border shadow-xl">
                            <button
                                v-for="staff in filteredStaff"
                                :key="staff.id"
                                type="button"
                                class="block w-full px-3 py-2 text-left text-sm transition hover:bg-white/5"
                                @click="chooseStaff(staff)"
                            >
                                <span class="font-semibold">{{ staff.name }}</span>
                                <span class="app-muted ml-2 text-xs">{{ formatRole(staff.role) }} - {{ staff.branch?.name || '-' }}</span>
                                <span v-if="staff.phone" class="app-muted block text-xs">{{ staff.phone }}</span>
                            </button>
                            <div v-if="filteredStaff.length === 0" class="app-muted px-3 py-3 text-sm">No staff found.</div>
                        </div>

                        <p v-if="fieldError('staff_id')" class="field-error">{{ fieldError('staff_id') }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Date <span class="text-red-400">*</span></label>
                        <input
                            v-model="form.attendance_date"
                            type="date"
                            class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400"
                            :class="{ 'field-invalid': fieldError('attendance_date') }"
                            required
                        >
                        <p v-if="fieldError('attendance_date')" class="field-error">{{ fieldError('attendance_date') }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Check In <span class="text-red-400">*</span></label>
                        <input
                            v-model="form.checked_in_at"
                            type="time"
                            class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400"
                            :class="{ 'field-invalid': fieldError('checked_in_at') }"
                            required
                        >
                        <p v-if="fieldError('checked_in_at')" class="field-error">{{ fieldError('checked_in_at') }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Check Out <span class="text-red-400">*</span></label>
                        <input
                            v-model="form.checked_out_at"
                            type="time"
                            class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400"
                            :class="{ 'field-invalid': fieldError('checked_out_at') }"
                            required
                        >
                        <p v-if="fieldError('checked_out_at')" class="field-error">{{ fieldError('checked_out_at') }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Notes</label>
                        <input
                            v-model="form.reason"
                            type="text"
                            placeholder="Optional note"
                            class="app-panel-strong w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400"
                            :class="{ 'field-invalid': fieldError('reason') }"
                        >
                        <p v-if="fieldError('reason')" class="field-error">{{ fieldError('reason') }}</p>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <Link href="/staff/attendance" class="app-panel rounded-lg border px-4 py-2 text-sm font-semibold transition hover:opacity-80">Cancel</Link>
                    <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-orange-400 disabled:opacity-60" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Attendance' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
