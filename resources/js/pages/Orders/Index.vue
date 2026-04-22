<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchOrders, deleteOrders } from '@/services/orderService'

interface Order {
    id: number
    status: string
    amount: number
    currency: string
    payment_method?: string | null
    paid_at?: string | null
    user?: { name: string } | null
    deal?: { title: string; id: number } | null
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

const orders = ref<Order[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const statusClasses: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    processing: 'bg-blue-100 text-blue-700',
    paid: 'bg-green-100 text-green-700',
    failed: 'bg-red-100 text-red-700',
    refunded: 'bg-purple-100 text-purple-700',
    cancelled: 'bg-gray-100 text-gray-600',
}

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

function formatDate(date?: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-GB')
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Orders', href: route('orders.index') },
]

async function loadOrders(page = 1) {
    loading.value = true
    try {
        const data = await fetchOrders(perPage, page)
        orders.value = data.data
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
    if (!confirm('Are you sure you want to delete this order?')) return
    await deleteOrders(id)
    loadOrders(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadOrders(page)
    }
}

onMounted(() => loadOrders())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Orders" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Orders</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('orders.create')"
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
                        <th class="p-2 text-left">Order ID</th>
                        <th class="p-2 text-left">User</th>
                        <th class="p-2 text-left">Deal</th>
                        <th class="p-2 text-left">Amount</th>
                        <th class="p-2 text-left">Payment Method</th>
                        <th class="p-2 text-left">Paid At</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in orders" :key="order.id" class="border-t">
                        <td class="p-2 font-medium">#{{ order.id }}</td>
                        <td class="p-2">{{ order.user?.name ?? '—' }}</td>
                        <td class="p-2">
                            <Link
                                v-if="order.deal"
                                :href="route('deals.show', { deal: order.deal.id })"
                            >
                                {{ order.deal.title }}
                            </Link>
                            <span v-else>—</span>
                        </td>
                        <td class="p-2">{{ formatCurrency(order.amount, order.currency) }}</td>
                        <td class="p-2 capitalize">{{ order.payment_method ?? '—' }}</td>
                        <td class="p-2">{{ formatDate(order.paid_at) }}</td>
                        <td class="p-2">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                                :class="statusClasses[order.status] ?? 'bg-gray-100 text-gray-600'"
                            >
                                {{ order.status }}
                            </span>
                        </td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="order.permissions.view"
                                :href="route('orders.show', { order: order.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="order.permissions.update"
                                :href="route('orders.edit', { order: order.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="order.permissions.delete"
                                @click="handleDelete(order.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="orders.length === 0">
                        <td colspan="8" class="p-4 text-center text-gray-500">
                            No orders found.
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