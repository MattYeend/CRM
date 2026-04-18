<script setup lang="ts">
import { computed } from 'vue'

interface TypeOption {
    value: string
    label: string
}

interface EntityOption {
    id: number
    name: string
}

interface AttachmentForm {
    attachable_type: string
    attachable_id: number | null
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{
    modelValue: AttachmentForm
    types: TypeOption[]
    options: EntityOption[]
    loading: boolean
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: AttachmentForm): void
}>()

const form = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
})

const hasType = computed(() => !!form.value.attachable_type)
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">
                Attachable Type
            </label>

            <select
                v-model="form.attachable_type"
                class="border rounded w-full p-2"
                @change="form.attachable_id = null"
            >
                <option value="">Select type</option>

                <option
                    v-for="type in types"
                    :key="type.value"
                    :value="type.value"
                >
                    {{ type.label }}
                </option>
            </select>

            <p v-if="form.errors.attachable_type" class="text-red-500 text-sm">
                {{ form.errors.attachable_type }}
            </p>
        </div>

        <div v-if="hasType">
            <label class="block text-sm font-medium mb-1">
                Attachable Item
            </label>

            <select
                v-model="form.attachable_id"
                class="border rounded w-full p-2"
                :disabled="loading"
            >
                <option :value="null">
                    {{ loading ? 'Loading...' : 'Select item' }}
                </option>

                <option
                    v-for="item in options"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.name }}
                </option>
            </select>

            <p v-if="form.errors.attachable_id" class="text-red-500 text-sm">
                {{ form.errors.attachable_id }}
            </p>
        </div>
    </div>
</template>