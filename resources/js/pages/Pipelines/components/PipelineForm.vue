<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { createPipelines, updatePipelines } from '@/services/pipelineService'
import PipelineDetailsSection from './PipelineDetailsSection.vue'

interface Pipeline {
    id?: number
    name?: string
    description?: string
    is_test?: boolean
    meta?: Record<string, any> | null
}

interface PipelineFormData {
    name: string
    description: string
    is_test: boolean
}

const props = defineProps<{
    pipeline?: Pipeline
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm<PipelineFormData>({
    name: props.pipeline?.name ?? '',
    description: props.pipeline?.description ?? '',
    is_test: props.pipeline?.is_test ?? false,
})

async function submit() {
    form.clearErrors()
    
    try {
        const payload = { ...form.data() }

        const response = props.method === 'put' && props.pipeline?.id
            ? await updatePipelines(props.pipeline.id, payload)
            : await createPipelines(payload)

        router.visit(`/pipelines/${response.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err);

        if (err.response?.status === 422 && err.response.data.errors) {
            const errors = err.response.data.errors as Record<string, string[]>
            Object.keys(errors).forEach(key => {
                form.setError(key as keyof PipelineFormData, errors[key][0])
            })
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-2xl">

        <PipelineDetailsSection
            :name="form.name"
            :description="form.description"
            :errors="form.errors"
            @update:name="form.name = $event"
            @update:description="form.description = $event"
        />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Pipeline') }}
        </button>

    </form>
</template>