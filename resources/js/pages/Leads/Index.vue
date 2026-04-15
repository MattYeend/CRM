<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchLeads, deleteLeads } from '@/services/leadService'

interface Lead {
    id: number
    title: string
    first_name: string | null
    last_name: string | null
    full_name: string
    display_name: string
    email: string | null
    phone: string | null
    source: string | null
    age_in_days: number
    is_stale: boolean
    is_hot: boolean
    is_high_priority: boolean
    is_low_priority: boolean
    is_eligible_for_conversion: boolean
    owner: { id: number; name: string } | null
    assigned_to: { id: number; name: string } | null
    permissions: {
        view: boolean
        update: boolean
        delete: boolean
    }
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

const permissions = ref<GlobalPermissions>({ create: false, viewAny: false })
const leads = ref<Lead[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Leads', href: route('leads.index') },
]

async function loadLeads(page = 1) {
    loading.value = true
    try {
        const data = await fetchLeads(perPage, page)
        leads.value = data.data
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
    if (!confirm('Are you sure you want to delete this lead?')) return
    await deleteLeads(id)
    loadLeads(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadLeads(page)
    }
}

onMounted(() => loadLeads())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Leads" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Leads</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('leads.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <div v-if="loading" class="text-center py-6">Loading...</div>

            <table v-else class="w-full border">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Email</th>
                        <th class="p-2 text-left">Phone</th>
                        <th class="p-2 text-left">Source</th>
                        <th class="p-2 text-left">Owner</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-right">Age (days)</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="lead in leads" :key="lead.id" class="border-t">
                        <td class="p-2 font-medium">{{ lead.display_name }}</td>
                        <td class="p-2 text-gray-500">{{ lead.email ?? '—' }}</td>
                        <td class="p-2 text-gray-500">{{ lead.phone ?? '—' }}</td>
                        <td class="p-2 text-gray-500">{{ lead.source ?? '—' }}</td>
                        <td class="p-2 text-gray-500">{{ lead.owner?.name ?? '—' }}</td>
                        <td class="p-2">
                            <span
                                v-if="lead.is_hot"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"
                            >
                                Hot
                            </span>
                            <span
                                v-else-if="lead.is_high_priority"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700"
                            >
                                High Priority
                            </span>
                            <span
                                v-else-if="lead.is_stale"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600"
                            >
                                Stale
                            </span>
                            <span
                                v-else-if="lead.is_low_priority"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-600"
                            >
                                Low Priority
                            </span>
                            <span
                                v-else
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700"
                            >
                                Active
                            </span>
                        </td>
                        <td class="p-2 text-right">{{ lead.age_in_days }}</td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="lead.permissions.view"
                                :href="route('leads.show', { lead: lead.id })"
                                class="text-blue-600 underline text-sm"
                            >
                                View
                            </Link>
                            <Link
                                v-if="lead.permissions.update"
                                :href="route('leads.edit', { lead: lead.id })"
                                class="text-blue-600 underline text-sm"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="lead.permissions.delete"
                                @click="handleDelete(lead.id)"
                                class="text-red-600 text-sm"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="leads.length === 0">
                        <td colspan="8" class="p-4 text-center text-gray-500">
                            No leads found.
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