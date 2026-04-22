<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchInvoices, deleteInvoices } from '@/services/invoiceService'

interface Invoice {
    id: number
    number: string
    status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled'
    currency: string
    formatted_subtotal: string
    formatted_tax: string
    formatted_total: string
    issue_date?: string | null
    due_date?: string | null
    is_overdue: boolean
    company?: { id: number; name: string } | null
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

const invoices = ref<Invoice[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const statusClasses: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-600',
    sent: 'bg-blue-100 text-blue-700',
    paid: 'bg-green-100 text-green-700',
    overdue: 'bg-red-100 text-red-700',
    cancelled: 'bg-yellow-100 text-yellow-700',
}

function formatDate(date?: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-GB')
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Invoices', href: route('invoices.index') },
]

async function loadInvoices(page = 1) {
    loading.value = true
    try {
        const data = await fetchInvoices(perPage, page)
        invoices.value = data.data
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
    if (!confirm('Are you sure you want to delete this invoice?')) return
    await deleteInvoices(id)
    loadInvoices(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadInvoices(page)
    }
}

onMounted(() => loadInvoices())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Invoices" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Invoices</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('invoices.create')"
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
                        <th class="p-2 text-left">Number</th>
                        <th class="p-2 text-left">Company</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-left">Currency</th>
                        <th class="p-2 text-right">Subtotal</th>
                        <th class="p-2 text-right">Tax</th>
                        <th class="p-2 text-right">Total</th>
                        <th class="p-2 text-left">Issue Date</th>
                        <th class="p-2 text-left">Due Date</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="invoice in invoices" :key="invoice.id" class="border-t">
                        <td class="p-2 font-medium">{{ invoice.number }}</td>
                        <td class="p-2">
                            <Link
                                v-if="invoice.company"
                                :href="route('companies.show', { company: invoice.company.id })"
                            >
                                {{ invoice.company.name }}
                            </Link>
                            <span v-else>—</span>
                        </td>
                        <td class="p-2">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                                :class="statusClasses[invoice.status] ?? 'bg-gray-100 text-gray-600'"
                            >
                                {{ invoice.status }}
                            </span>
                        </td>
                        <td class="p-2">{{ invoice.currency }}</td>
                        <td class="p-2 text-right">{{ invoice.formatted_subtotal }}</td>
                        <td class="p-2 text-right">{{ invoice.formatted_tax }}</td>
                        <td class="p-2 text-right font-medium">{{ invoice.formatted_total }}</td>
                        <td class="p-2">{{ formatDate(invoice.issue_date) }}</td>
                        <td class="p-2">
                            <span :class="{ 'text-red-600 font-semibold': invoice.is_overdue }">
                                {{ formatDate(invoice.due_date) }}
                            </span>
                        </td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="invoice.permissions.view"
                                :href="route('invoices.show', { invoice: invoice.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="invoice.permissions.update"
                                :href="route('invoices.edit', { invoice: invoice.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="invoice.permissions.delete"
                                @click="handleDelete(invoice.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="invoices.length === 0">
                        <td colspan="10" class="p-4 text-center text-gray-500">
                            No invoices found.
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