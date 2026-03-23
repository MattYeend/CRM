<script setup lang="ts">
interface ActivityForm {
    subject_type: string
    subject_id: number | null
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{
    form: ActivityForm
    subjectTypes: string[]
    subjectOptions: { id: number; name: string }[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Subject Type</label>
            <select v-model="form.subject_type" class="border rounded w-full p-2">
                <option value="">Select type</option>
                <option v-for="type in props.subjectTypes" :key="type" :value="type">
                    {{ type }}
                </option>
            </select>
            <p v-if="form.errors.subject_type" class="text-red-500 text-sm">
                {{ form.errors.subject_type }}
            </p>
        </div>

        <div v-if="props.subjectOptions.length">
            <label class="block text-sm font-medium mb-1">Subject</label>
            <select v-model="form.subject_id" class="border rounded w-full p-2">
                <option value="">Select subject</option>
                <option v-for="item in props.subjectOptions" :key="item.id" :value="item.id">
                    {{ item.name }}
                </option>
            </select>
            <p v-if="form.errors.subject_id" class="text-red-500 text-sm">
                {{ form.errors.subject_id }}
            </p>
        </div>
    </div>
</template>