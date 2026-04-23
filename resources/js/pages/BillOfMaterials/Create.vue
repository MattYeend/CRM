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
    parts: Part[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Parts', href: route('parts.index') },
    { title: props.part.name, href: route('parts.show', { part: props.part.id }) },
    { title: 'Bill of Materials', href: route('parts.billOfMaterials.index', { part: props.part.id }) },
    { title: 'Add Component', href: route('parts.billOfMaterials.create', { part: props.part.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Add Component" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-1">Add Component</h1>
            <p class="text-gray-500 text-sm mb-6">Adding to: {{ part.name }}</p>
            <BOMForm
                :parent-part="part"
                :parts="parts"
                method="post"
                submit-label="Add Component"
            />
        </div>
    </AppLayout>
</template>