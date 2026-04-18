<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { route } from 'ziggy-js';
import { type BreadcrumbItem } from '@/types';
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

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Notes', href: route('notes.index') },
    { title: `Note #${props.note.id}`, href: route('notes.show', { note: props.note.id }) },
];

const deleting = ref(false);

function notableTypeLabel(type: string): string {
    return type.split('\\').pop() ?? type;
}

async function handleDelete() {
    if (!confirm('Are you sure?')) return;
    deleting.value = true;
    try {
        await deleteNotes(props.note.id);
        window.location.href = route('notes.index');
    } catch {
        deleting.value = false;
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Note #${note.id}`" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-bold">Note #{{ note.id }}</h1>
                        <p v-if="note.user" class="text-gray-600">{{ note.user.name }}</p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="note.permissions.update"
                            :href="route('notes.edit', { note: note.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('notes.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="note.permissions.delete"
                            :disabled="deleting"
                            class="bg-red-600 text-white px-4 py-2 rounded disabled:opacity-50"
                            @click="handleDelete"
                        >
                            {{ deleting ? 'Deleting…' : 'Delete' }}
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <div>
                        <span class="font-semibold">Body: </span>
                        <span class="whitespace-pre-wrap">{{ note.body }}</span>
                    </div>
                </div>

                <div class="space-y-2 mt-2">
                    <div>
                        <span class="font-semibold">Related To: </span>
                        <span v-if="note.notable_name">
                            <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">
                                {{ notableTypeLabel(note.notable_type) }}
                            </span>
                            {{ note.notable_name }}
                        </span>
                        <span v-else>—</span>
                    </div>
                </div>

                <div class="space-y-2 mt-2">
                    <div>
                        <span class="font-semibold">Created By: </span>
                        <span>{{ note.creator?.name ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>