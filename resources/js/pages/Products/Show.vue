<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchProduct, deleteProduct } from '@/services/productService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Product {
    id: number
    sku: string
    name: string
    description: string | null
    price: number
    formatted_price: string
    currency: string
    status: 'active' | 'inactive' | 'discontinued'
    quantity: number
    min_stock_level: number | null
    max_stock_level: number | null
    reorder_point: number | null
    reorder_quantity: number | null
    lead_time_days: number | null
    is_active: boolean
    is_discontinued: boolean
    is_low_stock: boolean
    is_out_of_stock: boolean
    creator?: { name: string } | null
    permissions: UserPermissions
}

const props = defineProps<{ product: any }>()

const product = ref<Product>({
    id: props.product.id,
    sku: props.product.sku ?? '',
    name: props.product.name ?? '',
    description: props.product.description ?? null,
    price: props.product.price ?? 0,
    formatted_price: props.product.formatted_price ?? '0.00',
    currency: props.product.currency ?? 'USD',
    status: props.product.status ?? 'active',
    quantity: props.product.quantity ?? 0,
    min_stock_level: props.product.min_stock_level ?? null,
    max_stock_level: props.product.max_stock_level ?? null,
    reorder_point: props.product.reorder_point ?? null,
    reorder_quantity: props.product.reorder_quantity ?? null,
    lead_time_days: props.product.lead_time_days ?? null,
    is_active: props.product.is_active ?? false,
    is_discontinued: props.product.is_discontinued ?? false,
    is_low_stock: props.product.is_low_stock ?? false,
    is_out_of_stock: props.product.is_out_of_stock ?? false,
    creator: props.product.creator ?? null,
    permissions: props.product.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: product.value.name, href: route('products.show', { product: product.value.id }) },
]

async function loadProduct() {
    const data = await fetchProduct(product.value.id)
    Object.assign(product.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this product?')) return
    await deleteProduct(product.value.id)
    window.location.href = route('products.index')
}

function getStatusBadgeClass(status: string): string {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-700'
        case 'inactive':
            return 'bg-yellow-100 text-yellow-700'
        case 'discontinued':
            return 'bg-red-100 text-red-700'
        default:
            return 'bg-gray-100 text-gray-600'
    }
}

onMounted(() => loadProduct())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="View Product" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ product.name }}</h1>
                        <p class="text-sm text-gray-500 font-mono mt-1">SKU: {{ product.sku }}</p>
                        <span
                            :class="getStatusBadgeClass(product.status)"
                            class="mt-2 inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                        >
                            {{ product.status }}
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="product.permissions?.update"
                            :href="route('products.edit', { product: product.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>
                        <Link
                            :href="route('products.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>
                        <button
                            v-if="product.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold border-b pb-2 mb-4">Product Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                            <div>
                                <span class="font-semibold">Price: </span>
                                <span class="text-lg">{{ product.formatted_price }} {{ product.currency }}</span>
                            </div>

                            <div v-if="product.description">
                                <span class="font-semibold">Description: </span>
                                <span>{{ product.description }}</span>
                            </div>

                            <div v-if="product.creator">
                                <span class="font-semibold">Created By: </span>
                                <span>{{ product.creator.name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Information -->
                    <div>
                        <h3 class="text-lg font-semibold border-b pb-2 mb-4">Stock Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                            <div>
                                <span class="font-semibold">Current Stock: </span>
                                <span
                                    :class="{
                                        'text-red-600 font-bold': product.is_out_of_stock,
                                        'text-yellow-600 font-semibold': product.is_low_stock && !product.is_out_of_stock
                                    }"
                                >
                                    {{ product.quantity }}
                                </span>
                                <span v-if="product.is_out_of_stock" class="ml-2 text-xs text-red-600">
                                    (Out of Stock)
                                </span>
                                <span v-else-if="product.is_low_stock" class="ml-2 text-xs text-yellow-600">
                                    (Low Stock)
                                </span>
                            </div>

                            <div v-if="product.min_stock_level !== null">
                                <span class="font-semibold">Min Stock Level: </span>
                                <span>{{ product.min_stock_level }}</span>
                            </div>

                            <div v-if="product.max_stock_level !== null">
                                <span class="font-semibold">Max Stock Level: </span>
                                <span>{{ product.max_stock_level }}</span>
                            </div>

                            <div v-if="product.reorder_point !== null">
                                <span class="font-semibold">Reorder Point: </span>
                                <span>{{ product.reorder_point }}</span>
                            </div>

                            <div v-if="product.reorder_quantity !== null">
                                <span class="font-semibold">Reorder Quantity: </span>
                                <span>{{ product.reorder_quantity }}</span>
                            </div>

                            <div v-if="product.lead_time_days !== null">
                                <span class="font-semibold">Lead Time: </span>
                                <span>{{ product.lead_time_days }} days</span>
                            </div>
                        </div>
                    </div>

                    <!-- Related Resources -->
                    <div>
                        <h3 class="text-lg font-semibold border-b pb-2 mb-4">Related Resources</h3>
                        
                        <div class="flex gap-2">
                            <Link
                                :href="route('products.deals.index', { product: product.id })"
                                class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200"
                            >
                                Deals
                            </Link>
                            <Link
                                :href="route('products.orders.index', { product: product.id })"
                                class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200"
                            >
                                Orders
                            </Link>
                            <Link
                                :href="route('products.quotes.index', { product: product.id })"
                                class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200"
                            >
                                Quotes
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>