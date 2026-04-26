<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchParts, deletePart } from '@/services/partService'

interface Part {
    id: number
    sku: string
    name: string
    type?: string
    status?: string
    quantity: number
    reorder_point?: number
    is_low_stock: boolean
    is_out_of_stock: boolean
    category?: { id: number; name: string }
    product?: { id: number; name: string }
    permissions: UserPermissions
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

const permissions = ref<GlobalPermissions>({
    create: false,
    viewAny: false,
})

const parts = ref<Part[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Parts', href: route('parts.index') },
]

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

function formatStatus(status: string | undefined): string {
    if (!status) return '—'
    return status
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ')
}

async function loadParts(page = 1) {
    loading.value = true
    try {
        const data = await fetchParts(perPage, page)
        parts.value = data.data
        permissions.value = data.permissions
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

async function handleDelete(id: number) {
    if (!confirm('Are you sure you want to delete this part?')) return
    await deletePart(id)
    loadParts(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadParts(page)
    }
}

onMounted(() => loadParts())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Parts" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Parts</h1>
                <div class="flex gap-2">
                    <Link
                        v-if="permissions.create"
                        :href="route('parts.create')"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        Create
                    </Link>
                </div>
            </div>

            <div v-if="loading" class="text-center py-6 text-gray-500">
                Loading...
            </div>

            <table v-else class="w-full border text-sm">
                <thead>
                    <tr>
                        <th class="p-2 text-left">SKU</th>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Category</th>
                        <th class="p-2 text-left">Type</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="part in parts" :key="part.id" class="border-t">
                        <td class="p-2 text-xs">{{ part.sku }}</td>
                        <td class="p-2">
                            {{ part.name }}
                            <span
                                v-if="part.is_out_of_stock"
                                class="ml-1 text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded"
                            >Out</span>
                            <span
                                v-else-if="part.is_low_stock"
                                class="ml-1 text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded"
                            >Low</span>
                        </td>
                        <td class="p-2">{{ part.category?.name || '—' }}</td>
                        <td class="p-2">{{ part.type || '—' }}</td>
                        <td class="p-2">
                            <span
                                v-if="part.status"
                                class="text-xs px-2 py-0.5 rounded-full"
                                :class="part.status === 'active'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-600'"
                            >
                                {{ formatStatus(part.status) }}
                            </span>
                            <span v-else>—</span>
                        </td>
                        <td class="p-2 text-right tabular-nums">{{ part.quantity }}</td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="part.permissions.view"
                                :href="route('parts.show', { part: part.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="part.permissions.update"
                                :href="route('parts.edit', { part: part.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="part.permissions.delete"
                                @click="handleDelete(part.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="parts.length === 0">
                        <td colspan="7" class="p-6 text-center text-gray-500">
                            No parts found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
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