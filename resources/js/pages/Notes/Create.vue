<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import NoteForm, { type NoteFormData } from './components/NoteForm.vue';
import { createNotes } from '@/services/noteService';
import axios from 'axios';

defineProps<{
    notableTypes: string[];
}>();

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Notes', href: route('notes.index') },
    { label: 'Create', href: route('notes.create') },
];

const form = useForm<NoteFormData>({
    body: '',
    notable_type: '',
    notable_id: null,
});

async function submit() {
    form.clearErrors();
    form.processing = true;

    try {
        await createNotes({
            body: form.body,
            notable_type: form.notable_type || null,
            notable_id: form.notable_id || null,
        });

        form.processing = false;
        window.location.href = route('notes.index');
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
        <Head title="Create Note" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Create Note</h1>
                <p class="mt-1 text-sm text-gray-500">Add a new note, optionally linked to a record.</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <NoteForm
                    :form="form"
                    :notable-types="notableTypes"
                    submit-label="Create Note"
                    @submit="submit"
                >
                    <template #actions-left>
                        <Link
                            :href="route('notes.index')"
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