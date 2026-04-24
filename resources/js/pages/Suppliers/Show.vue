<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { deleteSuppliers, fetchSupplier } from '@/services/supplierService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Supplier {
    id: number
    name: string
    code?: string
    email?: string
    phone?: string
    website?: string
    website_host?: string
    currency?: string
    payment_terms?: string
    tax_number?: string
    is_active: boolean
    notes?: string
    address_line_1?: string
    address_line_2?: string
    city?: string
    county?: string
    postcode?: string
    country?: string
    full_address?: string
    contact_name?: string
    contact_email?: string
    contact_phone?: string
    parts?: any[]
    creator?: { name: string }
    permissions: UserPermissions
}

const props = defineProps<{ supplier: any }>()

const supplier = ref<Supplier>({
    id: props.supplier.id,
    name: props.supplier.name,
    code: props.supplier.code,
    email: props.supplier.email,
    phone: props.supplier.phone,
    website: props.supplier.website,
    is_active: props.supplier.is_active ?? true,
    permissions: props.supplier.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Suppliers', href: route('suppliers.index') },
    { title: props.supplier.name, href: route('suppliers.show', { supplier: supplier.value.id }) },
]

async function loadSupplier() {
    const data = await fetchSupplier(supplier.value.id)
    Object.assign(supplier.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this supplier?')) return
    await deleteSuppliers(supplier.value.id)
    window.location.href = route('suppliers.index')
}

onMounted(() => loadSupplier())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="supplier.name || 'Supplier'" />
        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ supplier.name }}</h1>
                        <p v-if="supplier.code" class="text-gray-600 mt-1">
                            Code: {{ supplier.code }}
                        </p>
                        <p class="mt-1">
                            <span
                                class="text-xs px-2 py-0.5 rounded-full font-medium"
                                :class="supplier.is_active
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-700'"
                            >
                                {{ supplier.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="supplier.permissions?.update"
                            :href="route('suppliers.edit', { supplier: supplier.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('suppliers.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="supplier.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

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
                            <a :href="supplier.website" target="_blank" class="text-blue-600">
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
        </div>
    </AppLayout>
</template>