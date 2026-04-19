<script setup lang="ts">
const props = defineProps<{ form: any }>()
const form = props.form

const sourceOptions = [
    'Website', 'Referral', 'Cold Call', 'Email', 'Social Media', 'Trade Show', 'Other'
]
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Contact Details</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="jane@example.com"
                />
                <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Phone</label>
                <input
                    v-model="form.phone"
                    type="text"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="+44 7700 900000"
                />
                <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</p>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-1">Source</label>
            <select
                v-model="form.source"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option :value="null">- None -</option>
                <option
                    v-for="s in sourceOptions"
                    :key="s"
                    :value="s"
                >
                    {{ s }}
                </option>
            </select>
            <p v-if="form.errors.source" class="text-red-500 text-sm mt-1">{{ form.errors.source }}</p>
        </div>
    </div>
</template>