<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import ProductForm from './components/ProductForm.vue'
import { route } from 'ziggy-js'

const props = defineProps<{
    product: any
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: props.product.name, href: route('products.show', { product: props.product.id }) },
    { title: `Edit ${props.product.name}`, href: route('products.edit', { product: props.product.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Product" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Product</h1>
            <ProductForm
                :product="product"
                :submit-route="`/api/products/${product.id}`"
                method="put"
                submitLabel="Update Product"
            />
        </div>
    </AppLayout>
</template>