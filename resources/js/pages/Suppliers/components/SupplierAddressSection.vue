<script setup lang="ts">
interface SupplierForm {
    address_line_1: string
    address_line_2: string
    city: string
    county: string
    postcode: string
    country: string
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
        <h2 class="text-lg font-semibold border-b pb-2">Address</h2>

        <div>
            <label class="block text-sm font-medium mb-1">Address Line 1</label>
            <input
                v-model="form.address_line_1"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="123 Business Park"
            />
            <p v-if="form.errors.address_line_1" class="text-red-500 text-sm mt-1">
                {{ form.errors.address_line_1 }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Address Line 2</label>
            <input
                v-model="form.address_line_2"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="Unit 5"
            />
            <p v-if="form.errors.address_line_2" class="text-red-500 text-sm mt-1">
                {{ form.errors.address_line_2 }}
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
                <label class="block text-sm font-medium mb-1">County/State</label>
                <input
                    v-model="form.county"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Leicestershire"
                />
                <p v-if="form.errors.county" class="text-red-500 text-sm mt-1">
                    {{ form.errors.county }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Postcode</label>
                <input
                    v-model="form.postcode"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="LE1 1AA"
                />
                <p v-if="form.errors.postcode" class="text-red-500 text-sm mt-1">
                    {{ form.errors.postcode }}
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