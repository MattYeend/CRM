<script setup lang="ts">
interface Industry {
    id: number
    name: string
}

interface CompanyForm {
    name: string
    industry_id: number | null
    website: string
    phone: string
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{
    form: CompanyForm
    industries: Industry[]
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
            <select
                v-model="form.industry_id"
                class="w-full border rounded px-3 py-2"
            >
                <option :value="null">-- Select an industry --</option>
                <option
                    v-for="industry in industries"
                    :key="industry.id"
                    :value="industry.id"
                >
                    {{ industry.name }}
                </option>
            </select>
            <p v-if="form.errors.industry_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.industry_id }}
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