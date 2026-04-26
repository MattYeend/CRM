<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchDealProducts, removeDealProduct } from '@/services/dealService'

interface Product {
    id: number
    name: string
    pivot?: {
        quantity: number
        price: number
        total: number
    }
}

interface Deal {
    id: number
    title: string
    currency: string
    products: Product[]
}

const props = defineProps<{ deal: any }>()

const deal = ref<Deal>({
    id: props.deal.id,
    title: props.deal.title,
    currency: props.deal.currency ?? 'USD',
    products: [],
})

const loading = ref(true)
const currentPage = ref(1)
const perPage = 10

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const visiblePages = computed(() => {
    const total = pagination.last_page
    const current = currentPage.value
    const delta = 2

    const pages: (number | string)[] = []

    const start = Math.max(1, current - delta)
    const end = Math.min(total, current + delta)

    if (start > 1) {
        pages.push(1)
        if (start > 2) pages.push('...')
    }

    for (let i = start; i <= end; i++) pages.push(i)

    if (end < total) {
        if (end < total - 1) pages.push('...')
        pages.push(total)
    }

    return pages
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Deals', href: route('deals.index') },
    { title: deal.value.title || 'Deal', href: route('deals.show', { deal: deal.value.id }) },
    { title: 'Products', href: route('deals.products.index', { deal: deal.value.id }) },
]

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

async function loadDeal(page = 1) {
    loading.value = true
    try {
        const data = await fetchDealProducts(deal.value.id, perPage, page)

        deal.value.products = data.data
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

async function handleRemove(productId: number) {
    if (!confirm('Remove this product from the deal?')) return
    await removeDealProduct(deal.value.id, productId)
    loadDeal(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadDeal(page)
    }
}

onMounted(() => loadDeal())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`${deal.title} — Products`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">{{ deal.title }}</h1>
                    <p class="text-gray-500 text-sm mt-0.5">Deal Products</p>
                </div>

                <div class="flex items-center space-x-2">
                    <Link
                        :href="route('deals.products.add', { deal: deal.id })"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        Add Products
                    </Link>

                    <Link
                        :href="route('deals.show', { deal: deal.id })"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                    >
                        Back to Deal
                    </Link>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <!-- Table -->
            <table v-else class="w-full border">
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
                    <tr
                        v-for="product in deal.products"
                        :key="product.id"
                        class="border-t"
                    >
                        <td class="p-2">{{ product.name }}</td>
                        <td class="p-2 text-right">{{ product.pivot?.quantity ?? 1 }}</td>
                        <td class="p-2 text-right">
                            {{ formatCurrency(product.pivot?.price ?? 0, deal.currency) }}
                        </td>
                        <td class="p-2 text-right">
                            {{ formatCurrency(product.pivot?.total ?? 0, deal.currency) }}
                        </td>
                        <td class="p-2 space-x-2 whitespace-nowrap text-right">
                            <Link
                                :href="route('deals.products.edit', { deal: deal.id, product: product.id })"
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

                    <tr v-if="deal.products.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500">
                            No products on this deal yet.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div
                v-if="pagination.last_page > 1"
                class="flex justify-center mt-4 space-x-2"
            >
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    Previous
                </button>

                <button
                    v-for="page in visiblePages"
                    :key="page"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-blue-600 text-white': page === currentPage }"
                    :disabled="page === '...'"
                    @click="typeof page === 'number' && goToPage(page)"
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