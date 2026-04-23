<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { createPart, updatePart } from '@/services/partService'
import PartIdentitySection from './PartIdentitySection.vue'
import PartDimensionsSection from './PartDimensionsSection.vue'
import PartPricingSection from './PartPricingSection.vue'
import PartStockSection from './PartStockSection.vue'
import PartFlagsSection from './PartFlagsSection.vue'

interface Category {
    id: number
    name: string
}

interface Product {
    id: number
    name: string
}

interface Part {
    id?: number
    sku?: string
    part_number?: string
    barcode?: string
    name?: string
    description?: string
    brand?: string
    manufacturer?: string
    type?: string
    status?: string
    unit_of_measure?: string
    colour?: string
    material?: string
    height?: number | null
    width?: number | null
    length?: number | null
    weight?: number | null
    volume?: number | null
    price?: number | null
    cost_price?: number | null
    currency?: string
    tax_rate?: number | null
    tax_code?: string
    discount_percentage?: number | null
    quantity?: number
    min_stock_level?: number | null
    max_stock_level?: number | null
    reorder_point?: number | null
    reorder_quantity?: number | null
    lead_time_days?: number | null
    warehouse_location?: string
    bin_location?: string
    is_active?: boolean
    is_purchasable?: boolean
    is_sellable?: boolean
    is_manufactured?: boolean
    is_serialised?: boolean
    is_batch_tracked?: boolean
    category_id?: number | null
    product_id?: number | null
}

const props = defineProps<{
    part?: Part
    categories: Category[]
    products: Product[]
    method?: 'post' | 'put'
    submitLabel?: string
}>()

const form = useForm({
    sku: props.part?.sku ?? '',
    part_number: props.part?.part_number ?? '',
    barcode: props.part?.barcode ?? '',
    name: props.part?.name ?? '',
    description: props.part?.description ?? '',
    brand: props.part?.brand ?? '',
    manufacturer: props.part?.manufacturer ?? '',
    type: props.part?.type ?? '',
    status: props.part?.status ?? 'active',
    unit_of_measure: props.part?.unit_of_measure ?? '',
    colour: props.part?.colour ?? '',
    material: props.part?.material ?? '',
    category_id: props.part?.category_id ?? null,
    product_id: props.part?.product_id ?? null,
    height: props.part?.height ?? null,
    width: props.part?.width ?? null,
    length: props.part?.length ?? null,
    weight: props.part?.weight ?? null,
    volume: props.part?.volume ?? null,
    price: props.part?.price ?? null,
    cost_price: props.part?.cost_price ?? null,
    currency: props.part?.currency ?? 'GBP',
    tax_rate: props.part?.tax_rate ?? null,
    tax_code: props.part?.tax_code ?? '',
    discount_percentage: props.part?.discount_percentage ?? null,
    quantity: props.part?.quantity ?? 0,
    min_stock_level: props.part?.min_stock_level ?? null,
    max_stock_level: props.part?.max_stock_level ?? null,
    reorder_point: props.part?.reorder_point ?? null,
    reorder_quantity: props.part?.reorder_quantity ?? null,
    lead_time_days: props.part?.lead_time_days ?? null,
    warehouse_location: props.part?.warehouse_location ?? '',
    bin_location: props.part?.bin_location ?? '',
    is_active: props.part?.is_active ?? true,
    is_purchasable: props.part?.is_purchasable ?? true,
    is_sellable: props.part?.is_sellable ?? true,
    is_manufactured: props.part?.is_manufactured ?? false,
    is_serialised: props.part?.is_serialised ?? false,
    is_batch_tracked: props.part?.is_batch_tracked ?? false,
})

async function submit() {
    try {
        const payload = form.data()
        let result

        if (props.method === 'put' && props.part?.id) {
            result = await updatePart(props.part.id, payload)
        } else {
            result = await createPart(payload)
        }

        router.visit(`/parts/${result.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err)

        if (err.response?.status === 422) {
            const raw = err.response.data.errors as Record<string, string[]>
            const flat = Object.fromEntries(
                Object.entries(raw).map(([key, messages]) => [key, messages[0]])
            ) as Record<string, string>
            form.setError(flat)
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-2xl">
        <PartIdentitySection :form="form" :categories="categories" :products="products" />
        <PartDimensionsSection :form="form" />
        <PartPricingSection :form="form" />
        <PartStockSection :form="form" />
        <PartFlagsSection :form="form" />

        <div>
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Part') }}
            </button>
        </div>
    </form>
</template>