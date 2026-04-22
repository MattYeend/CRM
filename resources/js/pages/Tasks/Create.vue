<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import TaskForm from './components/TaskForm.vue'
import { route } from 'ziggy-js'

interface SelectOption {
    id: number
    name: string
}

defineProps<{
    users: SelectOption[]
    taskableTypes: string[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Tasks', href: route('tasks.index') },
    { title: 'Create Task', href: route('tasks.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Task" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Task</h1>
            <TaskForm
                :users="users"
                :taskableTypes="taskableTypes"
                method="post"
                submitLabel="Create Task"
            />
        </div>
    </AppLayout>
</template>