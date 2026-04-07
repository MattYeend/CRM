<script setup lang="ts">
interface CompanyForm {
    name: string
    industry: string
    website: string
    phone: string
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{
    form: CompanyForm
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Company Details</h2>

        <div>
            <label class="block text-sm font-medium mb-1">
                Name <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.name"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="Acme Ltd"
            />
            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                {{ form.errors.name }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Industry</label>
            <input
                v-model="form.industry"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="Technology"
            />
            <p v-if="form.errors.industry" class="text-red-500 text-sm mt-1">
                {{ form.errors.industry }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Website</label>
            <input
                v-model="form.website"
                type="url"
                class="w-full border rounded px-3 py-2"
                placeholder="https://example.com"
            />
            <p v-if="form.errors.website" class="text-red-500 text-sm mt-1">
                {{ form.errors.website }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Phone</label>
            <input
                v-model="form.phone"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="+44 7700 900000"
            />
            <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
                {{ form.errors.phone }}
            </p>
        </div>
    </div>
</template>