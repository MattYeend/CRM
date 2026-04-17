<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { deleteNotes } from '@/services/noteService';

interface User {
    id: number;
    name: string;
    email?: string;
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
    creator: User | null;
    meta: Record<string, unknown> | null;
    permissions: NotePermissions;
}

const props = defineProps<{
    note: Note;
}>();

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Notes', href: route('notes.index') },
    { label: 'View Note', href: route('notes.show', props.note.id) },
];

const deleting = ref(false);

function notableTypeLabel(type: string): string {
    return type.split('\\').pop() ?? type;
}

async function handleDelete() {
    if (!confirm('Delete this note?')) return;
    deleting.value = true;
    try {
        await deleteNotes(props.note.id);
        router.visit(route('notes.index'));
    } catch {
        deleting.value = false;
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Note #${note.id}`" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto">
            <!-- Header -->
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Note #{{ note.id }}</h1>
                    <p v-if="note.user" class="mt-1 text-sm text-gray-500">
                        By {{ note.user.name }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        v-if="note.permissions.update"
                        :href="route('notes.edit', note.id)"
                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                        </svg>
                        Edit
                    </Link>
                    <button
                        v-if="note.permissions.delete"
                        :disabled="deleting"
                        class="inline-flex items-center gap-1.5 rounded-md border border-red-200 bg-white px-3 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50 disabled:opacity-50 transition-colors"
                        @click="handleDelete"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        {{ deleting ? 'Deleting…' : 'Delete' }}
                    </button>
                </div>
            </div>

            <!-- Note card -->
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm divide-y divide-gray-100">
                <!-- Body -->
                <div class="px-6 py-5">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-400 mb-3">Content</h2>
                    <p class="text-gray-800 whitespace-pre-wrap leading-relaxed">{{ note.body }}</p>
                </div>

                <!-- Related to -->
                <div class="px-6 py-5">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-400 mb-3">Related To</h2>
                    <div v-if="note.notable_name" class="flex items-center gap-2">
                        <span class="rounded bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                            {{ notableTypeLabel(note.notable_type) }}
                        </span>
                        <span class="text-sm text-gray-800">{{ note.notable_name }}</span>
                    </div>
                    <p v-else class="text-sm text-gray-400">Not linked to a record</p>
                </div>

                <!-- Meta -->
                <div class="px-6 py-5">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-400 mb-3">Details</h2>
                    <dl class="grid grid-cols-1 gap-y-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-gray-500">Author</dt>
                            <dd class="mt-0.5 text-sm font-medium text-gray-900">{{ note.user?.name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Created By</dt>
                            <dd class="mt-0.5 text-sm font-medium text-gray-900">{{ note.creator?.name ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Back link -->
            <div class="mt-6">
                <Link :href="route('notes.index')" class="text-sm text-gray-500 hover:text-gray-700">
                    ← Back to Notes
                </Link>
            </div>
        </div>
    </AppLayout>
</template>