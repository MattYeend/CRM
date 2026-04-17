<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { route } from 'ziggy-js';
import { type BreadcrumbItem } from '@/types';
import NoteForm from './components/NoteForm.vue';
import { updateNotes } from '@/services/noteService';
import axios from 'axios';
import { edit } from '@/routes/appearance';

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

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Update Note',
        href: edit().url
    }
];

const form = useForm({
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
        window.location.href = route('notes.show', { note: props.note.id });
    } catch (error: unknown) {
        form.processing = false;
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            form.setError(error.response.data.errors);
        }
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Edit Note #${note.id}`" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Note #{{ note.id }}</h1>

            <NoteForm
                :form="form"
                :notable-types="notableTypes"
                submit-label="Save Changes"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>