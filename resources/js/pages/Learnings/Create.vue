<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import LearningForm from './components/LearningForm.vue'
import { route } from 'ziggy-js'

defineProps<{
    users: Array<{ id: number; name: string }>
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Learnings', href: route('learnings.index') },
    { title: 'Create Learning', href: route('learnings.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Learning" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Learning</h1>
            <LearningForm
                :users="users"
                submit-route="/api/learnings"
                method="post"
            />
        </div>
    </AppLayout>
</template>