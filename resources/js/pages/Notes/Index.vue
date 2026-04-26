<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { deleteNotes, fetchNotes } from '@/services/noteService'

interface Note {
    id: number
    body: string
    notable_type: string
    notable_name: string | null
    user: { id: number; name: string } | null
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

const permissions = ref<GlobalPermissions>({
    create: false,
    viewAny: false,
})

const notes = ref<Note[]>([])
const loading = ref(true)

const currentPage = ref(1)
const perPage = 10

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const deletingId = ref<number | null>(null)

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Notes', href: route('notes.index') },
]

function notableTypeLabel(type: string) {
    return type.split('\\').pop() ?? type
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

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    if (end < total) {
        if (end < total - 1) pages.push('...')
        pages.push(total)
    }

    return pages
})

async function loadNotes(page = 1) {
    loading.value = true

    try {
        const data = await fetchNotes(perPage, page)

        notes.value = data.data
        permissions.value = data.permissions

        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total

        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadNotes(page)
    }
}

async function handleDelete(id: number) {
    if (!confirm('Are you sure?')) return

    deletingId.value = id
    try {
        await deleteNotes(id)
        loadNotes(currentPage.value)
    } finally {
        deletingId.value = null
    }
}

onMounted(() => loadNotes())
</script>
<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Notes" />

        <div class="p-6">

            <!-- Header -->
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Notes</h1>

                <Link
                    v-if="permissions.create"
                    :href="route('notes.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <!-- Table -->
            <table v-else class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2">Body</th>
                        <th class="p-2">Related To</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="note in notes" :key="note.id" class="border-t">
                        <td class="p-2">
                            <p class="line-clamp-2">{{ note.body }}</p>
                        </td>

                        <td class="p-2">
                            <span v-if="note.notable_name" class="inline-flex items-center gap-1">
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">
                                    {{ notableTypeLabel(note.notable_type) }}
                                </span>
                                {{ note.notable_name }}
                            </span>
                            <span v-else>—</span>
                        </td>

                        <td class="p-2 space-x-2">
                            <Link
                                v-if="note.permissions.view"
                                :href="route('notes.show', { note: note.id })"
                            >
                                View
                            </Link>

                            <Link
                                v-if="note.permissions.update"
                                :href="route('notes.edit', { note: note.id })"
                            >
                                Edit
                            </Link>

                            <button
                                v-if="note.permissions.delete"
                                class="text-red-600"
                                :disabled="deletingId === note.id"
                                @click="handleDelete(note.id)"
                            >
                                {{ deletingId === note.id ? 'Deleting…' : 'Delete' }}
                            </button>
                        </td>
                    </tr>

                    <tr v-if="notes.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            No notes found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination (Activity style) -->
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