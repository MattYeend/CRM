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
    <div class="grid grid-cols-3 gap-4">
        <div class="border p-4">
            <p class="text-xs">Stock</p>
            <p class="text-3xl" :class="isLowStock ? 'text-red-600' : ''">
                {{ quantity }}
            </p>
        </div>

        <div class="border p-4">
            <p class="text-xs">Reorder</p>
            <p class="text-3xl">{{ reorderPoint ?? '—' }}</p>
        </div>

        <div class="border p-4">
            <p class="text-xs">Movements</p>
            <p class="text-3xl">{{ totalMovements }}</p>
        </div>
    </div>
</template>