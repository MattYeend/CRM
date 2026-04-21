<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import ProductForm from './components/ProductForm.vue'
import { route } from 'ziggy-js'

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: 'Create Product', href: route('products.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Product" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Product</h1>
            <ProductForm
                submit-route="/api/products"
                method="post"
                submitLabel="Save Product"
            />
        </div>
    </AppLayout>
</template>