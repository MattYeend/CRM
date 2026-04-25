<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, computed, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchJobTitle, deleteJobTitles } from '@/services/jobTitleService'
import JobTitleDetailSection from './components/JobTitleDetailSection.vue'

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
                            <span v-if="jobTitle.short_code" class="text-sm text-gray-500">
                                {{ jobTitle.short_code }}
                            </span>
                            <span
                                v-if="jobTitle.group"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="groupClasses[jobTitle.group] ?? 'bg-gray-100 text-gray-600'"
                            >
                                {{ groupLabels[jobTitle.group] ?? jobTitle.group }}
                            </span>
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

                <JobTitleDetailSection :job-title="jobTitle" />
            </div>
        </div>
    </AppLayout>
</template>