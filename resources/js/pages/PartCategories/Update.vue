<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import PartCategoryForm from './components/PartCategoryForm.vue'
import { route } from 'ziggy-js'

interface Category {
    id: number
    name: string
}

const props = defineProps<{
    partCategory: any
    categories: Category[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Part Categories', href: route('part-categories.index') },
    { title: props.partCategory.name, href: route('part-categories.show', { partCategory: props.partCategory.id }) },
    { title: `Edit ${props.partCategory.name}`, href: route('part-categories.edit', { partCategory: props.partCategory.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Part Category" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Part Category</h1>
            <PartCategoryForm
                :partCategory="partCategory"
                :categories="categories"
                method="put"
                submit-label="Update Category"
            />
        </div>
    </AppLayout>
</template>