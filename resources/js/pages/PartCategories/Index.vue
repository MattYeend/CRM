<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchPartCategories, deletePartCategory } from '@/services/partService'

interface PartCategory {
    id: number
    name: string
    slug?: string
    full_path?: string
    description?: string
    parent?: { id: number; name: string }
    children?: { id: number; name: string }[]
    parts?: { id: number; name: string }[]
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

const partCategories = ref<PartCategory[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Part Categories', href: route('part-categories.index') },
]

const visiblePages = computed(() => {
    const total = pagination.last_page
    const current = currentPage.value
    const delta = 2

    const pages: (number | string)[] = []

    const start = Math.max(1, current - delta)
    const end = Math.min(total, current + delta)

    if (start > 1) {
        pages.push(1)
        if (start > 2) pages.push('...')
    }

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    if (end < total) {
        if (end < total - 1) pages.push('...')
        pages.push(total)
    }

    return pages
})

async function loadPartCategories(page = 1) {
    loading.value = true
    try {
        const data = await fetchPartCategories(perPage, page)
        partCategories.value = data.data
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
    if (!confirm('Are you sure you want to delete this category?')) return
    await deletePartCategory(id)
    loadPartCategories(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadPartCategories(page)
    }
}

onMounted(() => loadPartCategories())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Part Categories" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Part Categories</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('part-categories.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <div v-if="loading" class="text-center py-6 text-gray-500">
                Loading...
            </div>

            <table v-else class="w-full border text-sm">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Full Path</th>
                        <th class="p-2 text-left">Parent</th>
                        <th class="p-2 text-right">Parts</th>
                        <th class="p-2 text-right">Sub-categories</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="category in partCategories"
                        :key="category.id"
                        class="border-t"
                    >
                        <td class="p-2">{{ category.name }}</td>
                        <td class="p-2">{{ category.full_path || '—' }}</td>
                        <td class="p-2">{{ category.parent?.name || '—' }}</td>
                        <td class="p-2 text-right tabular-nums">
                            {{ category.parts?.length ?? 0 }}
                        </td>
                        <td class="p-2 text-right tabular-nums">
                            {{ category.children?.length ?? 0 }}
                        </td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="category.permissions.view"
                                :href="route('part-categories.show', { partCategory: category.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="category.permissions.update"
                                :href="route('part-categories.edit', { partCategory: category.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="category.permissions.delete"
                                @click="handleDelete(category.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="partCategories.length === 0">
                        <td colspan="6" class="p-6 text-center text-gray-500">
                            No categories found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    Previous
                </button>

                <button
                    v-for="page in visiblePages"
                    :key="page"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-blue-600 text-white': page === currentPage }"
                    :disabled="page === '...'"
                    @click="typeof page === 'number' && goToPage(page)"
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