<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { deleteCompanies, fetchCompany } from '@/services/companyService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Company {
    id: number
    name: string
    industry?: string
    industry_id?: number | null
    website?: string
    website_host?: string
    phone?: string
    address?: string
    city?: string
    region?: string
    postal_code?: string
    country?: string
    full_address?: string
    contact_first_name?: string
    contact_last_name?: string
    contact_full_name?: string
    contact_email?: string
    contact_phone?: string
    has_deals: boolean
    has_outstanding_invoices: boolean
    creator?: { name: string }
    permissions: UserPermissions
}

function capitalize(str: string | null | undefined) {
    if (!str) return '-'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
}

const props = defineProps<{ company: any }>()

const company = ref<Company>({
    id: props.company.id,
    name: props.company.name,
    industry: props.company.industry,
    industry_id: props.company.industry_id,
    website: props.company.website,
    phone: props.company.phone,
    city: props.company.city,
    country: props.company.country,
    has_deals: props.company.has_deals ?? false,
    has_outstanding_invoices: props.company.has_outstanding_invoices ?? false,
    permissions: props.company.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Companies', href: route('companies.index') },
    { title: props.company.name, href: route('companies.show', { company: company.value.id }) },
]

async function loadCompany() {
    const data = await fetchCompany(company.value.id)
    Object.assign(company.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure?')) return
    await deleteCompanies(company.value.id)
    window.location.href = route('companies.index')
}

onMounted(() => loadCompany())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="company.name || 'Company'" />
        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ company.name }}</h1>
                        <p v-if="company.industry" class="text-gray-600 mt-1">
                            {{ capitalize(company.industry) }}
                        </p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="company.permissions?.update"
                            :href="route('companies.edit', { company: company.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('companies.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="company.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
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
                        <a :href="company.website" target="_blank">
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
            </div>
        </div>
    </AppLayout>
</template>
