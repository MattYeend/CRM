<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import DealForm from './components/DealForm.vue'
import { route } from 'ziggy-js'

interface SelectOption {
    id: number
    name: string
}

const props = defineProps<{
    deal: any
    companies: SelectOption[]
    owners: SelectOption[]
    pipelines: SelectOption[]
    stages: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Deals', href: route('deals.index') },
    { title: 'Edit Deal', href: route('deals.edit', { deal: props.deal.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Deal" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Deal</h1>
            <DealForm
                :deal="deal"
                :companies="companies"
                :owners="owners"
                :pipelines="pipelines"
                :stages="stages"
                :submit-route="`/api/deals/${deal.id}`"
                method="put"
            />
        </div>
    </AppLayout>
</template>