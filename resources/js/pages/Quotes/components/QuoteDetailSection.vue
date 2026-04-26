<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

interface Quote {
    deal?: { id: number; title: string } | null
    currency: string
    formatted_subtotal: string
    formatted_tax: string
    formatted_total: string
    sent_at?: string | null
    accepted_at?: string | null
    products?: Array<{
        id: number
        name: string
        pivot?: { quantity: number; price: number; total: number }
    }>
    creator: { name: string } | null
    updater: { name: string } | null
    deleter: { name: string } | null
    created_at: string | null
    updated_at: string | null
}

defineProps<{ quote: Quote }>()

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}

function formatCurrency(value: number, currency: string): string {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}
</script>

<template>
    <div class="space-y-6">
        <!-- Quote Details -->
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div v-if="quote.deal">
                <dt class="font-semibold">Deal</dt>
                <dd>
                    <Link
                        :href="route('deals.show', { deal: quote.deal.id })"
                        class="text-blue-600 hover:underline"
                    >
                        {{ quote.deal.title }}
                    </Link>
                </dd>
            </div>

            <div>
                <dt class="font-semibold">Currency</dt>
                <dd>{{ quote.currency }}</dd>
            </div>

            <div>
                <dt class="font-semibold">Subtotal</dt>
                <dd>{{ quote.formatted_subtotal }}</dd>
            </div>

            <div>
                <dt class="font-semibold">Tax</dt>
                <dd>{{ quote.formatted_tax }}</dd>
            </div>

            <div>
                <dt class="font-semibold">Total</dt>
                <dd class="font-bold">{{ quote.formatted_total }}</dd>
            </div>

            <div v-if="quote.sent_at">
                <dt class="font-semibold">Sent At</dt>
                <dd>
                    <time :datetime="quote.sent_at">
                        {{ formatDate(quote.sent_at) }}
                    </time>
                </dd>
            </div>

            <div v-if="quote.accepted_at">
                <dt class="font-semibold">Accepted At</dt>
                <dd>
                    <time :datetime="quote.accepted_at">
                        {{ formatDate(quote.accepted_at) }}
                    </time>
                </dd>
            </div>
        </dl>

        <!-- Products Table -->
        <div v-if="quote.products && quote.products.length > 0">
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">Products</h2>
            <div class="overflow-x-auto">
                <table class="w-full border text-sm">
                    <thead>
                        <tr>
                            <th scope="col" class="p-2 text-left border-b font-semibold">Product</th>
                            <th scope="col" class="p-2 text-right border-b font-semibold">Qty</th>
                            <th scope="col" class="p-2 text-right border-b font-semibold">Price</th>
                            <th scope="col" class="p-2 text-right border-b font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="product in quote.products"
                            :key="product.id"
                            class="border-t"
                        >
                            <td class="p-2">{{ product.name }}</td>
                            <td class="p-2 text-right">
                                {{ product.pivot?.quantity ?? 1 }}
                            </td>
                            <td class="p-2 text-right">
                                {{ formatCurrency(product.pivot?.price ?? 0, quote.currency) }}
                            </td>
                            <td class="p-2 text-right">
                                {{ formatCurrency(product.pivot?.total ?? 0, quote.currency) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Audit Information -->
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-gray-600 pt-2 border-t border-gray-200">
            <div v-if="quote.creator">
                <dt class="font-semibold inline">Created By: </dt>
                <dd class="inline">{{ quote.creator.name }}</dd>
            </div>
            <div v-if="quote.created_at">
                <dt class="font-semibold inline">Created: </dt>
                <dd class="inline">
                    <time :datetime="quote.created_at">
                        {{ formatDate(quote.created_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="quote.updater">
                <dt class="font-semibold inline">Last Updated By: </dt>
                <dd class="inline">{{ quote.updater.name }}</dd>
            </div>
            <div v-if="quote.updated_at">
                <dt class="font-semibold inline">Last Updated: </dt>
                <dd class="inline">
                    <time :datetime="quote.updated_at">
                        {{ formatDate(quote.updated_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="quote.deleter" class="md:col-span-2">
                <dt class="font-semibold inline">Deleted By: </dt>
                <dd class="inline text-red-600">{{ quote.deleter.name }}</dd>
            </div>
        </dl>
    </div>
</template>