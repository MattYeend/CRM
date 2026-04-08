<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import CompanyForm from './components/CompanyForm.vue'
import { route } from 'ziggy-js'

interface Industry {
    id: number
    name: string
}

defineProps<{
    industries: Industry[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Companies', href: route('companies.index') },
    { title: 'Create Company', href: route('companies.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Company" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Company</h1>
            <CompanyForm
                :industries="industries"
                submit-route="/api/companies"
                method="post"
            />
        </div>
    </AppLayout>
</template>