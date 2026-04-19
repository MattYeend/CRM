<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import PermissionForm from './components/PermissionForm.vue'
import { route } from 'ziggy-js'

interface SelectOption {
    id: number
    name: string
}

interface Role {
    id: number
    name: string
}

interface Permission {
    id: number
    name: string
    label: string
    is_test: boolean
    meta: Record<string, any> | null
    roles: Role[]
}

defineProps<{
    permission: Permission
    roles: SelectOption[]
}>()
</script>

<template>
    <AppLayout :title="`Edit Permission: ${permission.name}`">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Permission
                </h2>
                <div class="flex space-x-2">
                    <Link
                        :href="route('permissions.show', permission.id)"
                        class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700"
                    >
                        Cancel
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <PermissionForm
                        :permission="permission"
                        :roles="roles"
                        method="put"
                        :submit-route="`/api/permissions/${permission.id}`"
                        submit-label="Update Permission"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>