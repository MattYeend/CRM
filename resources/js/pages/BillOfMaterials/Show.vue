<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { deleteBillOfMaterial } from '@/services/partService'
import BOMDetailSection from './components/BOMDetailSection.vue'

interface Part {
    id: number
    name: string
    sku: string
}

interface ChildPart {
    id: number
    name: string
    sku: string
    quantity: number
    unit_of_measure?: string
}

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface BillOfMaterial {
    id: number
    parent_part_id: number
    child_part_id: number
    quantity: number
    unit_of_measure?: string
    scrap_percentage?: number
    notes?: string
    child_part?: ChildPart
    creator?: { name: string }
    permissions: UserPermissions
}

const props = defineProps<{ part: Part; billOfMaterial: any }>()

const bom = ref<BillOfMaterial>({
    ...props.billOfMaterial,
    permissions: props.billOfMaterial.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Parts', href: route('parts.index') },
    { title: props.part.name, href: route('parts.show', { part: props.part.id }) },
    { title: 'Bill of Materials', href: route('parts.billOfMaterials.index', { part: props.part.id }) },
    {
        title: props.billOfMaterial.child_part?.name ?? `BOM #${props.billOfMaterial.id}`,
        href: route('parts.billOfMaterials.show', { part: props.part.id, billOfMaterial: props.billOfMaterial.id }),
    },
]

async function handleDelete() {
    if (!confirm('Are you sure you want to remove this BOM entry?')) return
    await deleteBillOfMaterial(props.part.id, bom.value.id)
    window.location.href = route('parts.billOfMaterials.index', { part: props.part.id })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="bom.child_part?.name ?? 'BOM Entry'" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">
                            {{ bom.child_part?.name ?? `BOM #${bom.id}` }}
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ bom.child_part?.sku }}
                        </p>
                        <p class="text-sm text-gray-600 mt-1">
                            Component of
                            <Link
                                :href="route('parts.show', { part: part.id })"
                            >{{ part.name }}</Link>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            v-if="bom.permissions?.update"
                            :href="route('parts.billOfMaterials.edit', { part: part.id, billOfMaterial: bom.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>
                        <Link
                            :href="route('parts.billOfMaterials.index', { part: part.id })"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>
                        <button
                            v-if="bom.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <BOMDetailSection :bom="bom" />
            </div>
        </div>
    </AppLayout>
</template>