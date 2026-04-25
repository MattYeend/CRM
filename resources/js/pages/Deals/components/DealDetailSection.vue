<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

interface Deal {
    id: number
    value: number
    currency: string
    close_date?: string | null
    company?: { id: number; name: string } | null
    owner?: { id: number; name: string } | null
    pipeline?: { id: number; name: string } | null
    stage?: { id: number; name: string } | null
    products?: Array<{
        id: number
        name: string
        pivot?: { quantity: number; price: number; total: number }
    }>
    creator?: { name: string }
}

defineProps<{ deal: Deal }>()

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

function formatDate(date?: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-GB')
}
</script>

<template>
    <div class="space-y-6">
        <!-- Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
            <div>
                <span class="font-semibold">Value: </span>
                <span>{{ formatCurrency(deal.value, deal.currency) }}</span>
            </div>
            <div>
                <span class="font-semibold">Close Date: </span>
                <span>{{ formatDate(deal.close_date) }}</span>
            </div>
            <div v-if="deal.company">
                <span class="font-semibold">Company: </span>
                <Link
                    :href="route('companies.show', { company: deal.company.id })"
                    class="text-blue-600"
                >
                    {{ deal.company.name }}
                </Link>
            </div>
            <div v-if="deal.owner">
                <span class="font-semibold">Owner: </span>
                <span>{{ deal.owner.name }}</span>
            </div>
            <div v-if="deal.pipeline">
                <span class="font-semibold">Pipeline: </span>
                <span>{{ deal.pipeline.name }}</span>
            </div>
            <div v-if="deal.stage">
                <span class="font-semibold">Stage: </span>
                <span>{{ deal.stage.name }}</span>
            </div>
            <div v-if="deal.creator">
                <span class="font-semibold">Created By: </span>
                <span>{{ deal.creator.name }}</span>
            </div>
        </div>

        <!-- Products -->
        <div v-if="deal.products && deal.products.length > 0">
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">Products</h2>
            <table class="w-full border text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="p-2 text-left border-b">Product</th>
                        <th class="p-2 text-right border-b">Qty</th>
                        <th class="p-2 text-right border-b">Price</th>
                        <th class="p-2 text-right border-b">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in deal.products" :key="product.id" class="border-t">
                        <td class="p-2">{{ product.name }}</td>
                        <td class="p-2 text-right">{{ product.pivot?.quantity ?? 1 }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(product.pivot?.price ?? 0, deal.currency) }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(product.pivot?.total ?? 0, deal.currency) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>