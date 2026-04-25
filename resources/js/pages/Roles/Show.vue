<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchRole } from '@/services/roleService'
import RoleDetailSection from './components/RoleDetailSection.vue'

interface Permission {
    id: number
    name: string
}

interface User {
    id: number
    name: string
    email: string
}

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Role {
    id: number
    name: string
    label: string
    is_admin: boolean
    is_super_admin: boolean
    user_count: number
    permissions: Permission[]
    users?: User[] | null
    permissions_meta: UserPermissions
}

const props = defineProps<{ role: any }>()

const role = ref<Role>({
    id: props.role.id,
    name: props.role.name,
    label: props.role.label,
    is_admin: props.role.is_admin ?? false,
    is_super_admin: props.role.is_super_admin ?? false,
    user_count: props.role.user_count ?? 0,
    permissions: props.role.permissions ?? [],
    users: props.role.users,
    permissions_meta: props.role.permissions_meta ?? { view: false, update: false, delete: false },
})

function getRoleTypeLabel(role: Role): string {
    if (role.is_super_admin) return 'Super Admin'
    if (role.is_admin) return 'Admin'
    return 'Standard'
}

function getRoleTypeClass(role: Role): string {
    if (role.is_super_admin) return 'bg-red-100 text-red-700'
    if (role.is_admin) return 'bg-purple-100 text-purple-700'
    return 'bg-green-100 text-green-700'
}

function toTitleCase(value: string): string {
    return value
        .replace(/([A-Z])/g, ' $1')
        .replace(/[_-]/g, ' ')
        .trim()
        .replace(/\w\S*/g, word =>
            word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
        )
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Roles', href: route('roles.index') },
    { title: toTitleCase(role.value.label), href: route('roles.show', { role: role.value.id }) },
]

async function loadRole() {
    const data = await fetchRole(role.value.id)
    Object.assign(role.value, data)
}

onMounted(() => loadRole())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Role: ${toTitleCase(role.label)}`" />
        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ toTitleCase(role.label) }}</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            System name: <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ role.name }}</code>
                        </p>
                        <span
                            class="mt-2 inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                            :class="getRoleTypeClass(role)"
                        >
                            {{ getRoleTypeLabel(role) }}
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            :href="route('roles.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>
                    </div>
                </div>

                <RoleDetailSection :role="role" />
            </div>
        </div>
    </AppLayout>
</template>