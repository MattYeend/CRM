<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { type BreadcrumbItem } from '@/types'
import PipelineStageForm from './components/PipelineStageForm.vue'

interface SelectOption {
    id: number
    name: string
}

interface DealSelectOption {
    id: number
    title: string
}

defineProps<{
    pipelines: SelectOption[]
    deals: DealSelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Pipeline Stages', href: route('pipeline-stages.index') },
    { title: 'Create Pipeline Stage', href: route('pipeline-stages.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Pipeline Stage" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">
                Create Pipeline Stage
            </h1>

            <PipelineStageForm
                :pipelines="pipelines"
                :deals="deals"
                submit-route="/api/pipeline-stages"
                method="post"
                submitLabel="Save Pipeline Stage"
            />
        </div>
    </AppLayout>
</template>