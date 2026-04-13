<script setup lang="ts">
const props = defineProps<{ 
    form: any 
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Financials</h2>
 
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Subtotal</label>
                <input
                    v-model="form.subtotal"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
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
                    v-model="form.total"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors.total" class="text-red-500 text-sm mt-1">{{ form.errors.total }}</p>
            </div>
        </div>
    </div>
</template>