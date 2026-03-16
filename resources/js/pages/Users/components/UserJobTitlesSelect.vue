<script setup lang="ts">
interface JobTitle {
    id: number
    title: string
}

defineProps<{
    jobTitles: JobTitle[]
    modelValue: number | null
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | null): void
}>()

function handleChange(event: Event) {
    const target = event.target as HTMLSelectElement
    const value = target.value ? Number(target.value) : null
    emit('update:modelValue', value)
}
</script>

<template>
    <select
        class="border rounded w-full p-2"
        :value="modelValue"
        @change="handleChange"
    >
        <option :value="''">Select job title</option>
        <option
            v-for="jobTitle in jobTitles"
            :key="jobTitle.id"
            :value="jobTitle.id"
        >
            {{ jobTitle.title }}
        </option>
    </select>
</template>