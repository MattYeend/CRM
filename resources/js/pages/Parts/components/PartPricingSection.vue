<script setup lang="ts">
interface PartForm {
    price: number | null
    cost_price: number | null
    currency: string
    tax_rate: number | null
    tax_code: string
    discount_percentage: number | null
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{ form: PartForm }>()
const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Pricing</h2>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Price</label>
                <input
                    v-model="form.price"
                    type="number"
                    step="0.01"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0.00"
                />
                <p v-if="form.errors.price" class="text-red-500 text-sm mt-1">
                    {{ form.errors.price }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Cost Price</label>
                <input
                    v-model="form.cost_price"
                    type="number"
                    step="0.01"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0.00"
                />
                <p v-if="form.errors.cost_price" class="text-red-500 text-sm mt-1">
                    {{ form.errors.cost_price }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Currency</label>
                <input
                    v-model="form.currency"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="GBP"
                />
                <p v-if="form.errors.currency" class="text-red-500 text-sm mt-1">
                    {{ form.errors.currency }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tax Rate (%)</label>
                <input
                    v-model="form.tax_rate"
                    type="number"
                    step="0.01"
                    class="w-full border rounded px-3 py-2"
                    placeholder="20.00"
                />
                <p v-if="form.errors.tax_rate" class="text-red-500 text-sm mt-1">
                    {{ form.errors.tax_rate }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tax Code</label>
                <input
                    v-model="form.tax_code"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="T20"
                />
                <p v-if="form.errors.tax_code" class="text-red-500 text-sm mt-1">
                    {{ form.errors.tax_code }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Discount (%)</label>
                <input
                    v-model="form.discount_percentage"
                    type="number"
                    step="0.01"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0.00"
                />
                <p v-if="form.errors.discount_percentage" class="text-red-500 text-sm mt-1">
                    {{ form.errors.discount_percentage }}
                </p>
            </div>
        </div>
    </div>
</template>