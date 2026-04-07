<script setup lang="ts">
interface CompanyForm {
    address: string
    city: string
    region: string
    postal_code: string
    country: string
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
        <h2 class="text-lg font-semibold border-b pb-2">Address</h2>

        <div>
            <label class="block text-sm font-medium mb-1">Street Address</label>
            <input
                v-model="form.address"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="123 Main Street"
            />
            <p v-if="form.errors.address" class="text-red-500 text-sm mt-1">
                {{ form.errors.address }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">City</label>
                <input
                    v-model="form.city"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Leicester"
                />
                <p v-if="form.errors.city" class="text-red-500 text-sm mt-1">
                    {{ form.errors.city }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Region</label>
                <input
                    v-model="form.region"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Leicestershire"
                />
                <p v-if="form.errors.region" class="text-red-500 text-sm mt-1">
                    {{ form.errors.region }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Postal Code</label>
                <input
                    v-model="form.postal_code"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="LE1 1AA"
                />
                <p v-if="form.errors.postal_code" class="text-red-500 text-sm mt-1">
                    {{ form.errors.postal_code }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Country</label>
                <input
                    v-model="form.country"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="United Kingdom"
                />
                <p v-if="form.errors.country" class="text-red-500 text-sm mt-1">
                    {{ form.errors.country }}
                </p>
            </div>
        </div>
    </div>
</template>