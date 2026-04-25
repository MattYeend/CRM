<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { deleteSuppliers, fetchSupplier } from '@/services/supplierService'
import SupplierDetailSection from './components/SupplierDetailSection.vue'

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

                <SupplierDetailSection :supplier="supplier" />
            </div>
        </div>
    </AppLayout>
</template>