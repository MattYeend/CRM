<script setup lang="ts">
interface SelectOption {
    id: number
    name: string
}

interface TaskForm {
    assigned_to: number | null
    taskable_type: string | null
    taskable_id: number | null
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{
    form: TaskForm
    users: SelectOption[]
    taskableTypes: string[]
    taskableOptions: { id: number; name: string }[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Associations</h2>

        <!-- Assigned To -->
        <div>
            <label class="block text-sm font-medium mb-1">Assigned To</label>

            <select v-model="form.assigned_to" class="border rounded w-full p-2">
                <option :value="null">- None -</option>

                <option
                    v-for="user in props.users"
                    :key="user.id"
                    :value="user.id"
                >
                    {{ user.name }}
                </option>
            </select>

            <p v-if="form.errors.assigned_to" class="text-red-500 text-sm">
                {{ form.errors.assigned_to }}
            </p>
        </div>

        <!-- Related Type -->
        <div>
            <label class="block text-sm font-medium mb-1">Related To <span class="text-red-500">*</span></label>

            <select v-model="form.taskable_type" class="border rounded w-full p-2">
                <option value="">Select type</option>

                <option
                    v-for="type in props.taskableTypes"
                    :key="type"
                    :value="type"
                >
                    {{ type }}
                </option>
            </select>

            <p v-if="form.errors.taskable_type" class="text-red-500 text-sm">
                {{ form.errors.taskable_type }}
            </p>
        </div>

        <!-- Related Record -->
        <div v-if="props.taskableOptions.length">
            <label class="block text-sm font-medium mb-1">Select <span class="text-red-500">*</span></label>

            <select v-model="form.taskable_id" class="border rounded w-full p-2">
                <option :value="null">Select record</option>

                <option
                    v-for="item in props.taskableOptions"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.name }}
                </option>
            </select>

            <p v-if="form.errors.taskable_id" class="text-red-500 text-sm">
                {{ form.errors.taskable_id }}
            </p>
        </div>
    </div>
</template>