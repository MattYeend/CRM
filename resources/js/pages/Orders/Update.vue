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

const props = defineProps<{
    order: any
    users: SelectOption[]
    deals: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Orders', href: route('orders.index') },
    { title: `Edit Order #${props.order.id}`, href: route('orders.edit', { order: props.order.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Order" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Order #{{ order.id }}</h1>
            <OrderForm
                :order="order"
                :users="users"
                :deals="deals"
                :submit-route="`/api/orders/${order.id}`"
                method="put"
            />
        </div>
    </AppLayout>
</template>