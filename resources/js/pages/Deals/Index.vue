<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchDeals, deleteDeals } from '@/services/dealService'

interface Deal {
    id: number
    title: string
    status: 'open' | 'won' | 'lost' | 'archived'
    value: number
    currency: string
    close_date?: string | null
    company?: { name: string } | null
    owner?: { name: string } | null
    pipeline?: { name: string } | null
    stage?: { name: string } | null
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

const deals = ref<Deal[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const statusClasses: Record<string, string> = {
    open: 'bg-blue-100 text-blue-700',
    won: 'bg-green-100 text-green-700',
    lost: 'bg-red-100 text-red-700',
    archived: 'bg-gray-100 text-gray-600',
}

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

function formatDate(date?: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-GB')
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Deals', href: route('deals.index') },
]

async function loadDeals(page = 1) {
    loading.value = true
    try {
        const data = await fetchDeals(perPage, page)
        deals.value = data.data
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
    if (!confirm('Are you sure you want to delete this deal?')) return
    await deleteDeals(id)
    loadDeals(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadDeals(page)
    }
}

onMounted(() => loadDeals())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Deals" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Deals</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('deals.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <table v-else class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Title</th>
                        <th class="p-2 text-left">Company</th>
                        <th class="p-2 text-left">Owner</th>
                        <th class="p-2 text-left">Pipeline / Stage</th>
                        <th class="p-2 text-left">Value</th>
                        <th class="p-2 text-left">Close Date</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="deal in deals" :key="deal.id" class="border-t">
                        <td class="p-2 font-medium">{{ deal.title }}</td>
                        <td class="p-2">{{ deal.company?.name ?? '—' }}</td>
                        <td class="p-2">{{ deal.owner?.name ?? '—' }}</td>
                        <td class="p-2">
                            <span v-if="deal.pipeline">{{ deal.pipeline.name }}</span>
                            <span v-if="deal.pipeline && deal.stage"> / </span>
                            <span v-if="deal.stage">{{ deal.stage.name }}</span>
                            <span v-if="!deal.pipeline && !deal.stage">—</span>
                        </td>
                        <td class="p-2">{{ formatCurrency(deal.value, deal.currency) }}</td>
                        <td class="p-2">{{ formatDate(deal.close_date) }}</td>
                        <td class="p-2">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                                :class="statusClasses[deal.status] ?? 'bg-gray-100 text-gray-600'"
                            >
                                {{ deal.status }}
                            </span>
                        </td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="deal.permissions.view"
                                :href="route('deals.show', { deal: deal.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="deal.permissions.update"
                                :href="route('deals.edit', { deal: deal.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="deal.permissions.delete"
                                @click="handleDelete(deal.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="deals.length === 0">
                        <td colspan="8" class="p-4 text-center text-gray-500">
                            No deals found.
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