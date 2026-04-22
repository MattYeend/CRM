<script setup lang="ts">
interface SelectOption {
    id: number
    name: string
}

const props = defineProps<{
    form: any
    users: SelectOption[]
    taskableTypes: string[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Associations</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    Assigned To
                </label>
                <select
                    v-model="form.assigned_to"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">- None -</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
                <p v-if="form.errors.assigned_to" class="text-red-500 text-sm mt-1">
                    {{ form.errors.assigned_to }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Related Type
                </label>
                <select
                    v-model="form.taskable_type"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">- None -</option>
                    <option v-for="type in taskableTypes" :key="type" :value="type">
                        {{ type }}
                    </option>
                </select>
                <p v-if="form.errors.taskable_type" class="text-red-500 text-sm mt-1">
                    {{ form.errors.taskable_type }}
                </p>
            </div>
        </div>

        <div v-if="form.taskable_type">
            <label class="block text-sm font-medium mb-1">
                Related ID
            </label>
            <input
                v-model="form.taskable_id"
                type="number"
                min="1"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p v-if="form.errors.taskable_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.taskable_id }}
            </p>
        </div>
    </div>
</template>