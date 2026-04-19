<script setup lang="ts">
interface User {
    id: number
    name: string
}

const props = defineProps<{
    form: any
    users: User[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Assignment</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Owner</label>
                <select
                    v-model="form.owner_id"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">- None -</option>
                    <option
                        v-for="user in users"
                        :key="user.id"
                        :value="user.id"
                    >
                        {{ user.name }}
                    </option>
                </select>
                <p v-if="form.errors.owner_id" class="text-red-500 text-sm mt-1">{{ form.errors.owner_id }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Assigned To</label>
                <select
                    v-model="form.assigned_to"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">- None -</option>
                    <option
                        v-for="user in users"
                        :key="user.id"
                        :value="user.id"
                    >
                        {{ user.name }}
                    </option>
                </select>
                <p v-if="form.errors.assigned_to" class="text-red-500 text-sm mt-1">{{ form.errors.assigned_to }}</p>
            </div>
        </div>
    </div>
</template>