<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    quantity: number
    reorderPoint?: number
    totalMovements: number
}>()

const isLowStock = computed(() =>
    props.reorderPoint != null &&
    props.quantity <= props.reorderPoint
)
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-3 mb-6">
        <div class="border p-4 rounded">
            <p class="text-sm mb-1">Current Stock</p>
            <p 
                class="text-3xl font-semibold" 
                :class="isLowStock ? 'text-red-600' : 'text-gray-300'"
            >
                {{ quantity }}
            </p>
            <p v-if="isLowStock" class="text-xs text-red-600 mt-1">Low Stock Alert</p>
        </div>

        <div class="border p-4 rounded">
            <p class="text-sm mb-1">Reorder Point</p>
            <p class="text-3xl font-semibold text-gray-300">{{ reorderPoint ?? '—' }}</p>
        </div>

        <div class="border p-4 rounded">
            <p class="text-sm mb-1">Total Movements</p>
            <p class="text-3xl font-semibold text-gray-300">{{ totalMovements }}</p>
        </div>
    </div>
</template>