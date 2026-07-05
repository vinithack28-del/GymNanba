<script setup>
import { computed } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    roleRow: {
        type: Object,
        default: null,
    },
    defaultModules: {
        type: Array,
        default: () => [],
    },
    staffCount: {
        type: Number,
        default: 0,
    },
});

const editing = computed(() => !!props.roleRow);
const roleName = computed(() => props.roleRow?.role || props.roleRow?.name || '');
const roleLabel = computed(() => props.roleRow?.display_name || roleName.value.replace(/_/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase()));

const normalizePermissions = (permissions) => {
    const normalized = {};

    if (Array.isArray(permissions)) {
        permissions.forEach((permission) => {
            const name = permission?.name || '';
            const [module, action] = name.split('.');

            if (module && action) {
                normalized[module] ??= {};
                normalized[module][action] = true;
            }
        });

        return normalized;
    }

    Object.entries(permissions || {}).forEach(([module, actions]) => {
        if (!actions || typeof actions !== 'object') {
            return;
        }

        if (actions.name) {
            const [permissionModule, permissionAction] = String(actions.name).split('.');

            if (permissionModule && permissionAction) {
                normalized[permissionModule] ??= {};
                normalized[permissionModule][permissionAction] = true;
            }

            return;
        }

        normalized[module] = {};
        Object.entries(actions).forEach(([action, enabled]) => {
            normalized[module][action] = Boolean(enabled);
        });
    });

    return normalized;
};

const storedPermissions = computed(() => normalizePermissions(props.roleRow?.permissions));

const initialPermissions = () => {
    const permissions = {};

    props.defaultModules.forEach((module) => {
        permissions[module.slug] = {};
        (module.actions || []).forEach((action) => {
            permissions[module.slug][action.slug] = Boolean(storedPermissions.value?.[module.slug]?.[action.slug]);
        });
    });

    return permissions;
};

const form = useForm({
    role_name: '',
    permissions: initialPermissions(),
});

const formTitle = computed(() => editing.value ? `${roleLabel.value} Permissions` : 'Add Role');
const formSubtitle = computed(() => editing.value ? `Manage permissions for ${roleLabel.value}.` : 'Create a custom role and choose permissions.');
const selectedCount = computed(() => {
    return Object.values(form.permissions || {}).reduce((total, actions) => {
        return total + Object.values(actions || {}).filter(Boolean).length;
    }, 0);
});

const fieldError = (field) => form.errors?.[field] || '';

const moduleSelectedCount = (module) => {
    return Object.values(form.permissions?.[module.slug] || {}).filter(Boolean).length;
};

const toggleModule = (module, enabled) => {
    (module.actions || []).forEach((action) => {
        form.permissions[module.slug][action.slug] = enabled;
    });
};

const submit = () => {
    if (editing.value) {
        form.put(`/staff/roles/${roleName.value}`);
        return;
    }

    form.post('/staff/roles');
};
</script>

<template>
    <AppLayout>
        <Head :title="formTitle" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold md:text-2xl">{{ formTitle }}</h1>
                    <p class="app-muted mt-0.5 text-sm">{{ formSubtitle }}</p>
                </div>
                <Link href="/staff/roles" class="app-panel rounded-lg border px-3 py-2 text-sm font-semibold transition hover:opacity-80">
                    Back
                </Link>
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <section v-if="!editing" class="app-panel rounded-xl border p-4">
                    <label class="mb-1.5 block text-sm font-semibold">Role name <span class="text-red-400">*</span></label>
                    <input
                        v-model="form.role_name"
                        type="text"
                        pattern="[a-z_]+"
                        placeholder="floor_manager"
                        class="app-panel-strong w-full max-w-sm rounded-lg border px-3 py-2 text-sm outline-none focus:border-orange-400"
                        :class="{ 'field-invalid': fieldError('role_name') }"
                        required
                    >
                    <p class="app-muted mt-1 text-xs">Use lowercase letters and underscores only.</p>
                    <p v-if="fieldError('role_name')" class="field-error">{{ fieldError('role_name') }}</p>
                </section>

                <section class="app-panel rounded-xl border p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold">Permissions</h2>
                            <p class="app-muted mt-0.5 text-xs">{{ selectedCount }} selected<span v-if="editing"> - {{ staffCount }} staff assigned</span></p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3">
                        <div v-for="module in defaultModules" :key="module.slug" class="app-panel-strong rounded-xl border p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="font-semibold">{{ module.name }}</p>
                                    <p class="app-muted mt-0.5 text-xs">{{ moduleSelectedCount(module) }} of {{ module.actions?.length || 0 }} enabled</p>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" class="app-panel rounded-lg border px-2.5 py-1.5 text-xs font-semibold transition hover:opacity-80" @click="toggleModule(module, true)">
                                        Enable all
                                    </button>
                                    <button type="button" class="app-panel rounded-lg border px-2.5 py-1.5 text-xs font-semibold transition hover:opacity-80" @click="toggleModule(module, false)">
                                        Clear
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                <label v-for="action in module.actions" :key="action.slug" class="app-panel flex items-center gap-2 rounded-lg border px-3 py-2 text-sm">
                                    <input v-model="form.permissions[module.slug][action.slug]" type="checkbox" class="h-4 w-4 accent-orange-500">
                                    <span>{{ action.name }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <p v-if="fieldError('permissions')" class="field-error">{{ fieldError('permissions') }}</p>
                </section>

                <div class="flex justify-end gap-2">
                    <Link href="/staff/roles" class="app-panel rounded-lg border px-4 py-2 text-sm font-semibold transition hover:opacity-80">
                        Cancel
                    </Link>
                    <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400 disabled:opacity-60" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : (editing ? 'Save Permissions' : 'Create Role') }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
