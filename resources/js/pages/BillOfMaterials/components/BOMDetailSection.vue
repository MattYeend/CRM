<script setup lang="ts">
interface ChildPart {
    id: number
    name: string
    sku: string
    quantity: number
    unit_of_measure?: string
}

interface BOM {
    id: number
    parent_part_id: number
    child_part_id: number
    quantity: number
    unit_of_measure?: string
    scrap_percentage?: number
    notes?: string
    child_part?: ChildPart
    creator?: { name: string }
}

defineProps<{ bom: BOM }>()
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm">
        <!-- Component details -->
        <div class="space-y-3">
            <h3 class="text-sm font-semibold uppercase tracking-wider">
                Component
            </h3>
            <dl class="space-y-2">
                <div v-if="bom.child_part?.sku" class="flex justify-between">
                    <dt class="font-medium">SKU</dt>
                    <dd>{{ bom.child_part.sku }}</dd>
                </div>
                <div v-if="bom.child_part?.name" class="flex justify-between">
                    <dt class="font-medium">Name</dt>
                    <dd>{{ bom.child_part.name }}</dd>
                </div>
                <div v-if="bom.child_part?.unit_of_measure" class="flex justify-between">
                    <dt class="font-medium">Part UoM</dt>
                    <dd>{{ bom.child_part.unit_of_measure }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium">Stock on Hand</dt>
                    <dd class="tabular-nums">{{ bom.child_part?.quantity ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <!-- BOM details -->
        <div class="space-y-3">
            <h3 class="text-sm font-semibold uppercase tracking-wider">
                BOM Entry
            </h3>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="font-medium">Quantity Required</dt>
                    <dd class="tabular-nums">{{ bom.quantity }}</dd>
                </div>
                <div v-if="bom.unit_of_measure" class="flex justify-between">
                    <dt class="font-medium">Unit of Measure</dt>
                    <dd>{{ bom.unit_of_measure }}</dd>
                </div>
                <div v-if="bom.scrap_percentage != null" class="flex justify-between">
                    <dt class="font-medium">Scrap %</dt>
                    <dd class="tabular-nums">{{ bom.scrap_percentage }}%</dd>
                </div>
                <div v-if="bom.creator" class="flex justify-between">
                    <dt class="font-medium">Created By</dt>
                    <dd>{{ bom.creator.name }}</dd>
                </div>
            </dl>
        </div>

        <!-- Notes -->
        <div v-if="bom.notes" class="md:col-span-2 space-y-2">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Notes</h3>
            <p class="text-gray-700 whitespace-pre-wrap">{{ bom.notes }}</p>
        </div>
    </div>
</template>