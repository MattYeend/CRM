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

const props = defineProps<{
    company: any
    industries: Industry[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Companies', href: route('companies.index') },
    { title: props.company.name, href: route('companies.show', { company: props.company.id }) },
    { title: `Edit ${props.company.name}`, href: route('companies.edit', { company: props.company.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Company" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Company</h1>
            <CompanyForm
                :company="company"
                :industries="industries"
                :submit-route="`/api/companies/${company.id}`"
                method="put"
                submitLabel="Update Company"
            />
        </div>
    </AppLayout>
</template>