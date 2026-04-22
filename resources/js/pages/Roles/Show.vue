<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchRole } from '@/services/roleService'

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

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Roles', href: route('roles.index') },
    { title: role.value.label, href: route('roles.show', { role: role.value.id }) },
]

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

function groupPermissionsByResource(permissions: Permission[]) {
    const grouped: Record<string, Permission[]> = {}
    
    permissions.forEach(permission => {
        const parts = permission.name.split('.')
        const resource = parts[0] || 'other'
        
        if (!grouped[resource]) {
            grouped[resource] = []
        }
        grouped[resource].push(permission)
    })
    
    return grouped
}

const groupedPermissions = groupPermissionsByResource(role.value.permissions)

async function loadRole() {
    const data = await fetchRole(role.value.id)
    Object.assign(role.value, data)
}

onMounted(() => loadRole())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Role: ${role.label}`" />
        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ role.label }}</h1>
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

                <!-- Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6">
                    <div>
                        <span class="font-semibold">Total Users: </span>
                        <span>{{ role.user_count }}</span>
                    </div>
                    <div>
                        <span class="font-semibold">Total Permissions: </span>
                        <span>{{ role.permissions.length }}</span>
                    </div>
                </div>

                <!-- Permissions -->
                <div v-if="role.permissions.length > 0">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-3">Permissions</h2>
                    <div class="space-y-4">
                        <div
                            v-for="(permissions, resource) in groupedPermissions"
                            :key="resource"
                            class="border-l-4 border-indigo-500 pl-4"
                        >
                            <h3 class="text-sm font-semibold text-gray-900 uppercase mb-2">
                                {{ resource }}
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="inline-block px-2 py-1 bg-indigo-50 text-indigo-700 text-xs rounded"
                                >
                                    {{ permission.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users -->
                <div v-if="role.users && role.users.length > 0" class="mt-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-3">
                        Users with this Role ({{ role.users.length }})
                    </h2>
                    <table class="w-full border text-sm">
                        <thead>
                            <tr>
                                <th class="p-2 text-left">Name</th>
                                <th class="p-2 text-left">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in role.users"
                                :key="user.id"
                                class="border-t"
                            >
                                <td class="p-2 font-medium">{{ user.name }}</td>
                                <td class="p-2">{{ user.email }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>