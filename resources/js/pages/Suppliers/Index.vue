<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchSuppliers, deleteSuppliers } from '@/services/supplierService'

interface Supplier {
    id: number
    name: string
    code?: string
    email?: string
    phone?: string
    city?: string
    country?: string
    is_active: boolean
    permissions: UserPermissions
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

const permissions = ref<GlobalPermissions>({
    create: false,
    viewAny: false,
})

const suppliers = ref<Supplier[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

function capitalize(str: string | null | undefined) {
    if (!str) return '—'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Suppliers', href: route('suppliers.index') },
]

async function loadSuppliers(page = 1) {
    loading.value = true
    try {
        const data = await fetchSuppliers(perPage, page)
        suppliers.value = data.data
        permissions.value = data.permissions
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

async function handleDelete(id: number) {
    if (!confirm('Are you sure you want to delete this supplier?')) return
    await deleteSuppliers(id)
    loadSuppliers(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadSuppliers(page)
    }
}

onMounted(() => loadSuppliers())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Suppliers" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Suppliers</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('suppliers.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <table v-else class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Code</th>
                        <th class="p-2 text-left">Email</th>
                        <th class="p-2 text-left">Phone</th>
                        <th class="p-2 text-left">City</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="supplier in suppliers" :key="supplier.id" class="border-t">
                        <td class="p-2">{{ supplier.name }}</td>
                        <td class="p-2">{{ supplier.code || '—' }}</td>
                        <td class="p-2">{{ supplier.email || '—' }}</td>
                        <td class="p-2">{{ supplier.phone || '—' }}</td>
                        <td class="p-2">{{ capitalize(supplier.city) }}</td>
                        <td class="p-2">
                            <span
                                class="text-xs px-2 py-0.5 rounded-full font-medium"
                                :class="supplier.is_active
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-700'"
                            >
                                {{ supplier.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="p-2 space-x-2">
                            <Link
                                v-if="supplier.permissions.view"
                                :href="route('suppliers.show', { supplier: supplier.id })"
                            >
                                View
                            </Link>

                            <Link
                                v-if="supplier.permissions.update"
                                :href="route('suppliers.edit', { supplier: supplier.id })"
                            >
                                Edit
                            </Link>

                            <button
                                v-if="supplier.permissions.delete"
                                @click="handleDelete(supplier.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="suppliers.length === 0">
                        <td colspan="7" class="p-4 text-center text-gray-500">
                            No suppliers found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    Previous
                </button>

                <button
                    v-for="page in pagination.last_page"
                    :key="page"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-blue-600 text-white': page === currentPage }"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>

                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === pagination.last_page"
                    @click="goToPage(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>