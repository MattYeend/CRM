<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchOrder, deleteOrders } from '@/services/orderService'
import OrderDetailSection from './components/OrderDetailSection.vue'

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
    creator: { name: string } | null
    updater: { name: string } | null
    deleter: { name: string } | null
    created_at: string | null
    updated_at: string | null
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
    creator: props.order.creator ?? null,
    updater: props.order.updater ?? null,
    deleter: props.order.deleter ?? null,
    created_at: props.order.created_at ?? null,
    updated_at: props.order.updated_at ?? null,
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

                    <div class="flex items-center gap-2">
                        <Link
                            v-if="order.permissions?.update"
                            :href="route('orders.edit', { order: order.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('orders.products.index', { order: order.id })"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded"
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

                <OrderDetailSection :order="order" />
            </div>
        </div>
    </AppLayout>
</template>