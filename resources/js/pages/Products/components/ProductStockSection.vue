<script setup lang="ts">
const form = defineModel<any>({ required: true })
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Stock Management</h2>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    Quantity <span class="text-red-500">*</span>
                </label>

                <input
                    v-model="form.quantity"
                    type="number"
                    min="0"
                    step="1"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <p v-if="form.errors?.quantity" class="text-red-500 text-sm mt-1">
                    {{ form.errors.quantity }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Min Stock Level
                </label>

                <input
                    v-model="form.min_stock_level"
                    type="number"
                    min="0"
                    step="1"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <p v-if="form.errors?.min_stock_level" class="text-red-500 text-sm mt-1">
                    {{ form.errors.min_stock_level }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Max Stock Level
                </label>

                <input
                    v-model="form.max_stock_level"
                    type="number"
                    min="0"
                    step="1"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <p v-if="form.errors?.max_stock_level" class="text-red-500 text-sm mt-1">
                    {{ form.errors.max_stock_level }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    Reorder Point
                </label>

                <input
                    v-model="form.reorder_point"
                    type="number"
                    min="0"
                    step="1"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <p v-if="form.errors?.reorder_point" class="text-red-500 text-sm mt-1">
                    {{ form.errors.reorder_point }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Reorder Quantity
                </label>

                <input
                    v-model="form.reorder_quantity"
                    type="number"
                    min="0"
                    step="1"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <p v-if="form.errors?.reorder_quantity" class="text-red-500 text-sm mt-1">
                    {{ form.errors.reorder_quantity }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Lead Time (Days)
                </label>

                <input
                    v-model="form.lead_time_days"
                    type="number"
                    min="0"
                    step="1"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <p v-if="form.errors?.lead_time_days" class="text-red-500 text-sm mt-1">
                    {{ form.errors.lead_time_days }}
                </p>
            </div>
        </div>
    </div>
</template>