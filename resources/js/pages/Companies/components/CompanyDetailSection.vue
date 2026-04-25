<script setup lang="ts">
interface Company {
    industry?: string
    full_address?: string
    phone?: string
    website?: string
    website_host?: string
    contact_full_name?: string
    contact_email?: string
    contact_phone?: string
    has_deals: boolean
    has_outstanding_invoices: boolean
    creator?: { name: string }
}

defineProps<{ company: Company }>()

function capitalize(str: string | null | undefined) {
    if (!str) return '—'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
}
</script>

<template>
    <div class="space-y-3">
        <div v-if="company.industry">
            <span class="font-semibold">Industry: </span>
            <span>{{ capitalize(company.industry) }}</span>
        </div>
        <div v-if="company.full_address">
            <span class="font-semibold">Address: </span>
            <span>{{ company.full_address }}</span>
        </div>
        <div v-if="company.phone">
            <span class="font-semibold">Phone: </span>
            <span>{{ company.phone }}</span>
        </div>
        <div v-if="company.website_host">
            <span class="font-semibold">Website: </span>
            <a :href="company.website" target="_blank" class="text-blue-600">
                {{ company.website_host }}
            </a>
        </div>
        <div v-if="company.contact_full_name">
            <span class="font-semibold">Contact: </span>
            <span>{{ company.contact_full_name }}</span>
        </div>
        <div v-if="company.contact_email">
            <span class="font-semibold">Contact Email: </span>
            <span>{{ company.contact_email }}</span>
        </div>
        <div v-if="company.contact_phone">
            <span class="font-semibold">Contact Phone: </span>
            <span>{{ company.contact_phone }}</span>
        </div>
        <div>
            <span class="font-semibold">Has Deals: </span>
            <span>{{ company.has_deals ? 'Yes' : 'No' }}</span>
        </div>
        <div>
            <span class="font-semibold">Outstanding Invoices: </span>
            <span>{{ company.has_outstanding_invoices ? 'Yes' : 'No' }}</span>
        </div>
        <div v-if="company.creator">
            <span class="font-semibold">Created By: </span>
            <span>{{ company.creator.name }}</span>
        </div>
    </div>
</template>