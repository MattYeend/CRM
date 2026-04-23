<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import PartForm from './components/PartForm.vue'
import { route } from 'ziggy-js'

interface Category {
    id: number
    name: string
}

interface Product {
    id: number
    name: string
}

const props = defineProps<{
    part: any
    categories: Category[]
    products: Product[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Parts', href: route('parts.index') },
    { title: props.part.name, href: route('parts.show', { part: props.part.id }) },
    { title: `Edit ${props.part.name}`, href: route('parts.edit', { part: props.part.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Part" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Part</h1>
            <PartForm
                :part="part"
                :categories="categories"
                :products="products"
                method="put"
                submit-label="Update Part"
            />
        </div>
    </AppLayout>
</template>