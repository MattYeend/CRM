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

defineProps<{
    companies: SelectOption[]
    owners: SelectOption[]
    pipelines: SelectOption[]
    stages: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Deals', href: route('deals.index') },
    { title: 'Create Deal', href: route('deals.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Deal" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Deal</h1>
            <DealForm
                :companies="companies"
                :owners="owners"
                :pipelines="pipelines"
                :stages="stages"
                submit-route="/api/deals"
                method="post"
            />
        </div>
    </AppLayout>
</template>