<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchPart, deletePart } from '@/services/partService'
import PartDetailSection from './components/PartDetailSection.vue'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Part {
    id: number
    sku: string
    name: string
    description?: string
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
    is_low_stock: boolean
    is_out_of_stock: boolean
    margin_percentage?: number
    has_bom: boolean
    product?: { id: number; name: string }
    category?: { id: number; name: string }
    primary_supplier?: { id: number; name: string }
    creator?: { name: string }
    permissions: UserPermissions
}

const props = defineProps<{ part: any }>()

const part = ref<Part>({
    ...props.part,
    permissions: props.part.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Parts', href: route('parts.index') },
    { title: props.part.name, href: route('parts.show', { part: props.part.id }) },
]

async function loadPart() {
    const data = await fetchPart(part.value.id)
    Object.assign(part.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this part?')) return
    await deletePart(part.value.id)
    window.location.href = route('parts.index')
}

onMounted(() => loadPart())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="part.name || 'Part'" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold">{{ part.name }}</h1>
                            <span
                                v-if="part.is_out_of_stock"
                                class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full"
                            >Out of Stock</span>
                            <span
                                v-else-if="part.is_low_stock"
                                class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full"
                            >Low Stock</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ part.sku }}</p>
                        <p v-if="part.description" class="text-gray-600 mt-2">
                            {{ part.description }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            v-if="part.has_bom"
                            :href="route('parts.billOfMaterials.index', { part: part.id })"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded"
                        >
                            Bill of Materials
                        </Link>
                        <Link
                            :href="route('parts.stock.show', { part: part.id })"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded"
                        >
                            Stock
                        </Link>
                        <Link
                            v-if="part.is_serialised"
                            :href="route('parts.serialNumbers.index', { part: part.id })"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded"
                        >
                            Serial Numbers
                        </Link>
                        <Link
                            v-if="part.permissions?.update"
                            :href="route('parts.edit', { part: part.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>
                        <Link
                            :href="route('parts.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>
                        <button
                            v-if="part.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <PartDetailSection :part="part" />
            </div>
        </div>
    </AppLayout>
</template>