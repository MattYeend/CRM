<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import OrderForm from './components/OrderForm.vue'
import { route } from 'ziggy-js'

interface SelectOption {
    id: number
    name: string
}

defineProps<{
    users: SelectOption[]
    deals: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Orders', href: route('orders.index') },
    { title: 'Create Order', href: route('orders.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Order" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Order</h1>
            <OrderForm
                :users="users"
                :deals="deals"
                submit-route="/api/orders"
                method="post"
            />
        </div>
    </AppLayout>
</template>