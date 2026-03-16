<script setup lang="ts">
interface Role {
    id: number
    name: string
}

defineProps<{
    roles: Role[]
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
        <option :value="''">Select role</option>
        <option
            v-for="role in roles"
            :key="role.id"
            :value="role.id"
        >
            {{ role.name }}
        </option>
    </select>
</template>