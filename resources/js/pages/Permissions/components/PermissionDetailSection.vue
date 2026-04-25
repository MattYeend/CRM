<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

interface Role {
    id: number
    name: string
}

interface Permission {
    label: string
    is_assigned: boolean
    role_count: number
    roles: Role[]
}

defineProps<{ permission: Permission }>()
</script>

<template>
    <div class="space-y-6">

        <!-- Details -->
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="font-semibold">Label</span>
                <span>{{ permission.label }}</span>
            </div>

            <div class="flex justify-between">
                <span class="font-semibold">Status</span>
                <span>
                    {{ permission.is_assigned ? 'Assigned' : 'Unassigned' }}
                </span>
            </div>

            <div class="flex justify-between">
                <span class="font-semibold">Roles</span>
                <span>{{ permission.role_count }}</span>
            </div>
        </div>

        <!-- Roles -->
        <div class="space-y-3">
            <h3 class="text-sm font-semibold uppercase tracking-wider">
                Assigned Roles ({{ permission.roles.length }})
            </h3>

            <div v-if="permission.roles.length" class="space-y-1">
                <div
                    v-for="role in permission.roles"
                    :key="role.id"
                    class="flex justify-between p-2 rounded text-sm"
                >
                    <span>{{ role.name }}</span>
                    <Link
                        :href="route('roles.show', { role: role.id })"
                    >
                        View
                    </Link>
                </div>
            </div>

            <div v-else class="text-gray-500 text-sm">
                No roles assigned.
            </div>
        </div>

    </div>
</template>