<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'

import { 
    createProductStockMovement,
    fetchProductStockMovements
 } from '@/services/productService'

import StockTable from '@/components/StockTable.vue'
import StockSummaryCards from '@/components/StockSummaryCards.vue'
import CreateMovementModal from '@/components/CreateMovementModal.vue'

const props = defineProps<{
    product: any
}>()

const movements = ref<any[]>([])
const total = ref(0)
const permissions = ref<{ create: boolean }>({ create: false })

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Product Stock', href: route('products.stock.index') },
    {
        title: props.product.name,
        href: route('products.stock.show', { product: props.product.id }),
    },
]

async function reload() {
    try {
        const data = await fetchProductStockMovements(props.product.id)

        movements.value = data?.data ?? []
        total.value = data?.total ?? 0
        permissions.value = data?.permissions ?? { create: false }
    } catch (error) {
        console.error('Failed to load stock movements:', error)

        movements.value = []
        total.value = 0
        permissions.value = { create: false }
    }
}

onMounted(() => {
    reload()
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Stock — ${product.name}`" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">
                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">Stock</h1>
                        <p class="text-gray-600 text-sm">
                            {{ product.name }} · {{ product.sku }}
                        </p>
                    </div>

                    <Link
                        :href="route('products.show', { product: product.id })"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                    >
                        Back
                    </Link>
                </div>

                <StockSummaryCards
                    :quantity="product.quantity"
                    :reorderPoint="product.reorder_point"
                    :totalMovements="total"
                />

                <CreateMovementModal
                    :entityId="product.id"
                    :createMovement="createProductStockMovement"
                    @created="reload"
                />

                <StockTable :movements="movements" />
            </div>
        </div>
    </AppLayout>
</template>