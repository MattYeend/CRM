<script setup lang="ts">
interface ActivityForm {
    type: string
    selected_assigned_to: number | null
    description: string
    subject_type: string
    subject_id: number | null
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{
    form: ActivityForm
    users: { id: number; name: string }[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Type</label>
            <input
                v-model="form.type"
                type="text"
                class="border rounded w-full p-2"
            />
            <p v-if="form.errors.type" class="text-red-500 text-sm">
                {{ form.errors.type }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Assign User</label>
            <select
                v-model="form.selected_assigned_to"
                class="border rounded w-full p-2"
                :disabled="!props.users.length"
            >
                <option :value="null">
                    {{ !props.users.length ? 'Loading...' : 'Select user' }}
                </option>
                <option v-for="user in props.users" :key="user.id" :value="user.id">
                    {{ user.name }}
                </option>
            </select>
            <p v-if="form.errors.selected_assigned_to" class="text-red-500 text-sm">
                {{ form.errors.selected_assigned_to }}
            </p>
        </div>
    </div>
</template>