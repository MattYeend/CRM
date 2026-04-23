<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchPartImages, deletePartImage } from '@/services/partService'

interface PartImage {
    id: number
    part_id: number
    part?: { id: number; name: string; sku: string }
    image?: string
    thumbnail_or_image_url?: string
    alt?: string
    is_primary: boolean
    sort_order?: number
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

const partImages = ref<PartImage[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Part Images', href: route('part-images.index') },
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

async function loadPartImages(page = 1) {
    loading.value = true
    try {
        const data = await fetchPartImages(perPage, page)
        partImages.value = data.data
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
    if (!confirm('Are you sure you want to delete this image?')) return
    await deletePartImage(id)
    loadPartImages(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadPartImages(page)
    }
}

onMounted(() => loadPartImages())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Part Images" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Part Images</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('part-images.create')"
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
                        <th class="p-2 text-left">Image</th>
                        <th class="p-2 text-left">Part</th>
                        <th class="p-2 text-left">Alt</th>
                        <th class="p-2 text-center">Primary</th>
                        <th class="p-2 text-right">Order</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="partImage in partImages"
                        :key="partImage.id"
                        class="border-t"
                    >
                        <td class="p-2">
                            <img
                                v-if="partImage.thumbnail_or_image_url"
                                :src="partImage.thumbnail_or_image_url"
                                :alt="partImage.alt ?? partImage.part?.name"
                                class="h-10 w-10 object-cover rounded border"
                            />
                            <span v-else class="text-gray-400 text-xs">No image</span>
                        </td>
                        <td class="p-2">
                            <Link
                                v-if="partImage.part"
                                :href="route('parts.show', { part: partImage.part.id })"
                            >
                                {{ partImage.part.name }}
                            </Link>
                            <span v-else>—</span>
                        </td>
                        <td class="p-2">{{ partImage.alt || '—' }}</td>
                        <td class="p-2 text-center">
                            <span
                                v-if="partImage.is_primary"
                                class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full"
                            >Primary</span>
                            <span v-else>—</span>
                        </td>
                        <td class="p-2 text-right tabular-nums">
                            {{ partImage.sort_order ?? '—' }}
                        </td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="partImage.permissions.view"
                                :href="route('part-images.show', { partImage: partImage.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="partImage.permissions.update"
                                :href="route('part-images.edit', { partImage: partImage.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="partImage.permissions.delete"
                                @click="handleDelete(partImage.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="partImages.length === 0">
                        <td colspan="6" class="p-6 text-center text-gray-500">
                            No images found.
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