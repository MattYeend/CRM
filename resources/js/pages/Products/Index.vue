<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchProducts, deleteProduct } from '@/services/productService'

interface Product {
    id: number
    sku: string
    name: string
    description: string | null
    price: number
    formatted_price: string
    currency: string
    status: 'active' | 'discontinued' | 'pending' | 'out_of_stock'
    quantity: number
    is_active: boolean
    is_discontinued: boolean
    is_low_stock: boolean
    is_out_of_stock: boolean
    permissions: UserPermissions
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

const permissions = ref<GlobalPermissions>({
    create: false,
    viewAny: false,
})

const products = ref<Product[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
]

async function loadProducts(page = 1) {
    loading.value = true
    try {
        const data = await fetchProducts(perPage, page)
        products.value = data.data
        permissions.value = data.permissions
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

async function handleDelete(id: number) {
    if (!confirm('Are you sure you want to delete this product?')) return
    await deleteProduct(id)
    loadProducts(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadProducts(page)
    }
}

function getStatusBadgeClass(status: string): string {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-700'
        case 'pending':
            return 'bg-yellow-100 text-yellow-700'
        case 'out_of_stock':
            return 'bg-orange-100 text-orange-700'
        case 'discontinued':
            return 'bg-red-100 text-red-700'
        default:
            return 'bg-gray-100 text-gray-600'
    }
}

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency,
    }).format(value)
}

function formatStatus(status: string): string {
    return status
        .replace(/_/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase())
}

onMounted(() => loadProducts())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Products" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Products</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('products.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <table v-else class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">SKU</th>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-right">Price</th>
                        <th class="p-2 text-right">Stock</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products" :key="product.id" class="border-t">
                        <td class="p-2 font-mono text-sm">{{ product.sku }}</td>
                        <td class="p-2">
                            <Link
                                v-if="product.permissions.view"
                                :href="route('products.show', { product: product.id })"
                            >
                                {{ product.name }}
                            </Link>
                            <span v-else>{{ product.name }}</span>
                        </td>
                        <td class="p-2 text-right font-medium">
                            {{ formatCurrency(product.price, product.currency) }}
                        </td>
                        <td class="p-2 text-right">
                            <span
                                :class="{
                                    'text-red-600 font-semibold': product.is_out_of_stock,
                                    'text-yellow-600': product.is_low_stock && !product.is_out_of_stock
                                }"
                            >
                                {{ product.quantity }}
                            </span>
                        </td>
                        <td class="p-2">
                            <span
                                :class="getStatusBadgeClass(product.status)"
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            >
                                {{ formatStatus(product.status) }}
                            </span>
                        </td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="product.permissions.view"
                                :href="route('products.show', { product: product.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="product.permissions.update"
                                :href="route('products.edit', { product: product.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="product.permissions.delete"
                                @click="handleDelete(product.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="products.length === 0">
                        <td colspan="6" class="p-4 text-center text-gray-500">
                            No products found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    Previous
                </button>

                <button
                    v-for="page in pagination.last_page"
                    :key="page"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-blue-600 text-white': page === currentPage }"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>

                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === pagination.last_page"
                    @click="goToPage(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>