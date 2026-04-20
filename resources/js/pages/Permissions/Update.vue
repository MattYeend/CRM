<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import PermissionForm from './components/PermissionForm.vue'

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

const props = defineProps<{
    permission: Permission
    roles: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Permissions', href: route('permissions.index') },
    { title: `Permission ${props.permission.name}`, href: route('permissions.show', { permission: props.permission.id }) },
    { title: `Edit Permission ${props.permission.name}`, href: route('permissions.edit', { permission: props.permission.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Edit ${permission.name}`" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Permission {{ permission.name }}</h1>

            <PermissionForm
                :permission="permission"
                :roles="roles"
                method="put"
                :submit-route="`/api/permissions/${permission.id}`"
                submitLabel="Update Permission"
            />
        </div>
    </AppLayout>
</template>