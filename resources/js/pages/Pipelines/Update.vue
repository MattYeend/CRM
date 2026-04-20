<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import PipelineForm from './components/PipelineForm.vue'
import { route } from 'ziggy-js'

interface Stage {
    id: number
    name: string
    order: number
}

interface Pipeline {
    id: number
    name: string
    description: string
    is_test: boolean
    meta: Record<string, any> | null
    stages: Stage[]
}

const props = defineProps<{
    pipeline: Pipeline
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Pipelines', href: route('pipelines.index') },
    { title: `Pipeline ${props.pipeline.name}`, href: route('pipelines.show', { pipeline: props.pipeline.id }) },
    { title: `Edit Pipeline ${props.pipeline.name}`, href: route('pipelines.edit', { pipeline: props.pipeline.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Edit ${pipeline.name}`" />

        <PipelineForm
            :pipeline="pipeline"
            method="put"
            :submit-route="`/api/pipelines/${pipeline.id}`"
            submitLabel="Update Pipeline"
        />
    </AppLayout>
</template>