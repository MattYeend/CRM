<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { ref, onMounted } from 'vue'
import { fetchProduct, removeProductOrder } from '@/services/productService'

interface Order {
    id: number
    status: string
    currency: string
    pivot?: {
        quantity: number
        price: number
        total: number
    }
}

interface Product {
    id: number
    name: string
    currency: string
    orders: Order[]
}

const props = defineProps<{
    product: Product
}>()

const product = ref<Product>({
    id: props.product.id,
    name: props.product.name,
    currency: props.product.currency ?? 'GBP',
    orders: props.product.orders ?? []
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: props.product.name, href: route('products.show', { product: props.product.id }) },
    { title: 'Orders', href: route('products.orders.index', { product: props.product.id }) },
]

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency,
    }).format(value)
}

async function loadProduct() {
    const data = await fetchProduct(product.value.id)
    product.value.currency = data.currency ?? 'GBP'
    product.value.orders = data.orders ?? []
    product.value.name = data.name
}

async function handleRemove(orderId: number) {
    if (!confirm('Are you sure you want to remove this order from the product?')) return
    await removeProductOrder(props.product.id, orderId)
    window.location.reload()
}

onMounted(() => loadProduct())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`${product.name} - Orders`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Orders for {{ product.name }}</h1>
                <Link
                    :href="route('products.orders.add', { product: product.id })"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Add Order
                </Link>
            </div>

            <table class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Order ID</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-right">Price</th>
                        <th class="p-2 text-right">Total</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in product.orders" :key="order.id" class="border-t">
                        <td class="p-2">
                            <Link
                                :href="route('orders.show', { order: order.id })"
                            >
                                #{{ order.id }}
                            </Link>
                        </td>
                        <td class="p-2 text-right">{{ order.pivot?.quantity }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(order.pivot?.price ?? 0, product.currency) }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(order.pivot?.total ?? 0, product.currency) }}</td>
                        <td class="p-2">{{ order.status }}</td>
                        <td class="p-2 space-x-2 text-right">
                            <Link
                                v-if="order.status !== 'paid'"
                                :href="route('products.orders.edit', { product: product.id, order: order.id })"
                            >
                                Edit
                            </Link>
                            <button
                                @click="handleRemove(order.id)"
                                class="text-red-600"
                            >
                                Remove
                            </button>
                        </td>
                    </tr>

                    <tr v-if="product.orders.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            No orders associated with this product.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>