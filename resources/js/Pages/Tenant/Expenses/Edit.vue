<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ExpenseForm from './ExpenseForm.vue';

defineProps({
    expense: Object,
    branches: Object,
    staffList: Object,
    categories: Object,
    methods: Object,
    recurrence: Object,
    selectedBranchId: [String, Number],
});
</script>

<template>
    <AppLayout>
        <Head title="Edit Expense" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold md:text-2xl">Edit Expense</h1>
                    <p class="app-muted mt-0.5 text-sm">Update expense details.</p>
                </div>
                <Link href="/expenses" class="app-panel rounded-lg border px-3 py-2 text-xs font-semibold transition hover:opacity-80">Back</Link>
            </div>

            <ExpenseForm
                :expense="expense"
                :branches="branches"
                :staff-list="staffList"
                :categories="categories"
                :methods="methods"
                :recurrence="recurrence"
                :selected-branch-id="selectedBranchId"
            />
        </div>
    </AppLayout>
</template>
