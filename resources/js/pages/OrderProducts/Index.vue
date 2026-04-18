<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchOrder, removeOrderProduct } from '@/services/orderService'

interface Product {
    id: number
    name: string
    pivot?: {
        quantity: number
        price: number
        total: number
    }
}

interface Order {
    id: number
    currency: string
    products: Product[]
}

const props = defineProps<{ order: any }>()

const order = ref<Order>({
    id: props.order.id,
    currency: props.order.currency ?? 'GBP',
    products: props.order.products ?? [],
})

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Orders', href: route('orders.index') },
    { title: `Order #${order.value.id}`, href: route('orders.show', { order: order.value.id }) },
    { title: 'Products', href: route('orders.products.index', { order: order.value.id }) },
]

async function loadOrder() {
    const data = await fetchOrder(order.value.id)
    order.value.currency = data.currency ?? 'GBP'
    order.value.products = data.products ?? []
}

async function handleRemove(productId: number) {
    if (!confirm('Remove this product from the order?')) return
    await removeOrderProduct(order.value.id, productId)
    loadOrder()
}

onMounted(() => loadOrder())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Order #${order.id} — Products`" />
        <div class="p-6">
            <div class="flex justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Order #{{ order.id }}</h1>
                    <p class="text-gray-500 text-sm mt-0.5">Order Products</p>
                </div>
                <div class="flex items-center space-x-2">
                    <Link
                        :href="route('orders.products.add', { order: order.id })"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        Add Products
                    </Link>
                    <Link
                        :href="route('orders.show', { order: order.id })"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                    >
                        Back to Order
                    </Link>
                </div>
            </div>

            <table class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Product</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-right">Unit Price</th>
                        <th class="p-2 text-right">Total</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in order.products" :key="product.id" class="border-t">
                        <td class="p-2">{{ product.name }}</td>
                        <td class="p-2 text-right">{{ product.pivot?.quantity ?? 1 }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(product.pivot?.price ?? 0, order.currency) }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(product.pivot?.total ?? 0, order.currency) }}</td>
                        <td class="p-2 space-x-2 whitespace-nowrap text-right">
                            <Link
                                :href="route('orders.products.edit', { order: order.id, product: product.id })"
                                class="text-blue-600"
                            >
                                Edit
                            </Link>
                            <button
                                @click="handleRemove(product.id)"
                                class="text-red-600"
                            >
                                Remove
                            </button>
                        </td>
                    </tr>

                    <tr v-if="order.products.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500">
                            No products on this order yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>