<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

interface Deal {
    id: number
    value: number
    currency: string
    close_date: string | null
    company: { id: number; name: string } | null
    owner: { id: number; name: string } | null
    pipeline: { id: number; name: string } | null
    stage: { id: number; name: string } | null
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

defineProps<{ deal: Deal }>()

function formatCurrency(value: number, currency: string): string {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}
</script>

<template>
    <div class="space-y-6 text-sm">
        <!-- Details -->
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
            <div>
                <dt class="font-semibold inline">Value: </dt>
                <dd class="inline">{{ formatCurrency(deal.value, deal.currency) }}</dd>
            </div>
            <div>
                <dt class="font-semibold inline">Close Date: </dt>
                <dd class="inline">
                    <time v-if="deal.close_date" :datetime="deal.close_date">
                        {{ formatDate(deal.close_date) }}
                    </time>
                    <span v-else>—</span>
                </dd>
            </div>
            <div v-if="deal.company">
                <dt class="font-semibold inline">Company: </dt>
                <dd class="inline">
                    <Link
                        :href="route('companies.show', { company: deal.company.id })"
                        class="text-blue-600 hover:underline"
                    >
                        {{ deal.company.name }}
                    </Link>
                </dd>
            </div>
            <div v-if="deal.owner">
                <dt class="font-semibold inline">Owner: </dt>
                <dd class="inline">{{ deal.owner.name }}</dd>
            </div>
            <div v-if="deal.pipeline">
                <dt class="font-semibold inline">Pipeline: </dt>
                <dd class="inline">{{ deal.pipeline.name }}</dd>
            </div>
            <div v-if="deal.stage">
                <dt class="font-semibold inline">Stage: </dt>
                <dd class="inline">{{ deal.stage.name }}</dd>
            </div>
        </dl>

        <!-- Products -->
        <div v-if="deal.products && deal.products.length > 0">
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
                        <tr v-for="product in deal.products" :key="product.id" class="border-t">
                            <td class="p-2">{{ product.name }}</td>
                            <td class="p-2 text-right">{{ product.pivot?.quantity ?? 1 }}</td>
                            <td class="p-2 text-right">
                                {{ formatCurrency(product.pivot?.price ?? 0, deal.currency) }}
                            </td>
                            <td class="p-2 text-right">
                                {{ formatCurrency(product.pivot?.total ?? 0, deal.currency) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Audit Information -->
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-gray-600 pt-2 border-t border-gray-200">
            <div v-if="deal.creator">
                <dt class="font-semibold inline">Created By: </dt>
                <dd class="inline">{{ deal.creator.name }}</dd>
            </div>
            <div v-if="deal.created_at">
                <dt class="font-semibold inline">Created: </dt>
                <dd class="inline">
                    <time :datetime="deal.created_at">
                        {{ formatDate(deal.created_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="deal.updater">
                <dt class="font-semibold inline">Last Updated By: </dt>
                <dd class="inline">{{ deal.updater.name }}</dd>
            </div>
            <div v-if="deal.updated_at">
                <dt class="font-semibold inline">Last Updated: </dt>
                <dd class="inline">
                    <time :datetime="deal.updated_at">
                        {{ formatDate(deal.updated_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="deal.deleter" class="md:col-span-2">
                <dt class="font-semibold inline">Deleted By: </dt>
                <dd class="inline text-red-600">{{ deal.deleter.name }}</dd>
            </div>
        </dl>
    </div>
</template>