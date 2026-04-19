<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    description: string
    quantity: number
    unitPrice: number
    errors: {
        description?: string
        quantity?: string
        unit_price?: string
    }
}>()

const emit = defineEmits<{
    'update:description': [value: string]
    'update:quantity': [value: number]
    'update:unitPrice': [value: number]
}>()

const descriptionModel = computed({
    get: () => props.description,
    set: (value: string) => emit('update:description', value)
})

const quantityModel = computed({
    get: () => props.quantity,
    set: (value: number) => emit('update:quantity', value)
})

const unitPriceModel = computed({
    get: () => props.unitPrice,
    set: (value: number) => emit('update:unitPrice', value)
})

const lineTotal = computed(() => {
    return (Number(props.quantity) * Number(props.unitPrice)).toFixed(2)
})
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Line Item Details</h2>

        <div>
            <label class="block text-sm font-medium mb-1">
                Description <span class="text-red-500">*</span>
            </label>
            <input
                v-model="descriptionModel"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Line item description"
            />
            <p v-if="errors.description" class="text-red-500 text-sm mt-1">
                {{ errors.description }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    Quantity <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="quantityModel"
                    type="number"
                    min="1"
                    step="1"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="errors.quantity" class="text-red-500 text-sm mt-1">
                    {{ errors.quantity }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Unit Price <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="unitPriceModel"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="errors.unit_price" class="text-red-500 text-sm mt-1">
                    {{ errors.unit_price }}
                </p>
            </div>
        </div>

        <!-- Computed line total preview -->
        <div class="bg-gray-50 border rounded px-3 py-2 text-sm text-gray-600">
            Line Total:
            <span class="font-semibold text-gray-900">
                {{ lineTotal }}
            </span>
        </div>
    </div>
</template>