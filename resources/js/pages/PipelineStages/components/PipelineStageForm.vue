<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import PipelineStageDetailsSection from './PipelineStageDetailsSection.vue'
import PipelineDealAssociationsSection from './PipelineDealAssociationsSection.vue'

interface PipelineSelectOption {
    id: number
    name: string
}

interface DealSelectOption {
    id: number
    title: string
}

interface PipelineStage {
    id?: number
    pipeline_id?: number | null
    deal_id?: number | null
    name?: string
    position?: number
    is_won_stage?: boolean
    is_lost_stage?: boolean
    is_open?: boolean
}

const props = defineProps<{
    stage?: PipelineStage
    pipelines: PipelineSelectOption[]
    deals: DealSelectOption[]
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    pipeline_id: props.stage?.pipeline_id ?? null,
    deal_id: props.stage?.deal_id ?? null,
    name: props.stage?.name ?? '',
    position: props.stage?.position ?? 0,
    is_won_stage: props.stage?.is_won_stage ?? false,
    is_lost_stage: props.stage?.is_lost_stage ?? false,
    is_open: props.stage?.is_open ?? false,
})

const selectedPipelineName = computed(() => {
    return props.pipelines.find(p => p.id === form.pipeline_id)?.name ?? 'N/A'
})

async function submit() {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const payload = { ...form.data() }

        const response = await axios({
            method: props.method === 'put' ? 'put' : 'post',
            url: props.submitRoute,
            data: payload,
            withCredentials: true,
            headers: { 'Content-Type': 'application/json' },
        })

        router.visit(`/pipeline-stages/${response.data.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err)

        if (err.response?.status === 422) {
            const raw = err.response.data.errors as Record<string, string[]>

            const flat = Object.fromEntries(
                Object.entries(raw).map(([key, messages]) => [key, messages[0]])
            ) as Record<string, string>

            form.setError(flat)
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-2xl">
        <PipelineStageDetailsSection
            :name="form.name"
            :position="form.position"
            :is-won-stage="form.is_won_stage"
            :is-lost-stage="form.is_lost_stage"
            :pipeline-name="selectedPipelineName"
            :is-open="form.is_open"
            :errors="form.errors"
            @update:name="form.name = $event"
            @update:position="form.position = $event"
            @update:is-won-stage="form.is_won_stage = $event"
            @update:is-lost-stage="form.is_lost_stage = $event"
            @update:is-open="form.is_open = $event"
        />

        <PipelineDealAssociationsSection
            :pipeline-id="form.pipeline_id"
            :deal-id="form.deal_id"
            :pipelines="pipelines"
            :deals="deals"
            :errors="form.errors"
            @update:pipeline-id="form.pipeline_id = $event"
            @update:deal-id="form.deal_id = $event"
        />

        <!-- Submit -->
        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Stage') }}
        </button>

    </form>
</template>