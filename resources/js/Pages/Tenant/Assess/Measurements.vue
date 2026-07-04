<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    // Add props based on controller data
});
</script>

<template>
    <AppLayout>
        <Head title="Tenant Assess Measurements" />
        <div class="p-6">
            <h1 class="text-2xl font-semibold mb-4">Measurements</h1>
            <p class="text-slate-400">This page is under construction. Vue component needs to be implemented.</p>
            <pre class="mt-4 text-xs bg-slate-800 p-4 rounded">{{ props }}</pre>
        </div>
    </AppLayout>
</template>
