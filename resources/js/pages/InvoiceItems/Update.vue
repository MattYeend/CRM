<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import InvoiceItemForm from './components/InvoiceItemForm.vue'
import { route } from 'ziggy-js'

interface SelectOption {
    id: number
    name: string
}

interface InvoiceSelectOption {
    id: number
}

const props = defineProps<{
    invoiceItem: any
    invoices: InvoiceSelectOption[]
    products: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Invoice Items', href: route('invoice-items.index') },
    { title: `Invoice Item #${props.invoiceItem.id}`, href: route('invoice-items.show', { invoiceItem: props.invoiceItem.id }) },
    { title: `Edit Invoice Item #${props.invoiceItem.id}`, href: route('invoice-items.edit', { invoiceItem: props.invoiceItem.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Invoice Item" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Invoice Item</h1>
            <InvoiceItemForm
                :invoice-item="invoiceItem"
                :invoices="invoices"
                :products="products"
                :submit-route="`/api/invoice-items/${invoiceItem.id}`"
                method="put"
                submitLabel="Update Invoice Item"
            />
        </div>
    </AppLayout>
</template>