<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted, computed } from 'vue'
import { route } from 'ziggy-js'
import { fetchPermissions, deletePermissions } from '@/services/permissionService'

interface Permission {
    id: number
    name: string
    label: string
    is_assigned: boolean
    role_count: number
    permissions: {
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

const items = ref<Permission[]>([])
const loading = ref(true)

const currentPage = ref(1)
const perPage = 10

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const deletingId = ref<number | null>(null)

async function loadPermissions(page = 1) {
    loading.value = true

    try {
        const data = await fetchPermissions(perPage, page)

        items.value = data.data
        permissions.value = data.permissions

        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total

        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

const visiblePages = computed(() => {
    const total = pagination.last_page
    const current = currentPage.value
    const delta = 2

    const pages: (number | string)[] = []

    const start = Math.max(1, current - delta)
    const end = Math.min(total, current + delta)

    if (start > 1) {
        pages.push(1)
        if (start > 2) pages.push('...')
    }

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    if (end < total) {
        if (end < total - 1) pages.push('...')
        pages.push(total)
    }

    return pages
})

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadPermissions(page)
    }
}

async function handleDelete(id: number) {
    if (!confirm('Are you sure?')) return

    deletingId.value = id
    try {
        await deletePermissions(id)
        loadPermissions(currentPage.value)
    } finally {
        deletingId.value = null
    }
}

onMounted(() => loadPermissions())
</script>

<template>
    <AppLayout title="Permissions">
        <Head title="Permissions" />

        <div class="p-6">

            <!-- Header -->
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Permissions</h1>

                <Link
                    v-if="permissions.create"
                    :href="route('permissions.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <!-- Table -->
            <table v-else class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Label</th>
                        <th class="p-2 text-left">Roles</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="permission in items" :key="permission.id" class="border-t">
                        <td class="p-2 font-mono">
                            <Link
                                :href="route('permissions.show', permission.id)"
                                class="text-blue-600"
                            >
                                {{ permission.name }}
                            </Link>
                        </td>

                        <td class="p-2">{{ permission.label }}</td>

                        <td class="p-2">
                            {{ permission.role_count }}
                        </td>

                        <td class="p-2">
                            <span
                                :class="permission.is_assigned
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-gray-100 text-gray-800'"
                                class="px-2 py-1 rounded text-xs"
                            >
                                {{ permission.is_assigned ? 'Assigned' : 'Unassigned' }}
                            </span>
                        </td>

                        <td class="p-2 space-x-2 text-right">
                            <Link
                                v-if="permission.permissions.view"
                                :href="route('permissions.show', permission.id)"
                            >
                                View
                            </Link>

                            <Link
                                v-if="permission.permissions.update && permission.role_count === 0"
                                :href="route('permissions.edit', permission.id)"
                            >
                                Edit
                            </Link>

                            <button
                                v-if="permission.permissions.delete && permission.role_count === 0"
                                class="text-red-600"
                                :disabled="deletingId === permission.id"
                                @click="handleDelete(permission.id)"
                            >
                                {{ deletingId === permission.id ? 'Deleting…' : 'Delete' }}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    Previous
                </button>

                <button
                    v-for="page in visiblePages"
                    :key="page"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-blue-600 text-white': page === currentPage }"
                    :disabled="page === '...'"
                    @click="typeof page === 'number' && goToPage(page)"
                >
                    {{ page }}
                </button>

                <button
                    class="px-3 py-1 border rounded"
                    :disabled="currentPage === pagination.last_page"
                    @click="goToPage(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>