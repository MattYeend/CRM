<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, computed, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchJobTitle, deleteJobTitles } from '@/services/jobTitleService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface JobTitle {
    id: number
    title: string
    short_code: string | null
    group: string | null
    is_csuite: boolean
    is_executive: boolean
    is_director: boolean
    user_count: number
    users?: Array<{ id: number; name: string }>
    creator?: { name: string } | null
    updater?: { name: string } | null
    permissions: UserPermissions
}

const props = defineProps<{ jobTitle: any }>()

const jobTitle = ref<JobTitle>({
    id: props.jobTitle.id,
    title: props.jobTitle.title ?? '',
    short_code: props.jobTitle.short_code ?? null,
    group: props.jobTitle.group ?? null,
    is_csuite: props.jobTitle.is_csuite ?? false,
    is_executive: props.jobTitle.is_executive ?? false,
    is_director: props.jobTitle.is_director ?? false,
    user_count: props.jobTitle.user_count ?? 0,
    users: props.jobTitle.users ?? [],
    creator: props.jobTitle.creator ?? null,
    updater: props.jobTitle.updater ?? null,
    permissions: props.jobTitle.permissions ?? { view: false, update: false, delete: false },
})

const hasUsers = computed(() => jobTitle.value.user_count > 0)
const canEdit = computed(() => jobTitle.value.permissions?.update && !hasUsers.value)
const canDelete = computed(() => jobTitle.value.permissions?.delete && !hasUsers.value)

const groupLabels: Record<string, string> = {
    c_suite: 'C-Suite',
    executive: 'Executive',
    director: 'Director',
}

const groupClasses: Record<string, string> = {
    c_suite: 'bg-purple-100 text-purple-700',
    executive: 'bg-blue-100 text-blue-700',
    director: 'bg-green-100 text-green-700',
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Job Titles', href: route('job-titles.index') },
    { title: jobTitle.value.title, href: route('job-titles.show', { jobTitle: jobTitle.value.id }) },
]

async function loadJobTitle() {
    const data = await fetchJobTitle(jobTitle.value.id)
    Object.assign(jobTitle.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this job title?')) return
    await deleteJobTitles(jobTitle.value.id)
    window.location.href = route('job-titles.index')
}

onMounted(() => loadJobTitle())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="jobTitle.title || 'Job Title'" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ jobTitle.title }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span v-if="jobTitle.short_code" class="text-sm text-gray-500 font-mono">
                                {{ jobTitle.short_code }}
                            </span>
                            <span
                                v-if="jobTitle.group"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="groupClasses[jobTitle.group] ?? 'bg-gray-100 text-gray-600'"
                            >
                                {{ groupLabels[jobTitle.group] ?? jobTitle.group }}
                            </span>
                            <span v-if="jobTitle.is_csuite" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">C-Suite</span>
                            <span v-else-if="jobTitle.is_director" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Director</span>
                            <span v-else-if="jobTitle.is_executive" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Executive</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="canEdit"
                            :href="route('job-titles.edit', { jobTitle: jobTitle.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>
                        <span
                            v-else-if="jobTitle.permissions?.update && hasUsers"
                            class="px-4 py-2 rounded bg-gray-100 text-gray-400 text-sm cursor-not-allowed"
                            title="Cannot edit a job title with assigned users"
                        >
                            Edit
                        </span>
                        <Link
                            :href="route('job-titles.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>
                        <button
                            v-if="canDelete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                        <span
                            v-else-if="jobTitle.permissions?.delete && hasUsers"
                            class="px-4 py-2 rounded bg-gray-100 text-gray-400 text-sm cursor-not-allowed"
                            title="Cannot delete a job title with assigned users"
                        >
                            Delete
                        </span>
                    </div>
                </div>

                <!-- Locked notice -->
                <div v-if="hasUsers" class="mb-6 px-4 py-3 rounded border border-amber-200 bg-amber-50 text-amber-800 text-sm">
                    This job title has {{ jobTitle.user_count }} assigned {{ jobTitle.user_count === 1 ? 'user' : 'users' }} and cannot be edited or deleted.
                </div>

                <!-- Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6">
                    <div>
                        <span class="font-semibold">Users with this title: </span>
                        <span>{{ jobTitle.user_count }}</span>
                    </div>

                    <div v-if="jobTitle.creator">
                        <span class="font-semibold">Created By: </span>
                        <span>{{ jobTitle.creator.name }}</span>
                    </div>

                    <div v-if="jobTitle.updater">
                        <span class="font-semibold">Last Updated By: </span>
                        <span>{{ jobTitle.updater.name }}</span>
                    </div>
                </div>

                <!-- Users -->
                <div v-if="jobTitle.users && jobTitle.users.length > 0">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-3">Users</h2>
                    <table class="w-full border text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="p-2 text-left">Name</th>
                                <th class="p-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in jobTitle.users" :key="user.id" class="border-t">
                                <td class="p-2">{{ user.name }}</td>
                                <td class="p-2 text-right">
                                    <Link
                                        :href="route('users.show', { user: user.id })"
                                        class="text-xs"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AppLayout>
</template>