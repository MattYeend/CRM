<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { route } from 'ziggy-js';
import { type BreadcrumbItem } from '@/types';
import { deleteNotes } from '@/services/noteService';
import NoteDetailSection from './components/NoteDetailSection.vue';

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
                    <div>
                        <h1 class="text-2xl font-bold">Note #{{ note.id }}</h1>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span
                                v-if="note.user"
                                class="text-sm text-gray-500"
                            >
                                {{ note.user.name }}
                            </span>
                        </div>
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

                <NoteDetailSection :note="note" />
            </div>
        </div>
    </AppLayout>
</template>