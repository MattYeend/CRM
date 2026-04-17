<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import NoteForm, { type NoteFormData } from './components/NoteForm.vue';
import { updateNotes } from '@/services/noteService';
import axios from 'axios';

interface User {
    id: number;
    name: string;
}

interface Notable {
    id: number;
    name?: string;
    title?: string;
}

interface Note {
    id: number;
    body: string;
    notable_type: string;
    notable_id: number;
    notable_name: string | null;
    notable: Notable | null;
    user: User | null;
}

const props = defineProps<{
    note: Note;
    notableTypes: string[];
}>();

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Notes', href: route('notes.index') },
    { label: `Note #${props.note.id}`, href: route('notes.show', props.note.id) },
    { label: 'Edit', href: route('notes.edit', props.note.id) },
];

const form = useForm<NoteFormData>({
    body: props.note.body ?? '',
    notable_type: props.note.notable_type ?? '',
    notable_id: props.note.notable_id ?? null,
});

async function submit() {
    form.clearErrors();
    form.processing = true;

    try {
        await updateNotes(props.note.id, {
            body: form.body,
            notable_type: form.notable_type || null,
            notable_id: form.notable_id || null,
        });

        form.processing = false;
        window.location.href = route('notes.show', props.note.id);
    } catch (error: unknown) {
        form.processing = false;
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            form.setError(error.response.data.errors);
        }
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit Note #${note.id}`" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Edit Note #{{ note.id }}</h1>
                <p class="mt-1 text-sm text-gray-500">Update the note content or its linked record.</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <NoteForm
                    :form="form"
                    :notable-types="notableTypes"
                    submit-label="Save Changes"
                    @submit="submit"
                >
                    <template #actions-left>
                        <Link
                            :href="route('notes.show', note.id)"
                            class="text-sm text-gray-500 hover:text-gray-700"
                        >
                            Cancel
                        </Link>
                    </template>
                </NoteForm>
            </div>
        </div>
    </AppLayout>
</template>