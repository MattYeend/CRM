<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchInvoiceItems, deleteInvoiceItems } from '@/services/invoiceItemService'

interface InvoiceItem {
    id: number
    invoice_id: number
    description: string
    quantity: number
    unit_price: number
    formatted_unit_price: string
    line_total: number
    formatted_line_total: string
    has_product: boolean
    invoice?: { id: number } | null
    product?: { id: number; name: string } | null
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

const invoiceItems = ref<InvoiceItem[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Invoice Items', href: route('invoice-items.index') },
]

async function loadInvoiceItems(page = 1) {
    loading.value = true
    try {
        const data = await fetchInvoiceItems(perPage, page)
        invoiceItems.value = data.data
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
    if (!confirm('Are you sure you want to delete this line item?')) return
    await deleteInvoiceItems(id)
    loadInvoiceItems(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadInvoiceItems(page)
    }
}

onMounted(() => loadInvoiceItems())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Invoice Items" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Invoice Items</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('invoice-items.create')"
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
                        <th class="p-2 text-left">Invoice</th>
                        <th class="p-2 text-left">Description</th>
                        <th class="p-2 text-left">Product</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-right">Unit Price</th>
                        <th class="p-2 text-right">Line Total</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in invoiceItems" :key="item.id" class="border-t">
                        <td class="p-2">
                            <Link
                                v-if="item.invoice"
                                :href="route('invoices.show', { invoice: item.invoice.id })"
                                class="text-blue-600 underline"
                            >
                                #{{ item.invoice.id }}
                            </Link>
                            <span v-else>—</span>
                        </td>
                        <td class="p-2">{{ item.description }}</td>
                        <td class="p-2">
                            <span v-if="item.product">{{ item.product.name }}</span>
                            <span v-else class="text-gray-400 italic">Bespoke</span>
                        </td>
                        <td class="p-2 text-right">{{ item.quantity }}</td>
                        <td class="p-2 text-right">{{ item.formatted_unit_price }}</td>
                        <td class="p-2 text-right font-medium">{{ item.formatted_line_total }}</td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="item.permissions.view"
                                :href="route('invoice-items.show', { invoiceItem: item.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="item.permissions.update"
                                :href="route('invoice-items.edit', { invoiceItem: item.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="item.permissions.delete"
                                @click="handleDelete(item.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="invoiceItems.length === 0">
                        <td colspan="7" class="p-4 text-center text-gray-500">
                            No invoice items found.
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