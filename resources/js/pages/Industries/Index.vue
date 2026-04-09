<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchIndustries, deleteIndustries } from '@/services/industryService'

interface Industry {
    id: number
    name: string
    has_companies: boolean
    permissions: { view: boolean; update: boolean; delete: boolean }
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

const permissions = ref<GlobalPermissions>({ create: false, viewAny: false })
const industries = ref<Industry[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Industries', href: route('industries.index') },
]

async function loadIndustries(page = 1) {
    loading.value = true
    try {
        const data = await fetchIndustries(perPage, page)
        industries.value = data.data
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
    if (!confirm('Are you sure?')) return
    await deleteIndustries(id)
    loadIndustries(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) loadIndustries(page)
}

onMounted(() => loadIndustries())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Industries" />
        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Industries</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('industries.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <div v-if="loading" class="text-center py-6">Loading...</div>

            <table v-else class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="industry in industries" :key="industry.id" class="border-t">
                        <td class="p-2">{{ industry.name }}</td>
                        <td class="p-2 space-x-2">
                            <Link
                                v-if="industry.permissions.view"
                                :href="route('industries.show', { industry: industry.id })"
                            >View</Link>
                            <Link
                                v-if="industry.permissions.update && !industry.has_companies"
                                :href="route('industries.edit', { industry: industry.id })"
                            >Edit</Link>
                            <button
                                v-if="industry.permissions.delete && !industry.has_companies"
                                @click="handleDelete(industry.id)"
                                class="text-red-600"
                            >Delete</button>
                        </td>
                    </tr>
                    <tr v-if="industries.length === 0">
                        <td colspan="2" class="p-4 text-center text-gray-500">No industries found.</td>
                    </tr>
                </tbody>
            </table>

            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >Previous</button>
                <button
                    v-for="page in pagination.last_page"
                    :key="page"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-blue-600 text-white': page === currentPage }"
                    @click="goToPage(page)"
                >{{ page }}</button>
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === pagination.last_page"
                    @click="goToPage(currentPage + 1)"
                >Next</button>
            </div>
        </div>
    </AppLayout>
</template>