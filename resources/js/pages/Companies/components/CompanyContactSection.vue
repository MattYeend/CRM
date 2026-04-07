<script setup lang="ts">
interface CompanyForm {
    contact_first_name: string
    contact_last_name: string
    contact_email: string
    contact_phone: string
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
        <h2 class="text-lg font-semibold border-b pb-2">Primary Contact</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">First Name</label>
                <input
                    v-model="form.contact_first_name"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Jane"
                />
                <p v-if="form.errors.contact_first_name" class="text-red-500 text-sm mt-1">
                    {{ form.errors.contact_first_name }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Last Name</label>
                <input
                    v-model="form.contact_last_name"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Smith"
                />
                <p v-if="form.errors.contact_last_name" class="text-red-500 text-sm mt-1">
                    {{ form.errors.contact_last_name }}
                </p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Contact Email</label>
            <input
                v-model="form.contact_email"
                type="email"
                class="w-full border rounded px-3 py-2"
                placeholder="jane.smith@example.com"
            />
            <p v-if="form.errors.contact_email" class="text-red-500 text-sm mt-1">
                {{ form.errors.contact_email }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Contact Phone</label>
            <input
                v-model="form.contact_phone"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="+44 7700 900001"
            />
            <p v-if="form.errors.contact_phone" class="text-red-500 text-sm mt-1">
                {{ form.errors.contact_phone }}
            </p>
        </div>
    </div>
</template>