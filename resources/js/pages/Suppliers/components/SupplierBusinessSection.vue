<script setup lang="ts">
interface SupplierForm {
    currency: string
    payment_terms: string
    tax_number: string
    notes: string
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
        <h2 class="text-lg font-semibold border-b pb-2">Business Terms</h2>

        <div>
            <label class="block text-sm font-medium mb-1">Currency</label>
            <input
                v-model="form.currency"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="e.g., GBP, USD, EUR"
            />
            <p v-if="form.errors.currency" class="text-red-500 text-sm mt-1">
                {{ form.errors.currency }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Payment Terms</label>
            <input
                v-model="form.payment_terms"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="e.g., Net 30, Net 60"
            />
            <p v-if="form.errors.payment_terms" class="text-red-500 text-sm mt-1">
                {{ form.errors.payment_terms }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Tax Number</label>
            <input
                v-model="form.tax_number"
                type="text"
                class="w-full border rounded px-3 py-2"
                placeholder="GB123456789"
            />
            <p v-if="form.errors.tax_number" class="text-red-500 text-sm mt-1">
                {{ form.errors.tax_number }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Notes</label>
            <textarea
                v-model="form.notes"
                rows="4"
                class="w-full border rounded px-3 py-2"
                placeholder="Additional information about this supplier..."
            ></textarea>
            <p v-if="form.errors.notes" class="text-red-500 text-sm mt-1">
                {{ form.errors.notes }}
            </p>
        </div>
    </div>
</template>