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

defineProps<{
    categories: Category[]
    products: Product[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Parts', href: route('parts.index') },
    { title: 'Create Part', href: route('parts.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Part" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Part</h1>
            <PartForm
                :categories="categories"
                :products="products"
                method="post"
                submit-label="Save Part"
            />
        </div>
    </AppLayout>
</template>