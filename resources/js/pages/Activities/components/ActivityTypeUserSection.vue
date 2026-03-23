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
    users: { id: number; name: string }[]
}>()
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Type</label>
            <input
                v-model="props.form.type"
                type="text"
                class="border rounded w-full p-2"
            />
            <p v-if="props.form.errors.type" class="text-red-500 text-sm">
                {{ props.form.errors.type }}
            </p>
        </div>

        <div v-if="props.users.length">
            <label class="block text-sm font-medium mb-1">Assign User</label>
            <select v-model="props.form.selected_user_id" class="border rounded w-full p-2">
                <option value="">Select user</option>
                <option v-for="user in props.users" :key="user.id" :value="user.id">
                    {{ user.name }}
                </option>
            </select>
            <p v-if="props.form.errors.selected_user_id" class="text-red-500 text-sm">
                {{ props.form.errors.selected_user_id }}
            </p>
        </div>
    </div>
</template>