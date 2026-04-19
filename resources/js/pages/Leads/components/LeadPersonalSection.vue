<script setup lang="ts">
const props = defineProps<{ form: any }>()
const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Personal Details</h2>

        <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input
                v-model="form.title"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g. Senior Engineer at Acme Corp"
            />
            <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">First Name</label>
                <input
                    v-model="form.first_name"
                    type="text"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Jane"
                />
                <p v-if="form.errors.first_name" class="text-red-500 text-sm mt-1">{{ form.errors.first_name }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Last Name</label>
                <input
                    v-model="form.last_name"
                    type="text"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Smith"
                />
                <p v-if="form.errors.last_name" class="text-red-500 text-sm mt-1">{{ form.errors.last_name }}</p>
            </div>
        </div>
    </div>
</template>