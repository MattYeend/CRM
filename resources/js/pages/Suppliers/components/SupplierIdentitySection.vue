<script setup lang="ts">
interface SupplierForm {
    name: string
    code: string
    email: string
    phone: string
    website: string
    is_active: boolean
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
        <h2 class="text-lg font-semibold border-b pb-2">Basic Information</h2>
        
        <div>
            <label class="block text-sm font-medium mb-1">
                Name <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.name"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="ABC Supplies Ltd"
                required
            />
            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                {{ form.errors.name }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Supplier Code</label>
            <input
                v-model="form.code"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="SUP-001"
            />
            <p v-if="form.errors.code" class="text-red-500 text-sm mt-1">
                {{ form.errors.code }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input
                v-model="form.email"
                type="email"
                class="w-full border rounded px-3 py-2"
                placeholder="info@abcsupplies.com"
            />
            <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                {{ form.errors.email }}
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
            <label class="flex items-center space-x-2">
                <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="rounded"
                />
                <span class="text-sm font-medium">Active Supplier</span>
            </label>
        </div>
    </div>
</template>