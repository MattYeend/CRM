<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import JobTitleForm from './components/JobTitleForm.vue'
import { route } from 'ziggy-js'

const props = defineProps<{
    jobTitle: any
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Job Titles', href: route('job-titles.index') },
    { title: 'Edit Job Title', href: route('job-titles.edit', { jobTitle: props.jobTitle.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Job Title" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Job Title</h1>
            <JobTitleForm
                :job-title="jobTitle"
                :submit-route="`/api/job-titles/${jobTitle.id}`"
                method="put"
            />
        </div>
    </AppLayout>
</template>