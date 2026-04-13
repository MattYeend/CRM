<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchInvoiceItem, deleteInvoiceItems } from '@/services/invoiceItemService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

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
    creator?: { name: string } | null
    permissions: UserPermissions
}

const props = defineProps<{ invoiceItem: any }>()

const invoiceItem = ref<InvoiceItem>({
    id: props.invoiceItem.id,
    invoice_id: props.invoiceItem.invoice_id,
    description: props.invoiceItem.description ?? '',
    quantity: props.invoiceItem.quantity ?? 1,
    unit_price: props.invoiceItem.unit_price ?? 0,
    formatted_unit_price: props.invoiceItem.formatted_unit_price ?? '0.00',
    line_total: props.invoiceItem.line_total ?? 0,
    formatted_line_total: props.invoiceItem.formatted_line_total ?? '0.00',
    has_product: props.invoiceItem.has_product ?? false,
    invoice: props.invoiceItem.invoice ?? null,
    product: props.invoiceItem.product ?? null,
    creator: props.invoiceItem.creator ?? null,
    permissions: props.invoiceItem.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Invoice Items', href: route('invoice-items.index') },
    { title: 'View Invoice Item', href: route('invoice-items.show', { invoiceItem: invoiceItem.value.id }) },
]

async function loadInvoiceItem() {
    const data = await fetchInvoiceItem(invoiceItem.value.id)
    Object.assign(invoiceItem.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this line item?')) return
    await deleteInvoiceItems(invoiceItem.value.id)
    window.location.href = route('invoice-items.index')
}

onMounted(() => loadInvoiceItem())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="View Invoice Item" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ invoiceItem.description }}</h1>
                        <span
                            v-if="invoiceItem.has_product && invoiceItem.product"
                            class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"
                        >
                            Catalogue Item
                        </span>
                        <span
                            v-else
                            class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600"
                        >
                            Bespoke
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="invoiceItem.permissions?.update"
                            :href="route('invoice-items.edit', { invoiceItem: invoiceItem.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>
                        <Link
                            :href="route('invoice-items.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>
                        <button
                            v-if="invoiceItem.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6">
                    <div v-if="invoiceItem.invoice">
                        <span class="font-semibold">Invoice: </span>
                        <Link
                            :href="route('invoices.show', { invoice: invoiceItem.invoice.id })"
                            class="text-blue-600 underline"
                        >
                            #{{ invoiceItem.invoice.id }}
                        </Link>
                    </div>

                    <div v-if="invoiceItem.product">
                        <span class="font-semibold">Product: </span>
                        <Link
                            :href="route('products.show', { product: invoiceItem.product.id })"
                            class="text-blue-600 underline"
                        >
                            {{ invoiceItem.product.name }}
                        </Link>
                    </div>

                    <div>
                        <span class="font-semibold">Quantity: </span>
                        <span>{{ invoiceItem.quantity }}</span>
                    </div>

                    <div>
                        <span class="font-semibold">Unit Price: </span>
                        <span>{{ invoiceItem.formatted_unit_price }}</span>
                    </div>

                    <div>
                        <span class="font-semibold">Line Total: </span>
                        <span class="font-semibold">{{ invoiceItem.formatted_line_total }}</span>
                    </div>

                    <div v-if="invoiceItem.creator">
                        <span class="font-semibold">Created By: </span>
                        <span>{{ invoiceItem.creator.name }}</span>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>