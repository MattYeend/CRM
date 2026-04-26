<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps<{
    products: {
        data: any[]
        current_page: number
        last_page: number
        total: number
    }
}>()

const currentPage = ref(props.products.current_page)

const pagination = reactive({
    current_page: props.products.current_page,
    last_page: props.products.last_page,
    total: props.products.total,
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

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        window.location.href = route('products.stock.low', { page })
    }
}
</script>

<template>
    <AppLayout>
        <Head title="Low Stock Parts" />

        <div class="p-6 space-y-6">
            <h1 class="text-2xl font-bold text-red-600">Low Stock</h1>

            <table class="w-full border text-sm">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">SKU</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-right">Reorder</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="product in products.data" :key="product.id" class="border-t">
                        <td class="p-2">
                            <Link :href="route('products.stock.show', { product: product.id })">
                                {{ product.name }}
                            </Link>
                        </td>
                        <td class="p-2">{{ product.sku }}</td>
                        <td class="p-2 text-right text-red-600 font-bold">
                            {{ product.quantity }}
                        </td>
                        <td class="p-2 text-right">{{ product.reorder_point }}</td>
                        <td class="p-2 whitespace-nowrap">
                            <Link :href="route('products.stock.show', { product: product.id })">
                                View
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded"
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
                    class="px-3 py-1 border rounded"
                    :disabled="currentPage === pagination.last_page"
                    @click="goToPage(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>