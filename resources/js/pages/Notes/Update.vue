<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { type BreadcrumbItem } from '@/types'
import NoteForm from './components/NoteForm.vue'

interface User {
    id: number
    name: string
}

interface Notable {
    id: number
    name?: string
    title?: string
}

interface Note {
    id: number
    body: string
    notable_type: string
    notable_id: number
    notable_name: string | null
    notable: Notable | null
    user: User | null
}

const props = defineProps<{
    note: Note
    notableTypes: string[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Notes', href: route('notes.index') },
    { title: `Note #${props.note.id}`, href: route('notes.show', { note: props.note.id }) },
    { title: `Edit Note #${props.note.id}`, href: route('notes.edit', { note: props.note.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Edit Note #${note.id}`" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Note #{{ note.id }}</h1>

            <NoteForm
                :note="note"
                :notable-types="notableTypes"
                method="put"
                submit-label="Save Changes"
            />
        </div>
    </AppLayout>
</template>