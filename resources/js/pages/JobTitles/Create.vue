<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import JobTitleForm from './components/JobTitleForm.vue'
import { route } from 'ziggy-js'

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Job Titles', href: route('job-titles.index') },
    { title: 'Create Job Title', href: route('job-titles.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Job Title" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Job Title</h1>
            <JobTitleForm
                submit-route="/api/job-titles"
                method="post"
            />
        </div>
    </AppLayout>
</template>