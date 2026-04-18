<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { deleteActivities, fetchActivity } from '@/services/activityService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Activity {
    id: number
    type: string
    description: string
    username: string
    subject_type: string
    subject_id?: number
    subject_name?: string  
    creator?: { name: string }
    permissions: UserPermissions
}

function capitalize(str: string | null | undefined) {
    if (!str) return '-'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
}

const props = defineProps<{ activity: any }>()
const activity = ref<Activity>({
    id: props.activity.id,
    username: props.activity.username,
    type: props.activity.type,
    subject_type: props.activity.subject_type,
    subject_name: props.activity.subject_name,
    description: props.activity.description,
    permissions: { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Activities', href: route('activities.index') },
    { title: props.activity.type, href: route('activities.show', { activity: activity.value.id }) },
]

// Fetch the activity via API to get correct permissions
async function loadActivity() {
    const data = await fetchActivity(activity.value.id)

    Object.assign(activity.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure?')) return
    await deleteActivities(activity.value.id)
    window.location.href = route('activities.index')
}

onMounted(() => {
    loadActivity()
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="capitalize(activity.subject_type) || 'Activity'" />
        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-bold">{{ activity.type }}</h1>
                        <p class="text-gray-600">{{ activity.username }}</p>
                    </div>

                    <!-- RIGHT: Buttons -->
                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="activity.permissions?.update"
                            :href="route('activities.edit', { activity: activity.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('activities.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="activity.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <div>
                        <span class="font-semibold">Description: </span>
                        <span>{{ activity.description }}</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div>
                        <span class="font-semibold">Subject Type: </span>
                        <span>{{ activity.subject_type }}</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div>
                        <span class="font-semibold">Subject Name: </span>
                        <span>{{ activity.subject_name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>