<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchUser, deleteUser } from '@/services/userService'

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

interface Tasks {
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
    contact?: { name: string }
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
    tasks?: Tasks[]
    deals?: Deal[]
    activities?: Activity[]
    learnings?: Learning[]
    permissions: UserPermissions
}

function formatDate(date: Date | string | null | undefined) {
    if (!date) return 'No Due Date Set'
    const d = typeof date === 'string' ? new Date(date) : date
    return d.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

function capitalize(str: string | null | undefined) {
    if (!str) return '-'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
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
    { title: 'View User', href: route('users.show', { user: user.value.id }) },
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

const tabs = ['Notes', 'Tasks', 'Deals', 'Activities', 'Learnings']
const activeTab = ref('Notes')

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
                            <p class="text-gray-600">{{ user.email }}</p>
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

                <div class="space-y-2">
                    <div>
                        <span class="font-semibold">Job Title: </span>
                        <span>{{ user.job_title?.title || '-' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold">Role: </span>
                        <span>{{ user.role?.name || '-' }}</span>
                    </div>
                </div>

                <div class="mt-6 mx-auto">
                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200 mb-4">
                        <button
                            v-for="tab in tabs"
                            :key="tab"
                            @click="activeTab = tab"
                            :class="[
                                'px-4 py-2 -mb-px font-semibold text-sm',
                                activeTab === tab
                                    ? 'border-b-2 border-blue-600 text-blue-600'
                                    : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            {{ tab }}
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="space-y-4">
                        <!-- Notes Tab -->
                        <div v-if="activeTab === 'Notes'">
                            <div v-if="user.notes?.length" class="space-y-3">
                                <div v-for="note in user.notes" :key="note.id" class="border rounded p-3">
                                    <div class="text-xs text-gray-500 mb-1">
                                        {{ capitalize(note.notable_type) }}
                                    </div>
                                    <p>{{ note.body }}</p>
                                </div>
                            </div>
                            <div v-else class="text-gray-400">No notes</div>
                        </div>

                        <!-- Tasks Tab -->
                        <div v-if="activeTab === 'Tasks'">
                            <div v-if="user.tasks?.length" class="space-y-3">
                                <div v-for="task in user.tasks" :key="task.id" class="border rounded p-3">
                                    <div class="flex justify-between">
                                        <strong>{{ task.title }}</strong>
                                        <span class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-700">
                                            {{ capitalize(task.status) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Priority: {{ capitalize(task.priority) }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-2">
                                        Due: {{ formatDate(task.due_at) }}
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-gray-400">No tasks</div>
                        </div>

                        <!-- Deals Tab -->
                        <div v-if="activeTab === 'Deals'">
                            <div v-if="user.deals?.length" class="space-y-3">
                                <div v-for="deal in user.deals" :key="deal.id" class="border rounded p-3">
                                    <div class="flex justify-between">
                                        <strong>{{ deal.title }}</strong>
                                        <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">
                                            {{ capitalize(deal.status) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ deal.company?.name || '-' }} • {{ deal.contact?.name || '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-2">
                                        {{ deal.pipeline?.name }} - {{ deal.stage?.name || '-' }}
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-gray-400">No deals</div>
                        </div>

                        <!-- Activities Tab -->
                        <div v-if="activeTab === 'Activities'">
                            <div v-if="user.activities?.length" class="space-y-3">
                                <div v-for="activity in user.activities" :key="activity.id" class="border-l-2 pl-4 py-2">
                                    <div class="text-sm font-medium">
                                        {{ capitalize(activity.type) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ capitalize(activity.subject_type) }}
                                    </div>
                                    <p class="text-sm mt-1">{{ activity.description || '-' }}</p>
                                </div>
                            </div>
                            <div v-else class="text-gray-400">No activities</div>
                        </div>

                        <!-- Learnings Tab -->
                        <div v-if="activeTab === 'Learnings'">
                            <div v-if="user.learnings?.length" class="space-y-3">
                                <div v-for="learning in user.learnings" :key="learning.id" class="border rounded-lg p-4 shadow-sm">
                                    <div class="flex justify-between items-center">
                                        <h3 class="font-semibold">{{ learning.title || 'Untitled' }}</h3>
                                        <span
                                            class="text-xs px-2 py-1 rounded"
                                            :class="learning.pivot?.is_complete 
                                                ? 'bg-green-100 text-green-700' 
                                                : 'bg-yellow-100 text-yellow-700'"
                                        >
                                            {{ learning.pivot?.is_complete ? 'Completed' : 'In Progress' }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ learning.description || 'No description' }}</p>
                                    <div class="flex gap-4 text-xs text-gray-500 mt-3">
                                        <span>Due: {{ formatDate(learning.date) }}</span>
                                        <span v-if="learning.pivot?.completed_at">
                                            {{ formatDate(learning.pivot.completed_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-gray-400">No learnings</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>