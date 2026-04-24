<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { route } from 'ziggy-js'

import {
    fetchPartStockMovements
} from '@/services/partService'

import StockTable from './components/StockTable.vue'
import StockSummaryCards from './components/StockSummaryCards.vue'
import CreateMovementModal from './components/CreateMovementModal.vue'

const props = defineProps<{
    part: any
    movements: any
}>()

const movements = ref(props.movements.data)
const total = ref(props.movements.total)
const permissions = ref(props.movements.permissions)

async function reload() {
    const data = await fetchPartStockMovements(props.part.id)
    movements.value = data.data
    total.value = data.total
}
</script>

<template>
    <AppLayout>
        <Head :title="`Stock — ${part.name}`" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">Stock</h1>
                        <p class="text-gray-600 text-sm">{{ part.name }} · {{ part.sku }}</p>
                    </div>

                    <Link
                        :href="route('parts.show', { part: part.id })"
                        class="bg-gray-100 text-gray-700 px-4 py-2 rounded"
                    >
                        Back
                    </Link>
                </div>
            </div>

            <StockSummaryCards
                :quantity="part.quantity"
                :reorderPoint="part.reorder_point"
                :totalMovements="total"
            />

            <CreateMovementModal
                v-if="permissions.create"
                :partId="part.id"
                @created="reload"
            />

            <StockTable :movements="movements" />

        </div>
    </AppLayout>
</template>