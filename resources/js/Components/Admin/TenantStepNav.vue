<script setup>
defineProps({
    steps: {
        type: Array,
        required: true,
    },
    currentStep: {
        type: Number,
        required: true,
    },
    stepErrors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['select']);

const hasError = (stepId, stepErrors) => Boolean(stepErrors?.[stepId]?.length);
</script>

<template>
    <div class="rounded-xl border border-white/10 bg-white/5 p-2">
        <div class="flex gap-1.5 overflow-x-auto">
            <template v-for="(step, index) in steps" :key="step.id">
                <button
                    type="button"
                    @click="emit('select', step.id)"
                    :class="[
                        'flex min-w-[128px] flex-1 items-center gap-2 rounded-lg border px-2.5 py-2 text-left transition',
                        currentStep === step.id
                            ? 'border-orange-400 bg-orange-500/10 text-slate-100'
                            : hasError(step.id, stepErrors)
                                ? 'border-red-400/50 bg-red-500/10 text-red-200'
                                : 'border-white/10 bg-slate-950/50 text-slate-300 hover:bg-white/5',
                    ]"
                >
                    <span
                        :class="[
                            'inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-xs font-semibold',
                            currentStep === step.id
                                ? 'bg-orange-500 text-slate-950'
                                : hasError(step.id, stepErrors)
                                    ? 'bg-red-500/20 text-red-300'
                                    : 'bg-orange-500/15 text-orange-300',
                        ]"
                    >
                        {{ step.id }}
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-xs font-semibold">{{ step.title }}</span>
                        <span class="mt-0.5 hidden truncate text-[11px] text-slate-400 md:block">{{ step.desc }}</span>
                    </span>
                </button>

                <span
                    v-if="index < steps.length - 1"
                    class="hidden items-center px-0.5 text-xs font-semibold text-slate-500 md:inline-flex"
                >
                    -&gt;
                </span>
            </template>
        </div>
    </div>
</template>

