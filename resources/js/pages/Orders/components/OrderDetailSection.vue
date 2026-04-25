<script setup lang="ts">
interface Order {
    status: string
    amount: number
    currency: string
    payment_method?: string | null
    payment_intent_id?: string | null
    charge_id?: string | null
    stripe_payment_intent?: string | null
    stripe_invoice_id?: string | null
    paid_at?: string | null
    user?: { id: number; name: string } | null
    deal?: { id: number; title: string } | null
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

defineProps<{ order: Order }>()

function formatCurrency(value: number, currency: string): string {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

function formatDate(date?: string | null): string {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-GB', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>
    <div class="space-y-6 text-sm">
        <!-- Overview -->
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
            <div>
                <dt class="font-semibold inline">Amount: </dt>
                <dd class="inline">{{ formatCurrency(order.amount, order.currency) }}</dd>
            </div>
            <div>
                <dt class="font-semibold inline">Paid At: </dt>
                <dd class="inline">
                    <time v-if="order.paid_at" :datetime="order.paid_at">
                        {{ formatDate(order.paid_at) }}
                    </time>
                    <span v-else>—</span>
                </dd>
            </div>
            <div>
                <dt class="font-semibold inline">Payment Method: </dt>
                <dd class="inline capitalize">{{ order.payment_method ?? '—' }}</dd>
            </div>
            <div v-if="order.user">
                <dt class="font-semibold inline">User: </dt>
                <dd class="inline">{{ order.user.name }}</dd>
            </div>
            <div v-if="order.deal">
                <dt class="font-semibold inline">Deal: </dt>
                <dd class="inline">{{ order.deal.title }}</dd>
            </div>
        </dl>

        <!-- Payment References -->
        <div v-if="order.payment_intent_id || order.charge_id || order.stripe_payment_intent || order.stripe_invoice_id">
            <h3 class="text-sm font-semibold uppercase tracking-wider mb-3 border-b pb-2">
                Payment References
            </h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                <div v-if="order.payment_intent_id">
                    <dt class="font-medium">Payment Intent ID</dt>
                    <dd>
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                            {{ order.payment_intent_id }}
                        </code>
                    </dd>
                </div>
                <div v-if="order.charge_id">
                    <dt class="font-medium">Charge ID</dt>
                    <dd>
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                            {{ order.charge_id }}
                        </code>
                    </dd>
                </div>
                <div v-if="order.stripe_payment_intent">
                    <dt class="font-medium">Stripe Payment Intent</dt>
                    <dd>
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                            {{ order.stripe_payment_intent }}
                        </code>
                    </dd>
                </div>
                <div v-if="order.stripe_invoice_id">
                    <dt class="font-medium">Stripe Invoice ID</dt>
                    <dd>
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                            {{ order.stripe_invoice_id }}
                        </code>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Products -->
        <div v-if="order.products && order.products.length">
            <h3 class="text-sm font-semibold uppercase tracking-wider mb-3 border-b pb-2">
                Products
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full border text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th scope="col" class="p-2 text-left border-b font-semibold">Product</th>
                            <th scope="col" class="p-2 text-right border-b font-semibold">Qty</th>
                            <th scope="col" class="p-2 text-right border-b font-semibold">Price</th>
                            <th scope="col" class="p-2 text-right border-b font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in order.products" :key="product.id" class="border-t">
                            <td class="p-2">{{ product.name }}</td>
                            <td class="p-2 text-right">{{ product.pivot?.quantity ?? 1 }}</td>
                            <td class="p-2 text-right">
                                {{ formatCurrency(product.pivot?.price ?? 0, order.currency) }}
                            </td>
                            <td class="p-2 text-right">
                                {{ formatCurrency(product.pivot?.total ?? 0, order.currency) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Audit Information -->
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-gray-600 pt-2 border-t border-gray-200">
            <div v-if="order.creator">
                <dt class="font-semibold inline">Created By: </dt>
                <dd class="inline">{{ order.creator.name }}</dd>
            </div>
            <div v-if="order.created_at">
                <dt class="font-semibold inline">Created: </dt>
                <dd class="inline">
                    <time :datetime="order.created_at">
                        {{ formatDate(order.created_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="order.updater">
                <dt class="font-semibold inline">Last Updated By: </dt>
                <dd class="inline">{{ order.updater.name }}</dd>
            </div>
            <div v-if="order.updated_at">
                <dt class="font-semibold inline">Last Updated: </dt>
                <dd class="inline">
                    <time :datetime="order.updated_at">
                        {{ formatDate(order.updated_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="order.deleter" class="md:col-span-2">
                <dt class="font-semibold inline">Deleted By: </dt>
                <dd class="inline text-red-600">{{ order.deleter.name }}</dd>
            </div>
        </dl>
    </div>
</template>