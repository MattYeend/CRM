<script setup lang="ts">
import { computed } from 'vue'

interface SelectOption {
    id: number
    name: string
}

interface DealSelectOption {
    id: number
    title: string
}

const props = defineProps<{
    pipelineId: number | null
    dealId: number | null
    pipelines: SelectOption[]
    deals: DealSelectOption[]
    errors: {
        pipeline_id?: string
        deal_id?: string
    }
}>()

const emit = defineEmits<{
    'update:pipelineId': [number | null]
    'update:dealId': [number | null]
}>()

const pipelineIdModel = computed({
    get: () => props.pipelineId,
    set: (value) => emit('update:pipelineId', value)
})

const dealIdModel = computed({
    get: () => props.dealId,
    set: (value) => emit('update:dealId', value)
})
</script>

<template>
    <div class="space-y-4">

        <h2 class="text-lg font-semibold border-b pb-2">
            Associations
        </h2>

        <div class="grid grid-cols-2 gap-4">

            <!-- Pipeline -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Pipeline <span class="text-red-500">*</span>
                </label>

                <select
                    v-model="pipelineIdModel"
                    class="w-full border rounded px-3 py-2"
                >
                    <option :value="null">- None -</option>
                    <option
                        v-for="p in pipelines"
                        :key="p.id"
                        :value="p.id"
                    >
                        {{ p.name }}
                    </option>
                </select>

                <p v-if="errors.pipeline_id" class="text-red-500 text-sm">
                    {{ errors.pipeline_id }}
                </p>
            </div>

            <!-- Deal -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Deal
                </label>

                <select
                    v-model="dealIdModel"
                    class="w-full border rounded px-3 py-2"
                >
                    <option :value="null">- None -</option>
                    <option
                        v-for="d in deals"
                        :key="d.id"
                        :value="d.id"
                    >
                        {{ d.title }}
                    </option>
                </select>

                <p v-if="errors.deal_id" class="text-red-500 text-sm">
                    {{ errors.deal_id }}
                </p>
            </div>

        </div>
    </div>
</template>