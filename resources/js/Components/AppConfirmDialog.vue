<script setup>
defineProps({
    open: Boolean,
    title: {
        type: String,
        default: 'Confirm action',
    },
    message: {
        type: String,
        default: '',
    },
    confirmLabel: {
        type: String,
        default: 'Confirm',
    },
    cancelLabel: {
        type: String,
        default: 'Cancel',
    },
    processing: Boolean,
    tone: {
        type: String,
        default: 'warning',
    },
});

const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm" @click.self="emit('cancel')">
        <div class="app-panel w-full max-w-md rounded-xl border p-4 shadow-2xl">
            <div class="flex items-start gap-3">
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                    :class="tone === 'danger' ? 'bg-red-500/10 text-red-400' : 'bg-amber-500/10 text-amber-400'"
                >
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                        <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-semibold">{{ title }}</h3>
                    <p class="app-muted mt-1 text-sm leading-5">{{ message }}</p>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button
                    type="button"
                    class="app-panel rounded-lg border px-3 py-2 text-sm font-semibold transition hover:opacity-80"
                    :disabled="processing"
                    @click="emit('cancel')"
                >
                    {{ cancelLabel }}
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-orange-500 px-3 py-2 text-sm font-semibold text-slate-950 transition hover:bg-orange-400 disabled:opacity-60"
                    :disabled="processing"
                    @click="emit('confirm')"
                >
                    {{ processing ? 'Please wait...' : confirmLabel }}
                </button>
            </div>
        </div>
    </div>
</template>
