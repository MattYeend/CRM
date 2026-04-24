<script setup lang="ts">
interface SupplierForm {
    contact_name: string
    contact_email: string
    contact_phone: string
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{
    form: SupplierForm
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Primary Contact</h2>

        <div>
            <label class="block text-sm font-medium mb-1">Contact Name</label>
            <input
                v-model="form.contact_name"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="John Smith"
            />
            <p v-if="form.errors.contact_name" class="text-red-500 text-sm mt-1">
                {{ form.errors.contact_name }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Contact Email</label>
            <input
                v-model="form.contact_email"
                type="email"
                class="w-full border rounded px-3 py-2"
                placeholder="john.smith@example.com"
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