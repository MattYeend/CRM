<script setup lang="ts">
interface Supplier {
    email?: string
    phone?: string
    website?: string
    website_host?: string
    contact_name?: string
    contact_email?: string
    contact_phone?: string
    full_address?: string
    currency?: string
    payment_terms?: string
    tax_number?: string
    parts?: any[]
    notes?: string
    creator?: { name: string }
}

defineProps<{ supplier: Supplier }>()
</script>

<template>
    <div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Contact Information -->
            <div class="space-y-3">
                <h2 class="text-lg font-semibold border-b pb-2">Contact Information</h2>
                <div v-if="supplier.email">
                    <span class="font-semibold">Email: </span>
                    <a :href="`mailto:${supplier.email}`" class="text-blue-600">
                        {{ supplier.email }}
                    </a>
                </div>
                <div v-if="supplier.phone">
                    <span class="font-semibold">Phone: </span>
                    <span>{{ supplier.phone }}</span>
                </div>
                <div v-if="supplier.website_host">
                    <span class="font-semibold">Website: </span>
                    <a :href="supplier.website" target="_blank">
                        {{ supplier.website_host }}
                    </a>
                </div>
                <div v-if="supplier.contact_name">
                    <span class="font-semibold">Contact Name: </span>
                    <span>{{ supplier.contact_name }}</span>
                </div>
                <div v-if="supplier.contact_email">
                    <span class="font-semibold">Contact Email: </span>
                    <a :href="`mailto:${supplier.contact_email}`" class="text-blue-600">
                        {{ supplier.contact_email }}
                    </a>
                </div>
                <div v-if="supplier.contact_phone">
                    <span class="font-semibold">Contact Phone: </span>
                    <span>{{ supplier.contact_phone }}</span>
                </div>
            </div>

            <!-- Address -->
            <div class="space-y-3">
                <h2 class="text-lg font-semibold border-b pb-2">Address</h2>
                <div v-if="supplier.full_address">
                    <span class="font-semibold">Full Address: </span>
                    <span>{{ supplier.full_address }}</span>
                </div>
                <div v-else class="text-gray-500">No address on file</div>
            </div>

            <!-- Business Information -->
            <div class="space-y-3">
                <h2 class="text-lg font-semibold border-b pb-2">Business Information</h2>
                <div v-if="supplier.currency">
                    <span class="font-semibold">Currency: </span>
                    <span>{{ supplier.currency }}</span>
                </div>
                <div v-if="supplier.payment_terms">
                    <span class="font-semibold">Payment Terms: </span>
                    <span>{{ supplier.payment_terms }}</span>
                </div>
                <div v-if="supplier.tax_number">
                    <span class="font-semibold">Tax Number: </span>
                    <span>{{ supplier.tax_number }}</span>
                </div>
            </div>

            <!-- Parts -->
            <div class="space-y-3">
                <h2 class="text-lg font-semibold border-b pb-2">Parts</h2>
                <div v-if="supplier.parts && supplier.parts.length > 0">
                    <span class="font-semibold">Total Parts: </span>
                    <span>{{ supplier.parts.length }}</span>
                </div>
                <div v-else class="text-gray-500">No parts linked</div>
            </div>
        </div>

        <!-- Notes -->
        <div v-if="supplier.notes" class="mt-6 space-y-3">
            <h2 class="text-lg font-semibold border-b pb-2">Notes</h2>
            <p class="text-gray-700 whitespace-pre-wrap">{{ supplier.notes }}</p>
        </div>

        <!-- Meta -->
        <div v-if="supplier.creator" class="mt-6 pt-4 border-t text-sm text-gray-500">
            <span class="font-semibold">Created By: </span>
            <span>{{ supplier.creator.name }}</span>
        </div>
    </div>
</template>