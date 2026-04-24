<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { route } from 'ziggy-js'

import {
    createPartStockMovement,
    fetchPartStockMovements
} from '@/services/partService'

import StockTable from '@/components/StockTable.vue'
import StockSummaryCards from '@/components/StockSummaryCards.vue'
import CreateMovementModal from '@/components/CreateMovementModal.vue'

const props = defineProps<{
    part: any
    movements: any
}>()

const movements = ref(props.movements.data)
const total = ref(props.movements.total)
const permissions = ref(props.movements.permissions)

async function reload() {
    try{
        const data = await fetchPartStockMovements(props.part.id)

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
</script>

<template>
    <AppLayout>
        <Head :title="`Stock — ${part.name}`" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">
                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">Stock</h1>
                        <p class="text-gray-600 text-sm">
                            {{ part.name }} · {{ part.sku }}
                        </p>
                    </div>

                    <Link
                        :href="route('parts.show', { part: part.id })"
                        class="bg-gray-100 text-gray-700 px-4 py-2 rounded"
                    >
                        Back
                    </Link>
                </div>

                <StockSummaryCards
                    :quantity="part.quantity"
                    :reorderPoint="part.reorder_point"
                    :totalMovements="total"
                />

                <CreateMovementModal
                    :entityId="part.id"
                    :createMovement="createPartStockMovement"
                    @created="reload"
                />

                <StockTable :movements="movements" />
            </div>
        </div>
    </AppLayout>
</template>