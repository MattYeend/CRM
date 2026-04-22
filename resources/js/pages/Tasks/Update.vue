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

interface User {
    id: number
    name: string
}

interface Taskable {
    id: number
    name?: string
    title?: string
}

interface Task {
    id: number
    title: string
    taskable_type: string
    taskable_id: number
    taskable_name: string | null
    taskable: Taskable | null
    user: User | null
}

const props = defineProps<{
    task: Task
    taskableTypes: string[]
    users: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Tasks', href: route('tasks.index') },
    { title: props.task.title, href: route('tasks.show', { task: props.task.id }) },
    { title: `Edit Task`, href: route('tasks.edit', { task: props.task.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Task" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Task: {{ task.title }}</h1>
            <TaskForm
                :task="task"
                :users="users"
                :taskableTypes="taskableTypes"
                :submit-route="`/api/tasks/${task.id}`"
                method="put"
                submitLabel="Update Task"
            />
        </div>
    </AppLayout>
</template>