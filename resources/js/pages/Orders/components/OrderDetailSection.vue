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
    creator?: { name: string }
}

defineProps<{ order: Order }>()

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

function formatDate(date?: string | null) {
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
    <div class="space-y-6">

        <!-- Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
            <div>
                <span class="font-semibold">Amount: </span>
                <span>{{ formatCurrency(order.amount, order.currency) }}</span>
            </div>
            <div>
                <span class="font-semibold">Paid At: </span>
                <span>{{ formatDate(order.paid_at) }}</span>
            </div>
            <div>
                <span class="font-semibold">Payment Method: </span>
                <span class="capitalize">{{ order.payment_method ?? '—' }}</span>
            </div>
            <div v-if="order.user">
                <span class="font-semibold">User: </span>
                <span>{{ order.user.name }}</span>
            </div>
            <div v-if="order.deal">
                <span class="font-semibold">Deal: </span>
                <span>{{ order.deal.title }}</span>
            </div>
            <div v-if="order.creator">
                <span class="font-semibold">Created By: </span>
                <span>{{ order.creator.name }}</span>
            </div>
        </div>

        <!-- Payment References -->
        <div v-if="order.payment_intent_id || order.charge_id || order.stripe_payment_intent || order.stripe_invoice_id">
            <h3 class="text-sm font-semibold uppercase tracking-wider mb-2">Payment References</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <div v-if="order.payment_intent_id">
                    <span class="font-medium">Payment Intent ID</span>
                    <code class="block">{{ order.payment_intent_id }}</code>
                </div>
                <div v-if="order.charge_id">
                    <span class="font-medium">Charge ID</span>
                    <code class="block">{{ order.charge_id }}</code>
                </div>
                <div v-if="order.stripe_payment_intent">
                    <span class="font-medium">Stripe Payment Intent</span>
                    <code class="block">{{ order.stripe_payment_intent }}</code>
                </div>
                <div v-if="order.stripe_invoice_id">
                    <span class="font-medium">Stripe Invoice ID</span>
                    <code class="block">{{ order.stripe_invoice_id }}</code>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div v-if="order.products && order.products.length">
            <h3 class="text-sm font-semibold uppercase tracking-wider mb-2">Products</h3>
            <table class="w-full border text-sm">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Product</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-right">Price</th>
                        <th class="p-2 text-right">Total</th>
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
</template>