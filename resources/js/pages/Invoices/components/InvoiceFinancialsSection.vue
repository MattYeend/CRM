<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    form: any
}>()

const form = props.form

const total = computed(() => {
    const subtotal = parseFloat(form.subtotal) || 0
    const tax = parseFloat(form.tax) || 0
    return (subtotal + tax).toFixed(2)
})
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Financials</h2>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Subtotal</label>
                <input
                    v-if="form.items?.length"
                    :value="parseFloat(form.subtotal || 0).toFixed(2)"
                    type="number"
                    disabled
                    class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed text-gray-500"
                />
                <input
                    v-else
                    v-model="form.subtotal"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p class="text-xs text-gray-400 mt-1">
                    {{ form.items?.length ? 'Calculated from line items' : 'Enter subtotal manually' }}
                </p>
                <p v-if="form.errors.subtotal" class="text-red-500 text-sm mt-1">{{ form.errors.subtotal }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tax</label>
                <input
                    v-model="form.tax"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors.tax" class="text-red-500 text-sm mt-1">{{ form.errors.tax }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Total</label>
                <input
                    :value="total"
                    type="number"
                    disabled
                    class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed text-gray-500"
                />
                <p class="text-xs text-gray-400 mt-1">Subtotal + tax</p>
            </div>
        </div>
    </div>
</template>