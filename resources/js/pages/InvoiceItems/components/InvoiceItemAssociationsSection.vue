<script setup lang="ts">
import { computed } from 'vue'

interface SelectOption {
    id: number
    name: string
}

interface InvoiceSelectOption {
    id: number
}

const props = defineProps<{
    invoiceId: number | null
    productId: number | null
    invoices: InvoiceSelectOption[]
    products: SelectOption[]
    errors: {
        invoice_id?: string
        product_id?: string
    }
}>()

const emit = defineEmits<{
    'update:invoiceId': [value: number | null]
    'update:productId': [value: number | null]
}>()

const invoiceIdModel = computed({
    get: () => props.invoiceId,
    set: (value: number | null) => emit('update:invoiceId', value)
})

const productIdModel = computed({
    get: () => props.productId,
    set: (value: number | null) => emit('update:productId', value)
})
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Associations</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    Invoice <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="invoiceIdModel"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">- None -</option>
                    <option v-for="inv in invoices" :key="inv.id" :value="inv.id">
                        #{{ inv.id }}
                    </option>
                </select>
                <p v-if="errors.invoice_id" class="text-red-500 text-sm mt-1">
                    {{ errors.invoice_id }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Product</label>
                <select
                    v-model="productIdModel"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">- None -</option>
                    <option v-for="p in products" :key="p.id" :value="p.id">
                        {{ p.name }}
                    </option>
                </select>
                <p v-if="errors.product_id" class="text-red-500 text-sm mt-1">
                    {{ errors.product_id }}
                </p>
            </div>
        </div>
    </div>
</template>