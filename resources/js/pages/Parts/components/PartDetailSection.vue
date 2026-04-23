<script setup lang="ts">
interface Part {
    sku?: string
    part_number?: string
    barcode?: string
    brand?: string
    manufacturer?: string
    type?: string
    status?: string
    unit_of_measure?: string
    colour?: string
    material?: string
    height?: number
    width?: number
    length?: number
    weight?: number
    volume?: number
    price?: number
    cost_price?: number
    currency?: string
    tax_rate?: number
    tax_code?: string
    discount_percentage?: number
    margin_percentage?: number
    quantity: number
    min_stock_level?: number
    max_stock_level?: number
    reorder_point?: number
    reorder_quantity?: number
    lead_time_days?: number
    warehouse_location?: string
    bin_location?: string
    is_active: boolean
    is_purchasable: boolean
    is_sellable: boolean
    is_manufactured: boolean
    is_serialised: boolean
    is_batch_tracked: boolean
    has_bom: boolean
    product?: { id: number; name: string }
    category?: { id: number; name: string }
    primary_supplier?: { id: number; name: string }
    creator?: { name: string }
}

defineProps<{ part: Part }>()

function formatCurrency(value?: number, currency = 'GBP') {
    if (value == null) return '—'
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

function formatNumber(value?: number) {
    if (value == null) return '—'
    return value.toLocaleString()
}
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Identity -->
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Details</h3>
            <dl class="space-y-2 text-sm">
                <div v-if="part.part_number" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Part Number</dt>
                    <dd>{{ part.part_number }}</dd>
                </div>
                <div v-if="part.barcode" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Barcode</dt>
                    <dd class="font-mono">{{ part.barcode }}</dd>
                </div>
                <div v-if="part.category" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Category</dt>
                    <dd>{{ part.category.name }}</dd>
                </div>
                <div v-if="part.product" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Product</dt>
                    <dd>{{ part.product.name }}</dd>
                </div>
                <div v-if="part.brand" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Brand</dt>
                    <dd>{{ part.brand }}</dd>
                </div>
                <div v-if="part.manufacturer" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Manufacturer</dt>
                    <dd>{{ part.manufacturer }}</dd>
                </div>
                <div v-if="part.type" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Type</dt>
                    <dd>{{ part.type }}</dd>
                </div>
                <div v-if="part.unit_of_measure" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Unit of Measure</dt>
                    <dd>{{ part.unit_of_measure }}</dd>
                </div>
                <div v-if="part.colour" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Colour</dt>
                    <dd>{{ part.colour }}</dd>
                </div>
                <div v-if="part.material" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Material</dt>
                    <dd>{{ part.material }}</dd>
                </div>
                <div v-if="part.primary_supplier" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Primary Supplier</dt>
                    <dd>{{ part.primary_supplier.name }}</dd>
                </div>
                <div v-if="part.creator" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Created By</dt>
                    <dd>{{ part.creator.name }}</dd>
                </div>
            </dl>
        </div>

        <!-- Stock -->
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Stock</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-600">Quantity</dt>
                    <dd class="tabular-nums">{{ formatNumber(part.quantity) }}</dd>
                </div>
                <div v-if="part.min_stock_level != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Min Stock</dt>
                    <dd class="tabular-nums">{{ formatNumber(part.min_stock_level) }}</dd>
                </div>
                <div v-if="part.max_stock_level != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Max Stock</dt>
                    <dd class="tabular-nums">{{ formatNumber(part.max_stock_level) }}</dd>
                </div>
                <div v-if="part.reorder_point != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Reorder Point</dt>
                    <dd class="tabular-nums">{{ formatNumber(part.reorder_point) }}</dd>
                </div>
                <div v-if="part.reorder_quantity != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Reorder Qty</dt>
                    <dd class="tabular-nums">{{ formatNumber(part.reorder_quantity) }}</dd>
                </div>
                <div v-if="part.lead_time_days != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Lead Time</dt>
                    <dd>{{ part.lead_time_days }} days</dd>
                </div>
                <div v-if="part.warehouse_location" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Warehouse</dt>
                    <dd>{{ part.warehouse_location }}</dd>
                </div>
                <div v-if="part.bin_location" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Bin</dt>
                    <dd class="font-mono">{{ part.bin_location }}</dd>
                </div>
            </dl>
        </div>

        <!-- Pricing -->
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Pricing</h3>
            <dl class="space-y-2 text-sm">
                <div v-if="part.price != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Price</dt>
                    <dd>{{ formatCurrency(part.price, part.currency) }}</dd>
                </div>
                <div v-if="part.cost_price != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Cost Price</dt>
                    <dd>{{ formatCurrency(part.cost_price, part.currency) }}</dd>
                </div>
                <div v-if="part.margin_percentage != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Margin</dt>
                    <dd>{{ part.margin_percentage }}%</dd>
                </div>
                <div v-if="part.tax_rate != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Tax Rate</dt>
                    <dd>{{ part.tax_rate }}%</dd>
                </div>
                <div v-if="part.tax_code" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Tax Code</dt>
                    <dd>{{ part.tax_code }}</dd>
                </div>
                <div v-if="part.discount_percentage != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Discount</dt>
                    <dd>{{ part.discount_percentage }}%</dd>
                </div>
            </dl>
        </div>

        <!-- Dimensions -->
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Dimensions</h3>
            <dl class="space-y-2 text-sm">
                <div v-if="part.height != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Height</dt>
                    <dd class="tabular-nums">{{ part.height }}</dd>
                </div>
                <div v-if="part.width != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Width</dt>
                    <dd class="tabular-nums">{{ part.width }}</dd>
                </div>
                <div v-if="part.length != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Length</dt>
                    <dd class="tabular-nums">{{ part.length }}</dd>
                </div>
                <div v-if="part.weight != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Weight</dt>
                    <dd class="tabular-nums">{{ part.weight }}</dd>
                </div>
                <div v-if="part.volume != null" class="flex justify-between">
                    <dt class="font-medium text-gray-600">Volume</dt>
                    <dd class="tabular-nums">{{ part.volume }}</dd>
                </div>
            </dl>
        </div>

        <!-- Flags -->
        <div class="space-y-3 md:col-span-2">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Flags</h3>
            <div class="flex flex-wrap gap-2 text-sm">
                <span
                    v-if="part.is_active"
                    class="px-2.5 py-0.5 rounded-full bg-green-100 text-green-700"
                >Active</span>
                <span
                    v-if="part.is_purchasable"
                    class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700"
                >Purchasable</span>
                <span
                    v-if="part.is_sellable"
                    class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700"
                >Sellable</span>
                <span
                    v-if="part.is_manufactured"
                    class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-700"
                >Manufactured</span>
                <span
                    v-if="part.is_serialised"
                    class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700"
                >Serialised</span>
                <span
                    v-if="part.is_batch_tracked"
                    class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700"
                >Batch Tracked</span>
                <span
                    v-if="part.has_bom"
                    class="px-2.5 py-0.5 rounded-full bg-orange-100 text-orange-700"
                >Has BOM</span>
            </div>
        </div>
    </div>
</template>