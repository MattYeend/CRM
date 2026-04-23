<script setup lang="ts">
interface PartForm {
    quantity: number
    min_stock_level: number | null
    max_stock_level: number | null
    reorder_point: number | null
    reorder_quantity: number | null
    lead_time_days: number | null
    warehouse_location: string
    bin_location: string
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{ form: PartForm }>()
const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Stock</h2>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Quantity</label>
                <input
                    v-model="form.quantity"
                    type="number"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0"
                />
                <p v-if="form.errors.quantity" class="text-red-500 text-sm mt-1">
                    {{ form.errors.quantity }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Min Stock Level</label>
                <input
                    v-model="form.min_stock_level"
                    type="number"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0"
                />
                <p v-if="form.errors.min_stock_level" class="text-red-500 text-sm mt-1">
                    {{ form.errors.min_stock_level }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Max Stock Level</label>
                <input
                    v-model="form.max_stock_level"
                    type="number"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0"
                />
                <p v-if="form.errors.max_stock_level" class="text-red-500 text-sm mt-1">
                    {{ form.errors.max_stock_level }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Reorder Point</label>
                <input
                    v-model="form.reorder_point"
                    type="number"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0"
                />
                <p v-if="form.errors.reorder_point" class="text-red-500 text-sm mt-1">
                    {{ form.errors.reorder_point }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Reorder Quantity</label>
                <input
                    v-model="form.reorder_quantity"
                    type="number"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0"
                />
                <p v-if="form.errors.reorder_quantity" class="text-red-500 text-sm mt-1">
                    {{ form.errors.reorder_quantity }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Lead Time (days)</label>
                <input
                    v-model="form.lead_time_days"
                    type="number"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0"
                />
                <p v-if="form.errors.lead_time_days" class="text-red-500 text-sm mt-1">
                    {{ form.errors.lead_time_days }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Warehouse Location</label>
                <input
                    v-model="form.warehouse_location"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Warehouse A"
                />
                <p v-if="form.errors.warehouse_location" class="text-red-500 text-sm mt-1">
                    {{ form.errors.warehouse_location }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Bin Location</label>
                <input
                    v-model="form.bin_location"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="A1-B2"
                />
                <p v-if="form.errors.bin_location" class="text-red-500 text-sm mt-1">
                    {{ form.errors.bin_location }}
                </p>
            </div>
        </div>
    </div>
</template>