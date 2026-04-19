<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import LeadForm from './components/LeadForm.vue'
import { route } from 'ziggy-js'

defineProps<{
    users: Array<{ id: number; name: string }>
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Leads', href: route('leads.index') },
    { title: 'Create Lead', href: route('leads.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Lead" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Lead</h1>
            <LeadForm
                :users="users"
                submit-route="/api/leads"
                method="post"
                submitLabel="Save Lead"
            />
        </div>
    </AppLayout>
</template>