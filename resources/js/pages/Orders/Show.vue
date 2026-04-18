<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchOrder, deleteOrders } from '@/services/orderService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Order {
    id: number
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
    permissions: UserPermissions
}

const statusClasses: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    processing: 'bg-blue-100 text-blue-700',
    paid: 'bg-green-100 text-green-700',
    failed: 'bg-red-100 text-red-700',
    refunded: 'bg-purple-100 text-purple-700',
    cancelled: 'bg-gray-100 text-gray-600',
}

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

const props = defineProps<{ order: any }>()

const order = ref<Order>({
    id: props.order.id,
    status: props.order.status ?? 'pending',
    amount: props.order.amount ?? 0,
    currency: props.order.currency ?? 'GBP',
    payment_method: props.order.payment_method,
    payment_intent_id: props.order.payment_intent_id,
    charge_id: props.order.charge_id,
    stripe_payment_intent: props.order.stripe_payment_intent,
    stripe_invoice_id: props.order.stripe_invoice_id,
    paid_at: props.order.paid_at,
    user: props.order.user,
    deal: props.order.deal,
    products: props.order.products ?? [],
    creator: props.order.creator,
    permissions: props.order.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Orders', href: route('orders.index') },
    { title: `Order #${order.value.id}`, href: route('orders.show', { order: order.value.id }) },
]

async function loadOrder() {
    const data = await fetchOrder(order.value.id)
    Object.assign(order.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this order?')) return
    await deleteOrders(order.value.id)
    window.location.href = route('orders.index')
}

onMounted(() => loadOrder())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Order #${order.id}`" />
        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">Order #{{ order.id }}</h1>
                        <span
                            class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                            :class="statusClasses[order.status] ?? 'bg-gray-100 text-gray-600'"
                        >
                            {{ order.status }}
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="order.permissions?.update"
                            :href="route('orders.edit', { order: order.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>
                        <Link
                            :href="route('orders.products.index', { order: order.id })"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Products
                        </Link>
                        <Link
                            :href="route('orders.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>
                        <button
                            v-if="order.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6">
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
                        <Link
                            :href="route('deals.show', { deal: order.deal.id })"
                            class="text-blue-600 underline"
                        >
                            {{ order.deal.title }}
                        </Link>
                    </div>
                    <div v-if="order.creator">
                        <span class="font-semibold">Created By: </span>
                        <span>{{ order.creator.name }}</span>
                    </div>
                </div>

                <!-- Payment References -->
                <div v-if="order.payment_intent_id || order.charge_id || order.stripe_payment_intent || order.stripe_invoice_id" class="mb-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-3">Payment References</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                        <div v-if="order.payment_intent_id">
                            <span class="font-semibold">Payment Intent ID: </span>
                            <code class="px-1 rounded">{{ order.payment_intent_id }}</code>
                        </div>
                        <div v-if="order.charge_id">
                            <span class="font-semibold">Charge ID: </span>
                            <code class="px-1 rounded">{{ order.charge_id }}</code>
                        </div>
                        <div v-if="order.stripe_payment_intent">
                            <span class="font-semibold">Stripe Payment Intent: </span>
                            <code class="px-1 rounded">{{ order.stripe_payment_intent }}</code>
                        </div>
                        <div v-if="order.stripe_invoice_id">
                            <span class="font-semibold">Stripe Invoice ID: </span>
                            <code class="px-1 rounded">{{ order.stripe_invoice_id }}</code>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div v-if="order.products && order.products.length > 0">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-3">Products</h2>
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
                                <td class="p-2 text-right">{{ formatCurrency(product.pivot?.price ?? 0, order.currency) }}</td>
                                <td class="p-2 text-right">{{ formatCurrency(product.pivot?.total ?? 0, order.currency) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AppLayout>
</template>