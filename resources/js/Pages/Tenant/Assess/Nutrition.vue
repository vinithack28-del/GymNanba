<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    member: Object,
    plans: Object,
    editingPlan: Object,
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const meals = ref([{ meal_name: '', time: '', calories: '', food_items: '' }]);

const form = useForm({
    member_id: props.member?.id || '',
    plan_name: '',
    plan_date: new Date().toISOString().split('T')[0],
    goal_notes: '',
    meals: meals.value,
});

const addMeal = () => {
    meals.value.push({ meal_name: '', time: '', calories: '', food_items: '' });
};

const removeMeal = (index) => {
    meals.value.splice(index, 1);
};

const submit = () => {
    form.meals = meals.value;
    if (props.editingPlan) {
        form.put(`/tenant/assess/nutrition/${props.editingPlan.id}`);
    } else {
        form.post('/tenant/assess/nutrition');
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Nutrition" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Assessments</p>
                <h1 class="mt-2 text-3xl font-semibold">Nutrition</h1>
                <p class="mt-1 text-slate-300">Create and review diet plans for the selected client.</p>
            </div>

            <div v-if="member && (canAdd || (editingPlan && canEdit))" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-lg font-bold mb-4">{{ editingPlan ? 'Save Plan' : 'Create Diet Plan' }}</h3>
                <form @submit.prevent="submit" class="flex flex-col gap-4">
                    <input type="hidden" v-model="form.member_id">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Plan Name <span class="text-red-400">*</span></label>
                            <input v-model="form.plan_name" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Plan Date <span class="text-red-400">*</span></label>
                            <input v-model="form.plan_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-2 block text-sm font-medium">Goal / Notes</label>
                            <textarea v-model="form.goal_notes" rows="2" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400"></textarea>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div v-for="(meal, index) in meals" :key="index" class="rounded-lg border border-white/10 bg-slate-950/50 p-4">
                            <div class="grid gap-3 md:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-xs font-medium">Meal Name <span class="text-red-400">*</span></label>
                                    <input v-model="meal.meal_name" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-2 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium">Time</label>
                                    <input v-model="meal.time" type="time" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-2 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium">Calories</label>
                                    <input v-model="meal.calories" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-2 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="mb-1 block text-xs font-medium">Food Items <span class="text-red-400">*</span></label>
                                    <textarea v-model="meal.food_items" rows="2" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-2 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400" required></textarea>
                                </div>
                            </div>
                            <button v-if="meals.length > 1" @click="removeMeal(index)" class="mt-2 text-xs text-red-400 hover:text-red-300">Remove Meal</button>
                        </div>
                        <button @click="addMeal" class="flex items-center gap-2 rounded-lg border border-dashed border-white/10 px-3 py-2 text-sm font-semibold text-slate-400 hover:border-orange-400 hover:text-orange-400">
                            + Add Meal
                        </button>
                    </div>

                    <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        {{ editingPlan ? 'Save Plan' : 'Create Diet Plan' }}
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-lg font-bold mb-4">Diet Plans</h3>
                <div v-if="!member" class="p-6 text-center text-sm text-slate-400">
                    Select a client to view diet plans.
                </div>
                <div v-else-if="!plans || plans.length === 0" class="p-6 text-center text-sm text-slate-400">
                    No diet plans yet.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Plan Date</th>
                                <th class="px-4 py-3">Plan Name</th>
                                <th class="px-4 py-3">Meals</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="plan in plans" :key="plan.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">{{ formatDate(plan.assessment_date) }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-bold">{{ plan.title }}</p>
                                    <p v-if="plan.notes" class="text-xs text-slate-400">{{ plan.notes }}</p>
                                </td>
                                <td class="px-4 py-3">{{ plan.payload?.meals?.length || 0 }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link v-if="canEdit" :href="`/tenant/assess/nutrition?member_id=${member.id}&edit=${plan.id}`" class="text-orange-400 hover:text-orange-300 text-sm">Edit</Link>
                                        <button v-if="canDelete" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
