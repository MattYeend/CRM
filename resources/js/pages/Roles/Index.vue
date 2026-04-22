<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchRoles } from '@/services/roleService'

interface Role {
    id: number
    name: string
    label: string
    is_admin: boolean
    is_super_admin: boolean
    user_count: number
    permissions: Array<{ id: number; name: string }>
    permissions_meta: {
        view: boolean
        update: boolean
        delete: boolean
    }
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

const permissions = ref<GlobalPermissions>({
    create: false,
    viewAny: false,
})

const roles = ref<Role[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Roles', href: route('roles.index') },
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

async function loadRoles(page = 1) {
    loading.value = true
    try {
        const data = await fetchRoles(perPage, page)
        roles.value = data.data
        permissions.value = data.permissions
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadRoles(page)
    }
}

onMounted(() => loadRoles())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Roles" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Roles</h1>
            </div>

            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <table v-else class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Label</th>
                        <th class="p-2 text-left">Users</th>
                        <th class="p-2 text-left">Permissions</th>
                        <th class="p-2 text-left">Type</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="role in roles" :key="role.id" class="border-t">
                        <td class="p-2">{{ role.name }}</td>
                        <td class="p-2">{{ role.label }}</td>
                        <td class="p-2">{{ role.user_count }}</td>
                        <td class="p-2">{{ role.permissions.length }}</td>
                        <td class="p-2">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="getRoleTypeClass(role)"
                            >
                                {{ getRoleTypeLabel(role) }}
                            </span>
                        </td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="role.permissions_meta.view"
                                :href="route('roles.show', { role: role.id })"
                            >
                                View
                            </Link>
                        </td>
                    </tr>

                    <tr v-if="roles.length === 0">
                        <td colspan="7" class="p-4 text-center text-gray-500">
                            No roles found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    Previous
                </button>

                <button
                    v-for="page in pagination.last_page"
                    :key="page"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-blue-600 text-white': page === currentPage }"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>

                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === pagination.last_page"
                    @click="goToPage(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>