<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3'

interface ActivityForm {
    type: string
    selected_user_id: number | null
    description: string
    subject_type: string
    subject_id: number | null
    [key: string]: any
}

const props = defineProps<{
    form: InertiaForm<ActivityForm>
    subjectTypes: string[]
    subjectOptions: { id: number; name: string }[]
}>()
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Subject Type</label>
            <select v-model="props.form.subject_type" class="border rounded w-full p-2">
                <option value="">Select type</option>
                <option v-for="type in props.subjectTypes" :key="type" :value="type">
                    {{ type }}
                </option>
            </select>
            <p v-if="props.form.errors.subject_type" class="text-red-500 text-sm">
                {{ props.form.errors.subject_type }}
            </p>
        </div>

        <div v-if="props.subjectOptions.length">
            <label class="block text-sm font-medium mb-1">Subject</label>
            <select v-model="props.form.subject_id" class="border rounded w-full p-2">
                <option value="">Select subject</option>
                <option v-for="item in props.subjectOptions" :key="item.id" :value="item.id">
                    {{ item.name }}
                </option>
            </select>
            <p v-if="props.form.errors.subject_id" class="text-red-500 text-sm">
                {{ props.form.errors.subject_id }}
            </p>
        </div>
    </div>
</template>