<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import PipelineForm from './components/PipelineForm.vue'
import { route } from 'ziggy-js'

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Job Titles', href: route('job-titles.index') },
    { title: 'Create Job Title', href: route('job-titles.create') },
]
</script>

<template>
   <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Pipeline" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Pipeline</h1>

            <PipelineForm
                method="post"
                submit-route="/api/pipelines"
                submitLabel="Create Pipeline"
            />
        </div>

    </AppLayout>
</template>