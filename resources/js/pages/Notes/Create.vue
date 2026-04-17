<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { type BreadcrumbItem } from '@/types'
import NoteForm from './components/NoteForm.vue'

defineProps<{
    notableTypes: string[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Notes', href: route('notes.index') },
    { title: 'Create Note', href: route('notes.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Note" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Note</h1>

            <NoteForm
                :notable-types="notableTypes"
                submit-label="Create Note"
            />
        </div>
    </AppLayout>
</template>