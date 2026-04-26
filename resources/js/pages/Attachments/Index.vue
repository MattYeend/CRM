<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchAttachments, deleteAttachments } from '@/services/attachmentService'

interface Attachment {
    id: number
    filename: string
    mime: string | null
    size: number | null
    attachable_type: string | null
    uploaded_by: { id: number; name: string } | null
    created_at: string
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

const attachments = ref<Attachment[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

function capitalize(str: string | null | undefined) {
    if (!str) return '-'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Attachments', href: route('attachments.index') },
]

async function loadAttachments(page = 1) {
    loading.value = true
    try {
        const data = await fetchAttachments(perPage, page)
        attachments.value = data.data
        permissions.value = data.permissions
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

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

    for (let i = start; i <= end; i++) pages.push(i)

    if (end < total) {
        if (end < total - 1) pages.push('...')
        pages.push(total)
    }

    return pages
})

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadAttachments(page)
    }
}

async function handleDelete(id: number) {
    if (!confirm('Are you sure you want to delete this attachment? This cannot be undone.')) return
    await deleteAttachments(id)
    loadAttachments(currentPage.value)
}

function isImage(mimeType: string | null): boolean {
    return !!mimeType?.startsWith('image/')
}

function formatSize(bytes: number | null): string {
    if (!bytes) return '—'
    if (bytes < 1024) return bytes + ' B'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

function formatDate(dateString: string | null): string {
    if (!dateString) return '—'
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}
onMounted(() => loadAttachments())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Attachments" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Attachments</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('attachments.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Add Attachment
                </Link>
            </div>

            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <table v-else class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">File Title</th>
                        <th class="p-2 text-left">Attached To</th>
                        <th class="p-2 text-left">Uploaded By</th>
                        <th class="p-2 text-left">Date</th>
                        <th class="p-2 text-left">Size</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="attachment in attachments"
                        :key="attachment.id"
                        class="border-t"
                    >
                        <td class="p-2">
                            <div class="flex items-center gap-2">
                                <svg v-if="isImage(attachment.mime)" class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <svg v-else class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div>
                                    <div class="text-sm font-medium">{{ attachment.filename }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-2 text-sm">{{ capitalize(attachment.attachable_type) }}</td>
                        <td class="p-2 text-sm">{{ attachment.uploaded_by ?? '—' }}</td>
                        <td class="p-2 text-sm">{{ formatDate(attachment.created_at) }}</td>
                        <td class="p-2 text-sm">{{ formatSize(attachment.size) }}</td>
                        <td class="p-2 space-x-2 text-sm whitespace-nowrap">
                            <Link
                                v-if="attachment.permissions.view"
                                :href="route('attachments.show', { attachment: attachment.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="attachment.permissions.update"
                                :href="route('attachments.edit', { attachment: attachment.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="attachment.permissions.delete"
                                class="text-red-600"
                                @click="handleDelete(attachment.id)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="attachments.length === 0">
                        <td colspan="6" class="p-4 text-center text-gray-500">
                            No attachments found.
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