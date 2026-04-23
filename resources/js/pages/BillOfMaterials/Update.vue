<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import BOMForm from './components/BOMForm.vue'
import { route } from 'ziggy-js'

interface Part {
    id: number
    name: string
    sku: string
}

const props = defineProps<{
    part: Part
    billOfMaterial: any
    parts: Part[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Parts', href: route('parts.index') },
    { title: props.part.name, href: route('parts.show', { part: props.part.id }) },
    { title: 'Bill of Materials', href: route('parts.billOfMaterials.index', { part: props.part.id }) },
    {
        title: props.billOfMaterial.child_part?.name ?? `BOM #${props.billOfMaterial.id}`,
        href: route('parts.billOfMaterials.show', { part: props.part.id, billOfMaterial: props.billOfMaterial.id }),
    },
    {
        title: 'Edit',
        href: route('parts.billOfMaterials.edit', { part: props.part.id, billOfMaterial: props.billOfMaterial.id }),
    },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Component" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-1">Edit Component</h1>
            <p class="text-gray-500 text-sm mb-6">Part: {{ part.name }}</p>
            <BOMForm
                :parent-part="part"
                :bom="billOfMaterial"
                :parts="parts"
                method="put"
                submit-label="Update Component"
            />
        </div>
    </AppLayout>
</template>