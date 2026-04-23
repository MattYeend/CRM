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

defineProps<{
    categories: Category[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Part Categories', href: route('part-categories.index') },
    { title: 'Create Category', href: route('part-categories.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Part Category" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Part Category</h1>
            <PartCategoryForm
                :categories="categories"
                method="post"
                submit-label="Save Category"
            />
        </div>
    </AppLayout>
</template>