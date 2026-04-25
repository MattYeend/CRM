<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchUser, deleteUser } from '@/services/userService'
import UserDetailSection from './components/UserDetailSection.vue'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Note {
    id: number
    body?: string | null
    notable_type?: string | null
    notable_id?: number | null
}

interface Task {
    id: number
    title?: string
    taskable_type?: string | null
    taskable_id?: number | null
    priority?: string
    status?: string
    due_at?: string | null
}

interface Deal {
    id: number
    title: string
    status?: string
    value?: number
    close_date?: string | null
    company?: { name: string }
    pipeline?: { name: string }
    stage?: { name: string }
}

interface Activity {
    id: number
    type?: string
    subject_type?: string | null
    subject_id?: number | null
    description?: string | null
}

interface Learning {
    id: number
    title?: string
    description?: string
    date?: Date
    pivot?: {
        is_complete?: boolean
        completed_at?: string | null
    }
}

interface User {
    id: number
    name: string
    email: string
    avatar_url?: string
    job_title?: { title: string }
    role?: { name: string }
    notes?: Note[]
    tasks?: Task[]
    deals?: Deal[]
    activities?: Activity[]
    learnings?: Learning[]
    permissions: UserPermissions
}

const props = defineProps<{ user: any }>()
const user = ref<User>({
    id: props.user.id,
    name: props.user.name,
    email: props.user.email,
    avatar_url: props.user.avatar_url,
    job_title: props.user.job_title,
    role: props.user.role,
    notes: props.user.notes,
    tasks: props.user.tasks,
    deals: props.user.deals,
    activities: props.user.activity,
    learnings: props.user.learnings,
    permissions: { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
    { title: props.user.name, href: route('users.show', { user: user.value.id }) },
]

// Fetch the user via API to get correct permissions
async function loadUser() {
    const data = await fetchUser(user.value.id)

    data.tasks?.forEach((task: any) => {
        if (task.due_at) task.due_at = new Date(task.due_at)
    })

    Object.assign(user.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure?')) return
    await deleteUser(user.value.id)
    window.location.href = route('users.index')
}

onMounted(() => {
    loadUser()
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="user.name || 'User'" />
        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">
                <div class="flex items-start justify-between mb-6">
                    <!-- LEFT: Avatar + Name -->
                    <div class="flex items-center space-x-4">
                        <img v-if="user.avatar_url" :src="user.avatar_url" class="w-16 h-16 rounded-full" />
                        <div>
                            <h1 class="text-2xl font-bold">{{ user.name }}</h1>
                            <a :href="`mailto:${user.email}`" class="text-blue-600">
                                {{ user.email }}
                            </a>
                        </div>
                    </div>

                    <!-- RIGHT: Buttons -->
                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="user.permissions?.update"
                            :href="route('users.edit', { user: user.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('users.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="user.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <UserDetailSection :user="user" />
            </div>
        </div>
    </AppLayout>
</template>