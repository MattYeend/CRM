<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import LearningForm from './components/LearningForm.vue'
import { route } from 'ziggy-js'

const props = defineProps<{
    learning: any
    users: Array<{ id: number; name: string }>
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Learnings', href: route('learnings.index') },
    { title: 'Edit Learning', href: route('learnings.edit', { learning: props.learning.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Learning" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Learning</h1>
            <LearningForm
                :learning="learning"
                :users="users"
                :submit-route="`/api/learnings/${learning.id}`"
                method="put"
            />
        </div>
    </AppLayout>
</template>