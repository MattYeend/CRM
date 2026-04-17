<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { deleteNotes, restoreNote } from '@/services/noteService';

interface User {
    id: number;
    name: string;
}

interface Notable {
    id: number;
    name?: string;
    title?: string;
}

interface NotePermissions {
    view: boolean;
    update: boolean;
    delete: boolean;
}

interface Note {
    id: number;
    body: string;
    notable_type: string;
    notable_id: number;
    notable_name: string | null;
    notable: Notable | null;
    user: User | null;
    meta: Record<string, unknown> | null;
    permissions: NotePermissions;
}

interface PaginatorMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface PaginatorLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

interface PaginatedNotes {
    data: Note[];
    meta: PaginatorMeta;
    links: PaginatorLinks;
    permissions?: {
        create: boolean;
        viewAny: boolean;
    };
}

const props = defineProps<{
    notes: PaginatedNotes;
}>();

const breadcrumbItems = [
    { label: 'Notes', href: route('notes.index') },
];

const deletingId = ref<number | null>(null);
const restoringId = ref<number | null>(null);

const canCreate = computed(() => props.notes.permissions?.create ?? false);

function notableTypeLabel(type: string): string {
    return type.split('\\').pop() ?? type;
}

async function handleDelete(note: Note) {
    if (!confirm(`Delete this note?`)) return;
    deletingId.value = note.id;
    try {
        await deleteNotes(note.id);
        router.reload({ only: ['notes'] });
    } finally {
        deletingId.value = null;
    }
}

async function handleRestore(note: Note) {
    restoringId.value = note.id;
    try {
        await restoreNote(note.id);
        router.reload({ only: ['notes'] });
    } finally {
        restoringId.value = null;
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Notes" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Notes</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ notes.meta.total }} note{{ notes.meta.total !== 1 ? 's' : '' }} total
                    </p>
                </div>
                <Link
                    v-if="canCreate"
                    :href="route('notes.create')"
                    class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Note
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Body</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Related To</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Author</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr v-if="notes.data.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-400">
                                No notes found.
                            </td>
                        </tr>
                        <tr
                            v-for="note in notes.data"
                            :key="note.id"
                            class="hover:bg-gray-50 transition-colors"
                        >
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                <p class="line-clamp-2">{{ note.body }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <span
                                    v-if="note.notable_name"
                                    class="inline-flex items-center gap-1"
                                >
                                    <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">
                                        {{ notableTypeLabel(note.notable_type) }}
                                    </span>
                                    {{ note.notable_name }}
                                </span>
                                <span v-else class="text-gray-300">—</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ note.user?.name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <Link
                                        v-if="note.permissions.view"
                                        :href="route('notes.show', note.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >View</Link>
                                    <Link
                                        v-if="note.permissions.update"
                                        :href="route('notes.edit', note.id)"
                                        class="text-gray-600 hover:text-gray-900"
                                    >Edit</Link>
                                    <button
                                        v-if="note.permissions.delete"
                                        :disabled="deletingId === note.id"
                                        class="text-red-500 hover:text-red-700 disabled:opacity-50"
                                        @click="handleDelete(note)"
                                    >
                                        {{ deletingId === note.id ? 'Deleting…' : 'Delete' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="notes.meta.last_page > 1" class="mt-6 flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    Showing {{ notes.meta.from }}–{{ notes.meta.to }} of {{ notes.meta.total }}
                </p>
                <div class="flex gap-2">
                    <Link
                        v-if="notes.links.prev"
                        :href="notes.links.prev"
                        class="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50 transition-colors"
                    >Previous</Link>
                    <Link
                        v-if="notes.links.next"
                        :href="notes.links.next"
                        class="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50 transition-colors"
                    >Next</Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>