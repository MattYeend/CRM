<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

interface InvoiceItem {
    invoice?: { id: number } | null
    product?: { id: number; name: string } | null
    quantity: number
    formatted_unit_price: string
    formatted_line_total: string
    creator?: { name: string } | null
}

defineProps<{ invoiceItem: InvoiceItem }>()
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
        <div v-if="invoiceItem.invoice">
            <span class="font-semibold">Invoice: </span>
            <Link
                :href="route('invoices.show', { invoice: invoiceItem.invoice.id })"
                class="text-blue-600"
            >
                #{{ invoiceItem.invoice.id }}
            </Link>
        </div>

        <div v-if="invoiceItem.product">
            <span class="font-semibold">Product: </span>
            <Link
                :href="route('products.show', { product: invoiceItem.product.id })"
                class="text-blue-600"
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
</template>