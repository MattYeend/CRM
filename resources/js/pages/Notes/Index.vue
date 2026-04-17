<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { route } from 'ziggy-js';
import { type BreadcrumbItem } from '@/types';
import { deleteNotes } from '@/services/noteService';

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

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Notes', href: route('notes.index') },
];

const deletingId = ref<number | null>(null);

const canCreate = computed(() => props.notes.permissions?.create ?? false);

function notableTypeLabel(type: string): string {
    return type.split('\\').pop() ?? type;
}

async function handleDelete(note: Note) {
    if (!confirm('Are you sure?')) return;
    deletingId.value = note.id;
    try {
        await deleteNotes(note.id);
        router.reload({ only: ['notes'] });
    } finally {
        deletingId.value = null;
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Notes" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Notes</h1>
                <Link
                    v-if="canCreate"
                    :href="route('notes.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <table class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2">Body</th>
                        <th class="p-2">Related To</th>
                        <th class="p-2">Author</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="note in notes.data" :key="note.id" class="border-t">
                        <td class="p-2">
                            <p class="line-clamp-2">{{ note.body }}</p>
                        </td>
                        <td class="p-2">
                            <span v-if="note.notable_name" class="inline-flex items-center gap-1">
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">
                                    {{ notableTypeLabel(note.notable_type) }}
                                </span>
                                {{ note.notable_name }}
                            </span>
                            <span v-else>—</span>
                        </td>
                        <td class="p-2">{{ note.user?.name ?? '—' }}</td>
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
                                :disabled="deletingId === note.id"
                                class="text-red-600"
                                @click="handleDelete(note)"
                            >
                                {{ deletingId === note.id ? 'Deleting…' : 'Delete' }}
                            </button>
                        </td>
                    </tr>

                    <tr v-if="notes.data.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            No notes found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="notes.meta.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <Link
                    v-if="notes.links.prev"
                    :href="notes.links.prev"
                    class="px-3 py-1 border rounded"
                >
                    Previous
                </Link>
                <Link
                    v-if="notes.links.next"
                    :href="notes.links.next"
                    class="px-3 py-1 border rounded"
                >
                    Next
                </Link>
            </div>
        </div>
    </AppLayout>
</template>