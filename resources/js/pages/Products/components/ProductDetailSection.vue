<script setup lang="ts">
interface Product {
    formatted_price: string
    currency: string
    description: string | null
    creator?: { name: string } | null
    quantity: number
    min_stock_level: number | null
    max_stock_level: number | null
    reorder_point: number | null
    reorder_quantity: number | null
    lead_time_days: number | null
    is_low_stock: boolean
    is_out_of_stock: boolean
}

defineProps<{ product: Product }>()
</script>

<template>
    <div class="space-y-6">

        <!-- Product Information -->
        <div>
            <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                Product Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">

                <div>
                    <span class="font-semibold">Price</span>
                    <div class="text-lg">
                        {{ product.formatted_price }} {{ product.currency }}
                    </div>
                </div>

                <div v-if="product.description">
                    <span class="font-semibold">Description</span>
                    <div>{{ product.description }}</div>
                </div>

                <div v-if="product.creator">
                    <span class="font-semibold">Created By</span>
                    <div>{{ product.creator.name }}</div>
                </div>

            </div>
        </div>

        <!-- Stock Information -->
        <div>
            <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                Stock Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">

                <div>
                    <span class="font-semibold">Current Stock</span>
                    <div>
                        <span
                            :class="{
                                'text-red-600 font-bold': product.is_out_of_stock,
                                'text-yellow-600 font-semibold': product.is_low_stock && !product.is_out_of_stock
                            }"
                        >
                            {{ product.quantity }}
                        </span>

                        <span
                            v-if="product.is_out_of_stock"
                            class="ml-2 text-xs text-red-600"
                        >
                            (Out of Stock)
                        </span>

                        <span
                            v-else-if="product.is_low_stock"
                            class="ml-2 text-xs text-yellow-600"
                        >
                            (Low Stock)
                        </span>
                    </div>
                </div>

                <div v-if="product.min_stock_level !== null">
                    <span class="font-semibold">Min Stock Level</span>
                    <div>{{ product.min_stock_level }}</div>
                </div>

                <div v-if="product.max_stock_level !== null">
                    <span class="font-semibold">Max Stock Level</span>
                    <div>{{ product.max_stock_level }}</div>
                </div>

                <div v-if="product.reorder_point !== null">
                    <span class="font-semibold">Reorder Point</span>
                    <div>{{ product.reorder_point }}</div>
                </div>

                <div v-if="product.reorder_quantity !== null">
                    <span class="font-semibold">Reorder Quantity</span>
                    <div>{{ product.reorder_quantity }}</div>
                </div>

                <div v-if="product.lead_time_days !== null">
                    <span class="font-semibold">Lead Time</span>
                    <div>{{ product.lead_time_days }} days</div>
                </div>

            </div>
        </div>

    </div>
</template>