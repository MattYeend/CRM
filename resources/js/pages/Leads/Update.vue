<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import LeadForm from './components/LeadForm.vue'
import { route } from 'ziggy-js'

const props = defineProps<{
    lead: any
    users: Array<{ id: number; name: string }>
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Leads', href: route('leads.index') },
    { title: props.lead.title, href: route('leads.show', { lead: props.lead.id }) },
    { title: `Edit ${props.lead.title}`, href: route('leads.edit', { lead: props.lead.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Lead" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Lead</h1>
            <LeadForm
                :lead="lead"
                :users="users"
                :submit-route="`/api/leads/${lead.id}`"
                method="put"
            />
        </div>
    </AppLayout>
</template>