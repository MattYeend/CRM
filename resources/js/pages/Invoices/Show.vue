<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchInvoice, deleteInvoices } from '@/services/invoiceService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface InvoiceItem {
    id: number
    description: string
    quantity: number
    formatted_unit_price: string
    formatted_line_total: string
    product?: { id: number; name: string } | null
}

interface Invoice {
    id: number
    number: string
    status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled'
    currency: string
    subtotal: number
    formatted_subtotal: string
    tax: number
    formatted_tax: string
    total: number
    formatted_total: string
    issue_date?: string | null
    due_date?: string | null
    is_draft: boolean
    is_sent: boolean
    is_paid: boolean
    is_overdue: boolean
    is_cancelled: boolean
    company?: { id: number; name: string } | null
    items?: InvoiceItem[]
    creator?: { name: string } | null
    permissions: UserPermissions
}

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

const props = defineProps<{ invoice: any }>()

const invoice = ref<Invoice>({
    id: props.invoice.id,
    number: props.invoice.number ?? '',
    status: props.invoice.status ?? 'draft',
    currency: props.invoice.currency ?? 'GBP',
    subtotal: props.invoice.subtotal ?? 0,
    formatted_subtotal: props.invoice.formatted_subtotal ?? '0.00',
    tax: props.invoice.tax ?? 0,
    formatted_tax: props.invoice.formatted_tax ?? '0.00',
    total: props.invoice.total ?? 0,
    formatted_total: props.invoice.formatted_total ?? '0.00',
    issue_date: props.invoice.issue_date ?? null,
    due_date: props.invoice.due_date ?? null,
    is_draft: props.invoice.is_draft ?? false,
    is_sent: props.invoice.is_sent ?? false,
    is_paid: props.invoice.is_paid ?? false,
    is_overdue: props.invoice.is_overdue ?? false,
    is_cancelled: props.invoice.is_cancelled ?? false,
    company: props.invoice.company ?? null,
    items: props.invoice.items ?? [],
    creator: props.invoice.creator ?? null,
    permissions: props.invoice.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Invoices', href: route('invoices.index') },
    { title: invoice.value.number, href: route('invoices.show', { invoice: invoice.value.id }) },
]

async function loadInvoice() {
    const data = await fetchInvoice(invoice.value.id)
    Object.assign(invoice.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this invoice?')) return
    await deleteInvoices(invoice.value.id)
    window.location.href = route('invoices.index')
}

onMounted(() => loadInvoice())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="invoice.number || 'Invoice'" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ invoice.number }}</h1>
                        <span
                            class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                            :class="statusClasses[invoice.status] ?? 'bg-gray-100 text-gray-600'"
                        >
                            {{ invoice.status }}
                        </span>
                        <span
                            v-if="invoice.is_overdue"
                            class="ml-2 mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"
                        >
                            Overdue
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="invoice.permissions?.update"
                            :href="route('invoices.edit', { invoice: invoice.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>
                        <Link
                            :href="route('invoices.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>
                        <button
                            v-if="invoice.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6">
                    <div v-if="invoice.company">
                        <span class="font-semibold">Company: </span>
                        <Link
                            :href="route('companies.show', { company: invoice.company.id })"
                            class="text-blue-600 underline"
                        >
                            {{ invoice.company.name }}
                        </Link>
                    </div>

                    <div>
                        <span class="font-semibold">Currency: </span>
                        <span>{{ invoice.currency }}</span>
                    </div>

                    <div>
                        <span class="font-semibold">Issue Date: </span>
                        <span>{{ formatDate(invoice.issue_date) }}</span>
                    </div>

                    <div>
                        <span class="font-semibold">Due Date: </span>
                        <span :class="{ 'text-red-600 font-semibold': invoice.is_overdue }">
                            {{ formatDate(invoice.due_date) }}
                        </span>
                    </div>

                    <div v-if="invoice.creator">
                        <span class="font-semibold">Created By: </span>
                        <span>{{ invoice.creator.name }}</span>
                    </div>
                </div>

                <!-- Line Items -->
                <div v-if="invoice.items && invoice.items.length > 0" class="mb-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-3">Line Items</h2>
                    <table class="w-full border text-sm">
                        <thead>
                            <tr>
                                <th class="p-2 text-left">Description</th>
                                <th class="p-2 text-left">Product</th>
                                <th class="p-2 text-right">Qty</th>
                                <th class="p-2 text-right">Unit Price</th>
                                <th class="p-2 text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in invoice.items" :key="item.id" class="border-t">
                                <td class="p-2">{{ item.description }}</td>
                                <td class="p-2">
                                    <span v-if="item.product">{{ item.product.name }}</span>
                                    <span v-else class="text-gray-400 italic">Bespoke</span>
                                </td>
                                <td class="p-2 text-right">{{ item.quantity }}</td>
                                <td class="p-2 text-right">{{ item.formatted_unit_price }}</td>
                                <td class="p-2 text-right font-medium">{{ item.formatted_line_total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="flex justify-end">
                    <div class="w-64 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span>{{ invoice.currency }} {{ invoice.formatted_subtotal }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax</span>
                            <span>{{ invoice.currency }} {{ invoice.formatted_tax }}</span>
                        </div>
                        <div class="flex justify-between font-semibold text-base border-t pt-1">
                            <span>Total</span>
                            <span>{{ invoice.currency }} {{ invoice.formatted_total }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>