<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

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
    status: string
    currency: string
    subtotal: number
    formatted_subtotal: string
    tax: number
    formatted_tax: string
    total: number
    formatted_total: string
    issue_date?: string | null
    due_date?: string | null
    is_overdue: boolean
    company?: { id: number; name: string } | null
    items?: InvoiceItem[]
    creator?: { name: string } | null
}

defineProps<{ invoice: Invoice }>()

function formatDate(date?: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-GB')
}
</script>

<template>
    <div>
        <!-- Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6 text-sm">
            <div v-if="invoice.company">
                <span class="font-semibold">Company: </span>
                <Link
                    :href="route('companies.show', { company: invoice.company.id })"
                    class="text-blue-600"
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
</template>