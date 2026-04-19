<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    title: string
    shortCode: string
    group: string | null
    errors: {
        title?: string
        short_code?: string
        group?: string
    }
}>()

const emit = defineEmits<{
    'update:title': [value: string]
    'update:shortCode': [value: string]
    'update:group': [value: string | null]
}>()

const groupOptions = [
    { value: 'C-Suite', label: 'C-Suite' },
    { value: 'Executive', label: 'Executive' },
    { value: 'Director', label: 'Director' },
]

const titleModel = computed({
    get: () => props.title,
    set: (value: string) => emit('update:title', value)
})

const shortCodeModel = computed({
    get: () => props.shortCode,
    set: (value: string) => emit('update:shortCode', value)
})

const groupModel = computed({
    get: () => props.group,
    set: (value: string | null) => emit('update:group', value)
})
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Job Title Details</h2>

        <div>
            <label class="block text-sm font-medium mb-1">
                Title <span class="text-red-500">*</span>
            </label>
            <input
                v-model="titleModel"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g. Chief Executive Officer"
            />
            <p v-if="errors.title" class="text-red-500 text-sm mt-1">{{ errors.title }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Short Code</label>
                <input
                    v-model="shortCodeModel"
                    type="text"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g. CEO"
                />
                <p v-if="errors.short_code" class="text-red-500 text-sm mt-1">{{ errors.short_code }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Group</label>
                <select
                    v-model="groupModel"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">- None -</option>
                    <option v-for="g in groupOptions" :key="g.value" :value="g.value">{{ g.label }}</option>
                </select>
                <p v-if="errors.group" class="text-red-500 text-sm mt-1">{{ errors.group }}</p>
            </div>
        </div>
    </div>
</template>