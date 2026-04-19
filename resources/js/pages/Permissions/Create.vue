<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import PermissionForm from './components/PermissionForm.vue'
import { route } from 'ziggy-js'

interface SelectOption {
    id: number
    name: string
}

defineProps<{
    roles: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Job Titles', href: route('job-titles.index') },
    { title: 'Create Job Title', href: route('job-titles.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Permission" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Permission</h1>

            <PermissionForm
                :roles="roles"
                method="post"
                submit-route="/api/permissions"
                submitLabel="Create Permission"
            />
        </div>
    </AppLayout>
</template>