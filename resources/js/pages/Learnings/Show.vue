<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, computed, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import {
    fetchLearning,
    deleteLearnings,
    completeLearning,
    incompleteLearning,
} from '@/services/learningService'
import LearningDetailSection from './components/LearningDetailSection.vue'

interface LearningAnswer {
    id: number
    answer: string
    is_correct: boolean
}

interface LearningQuestion {
    id: number
    question: string
    answers: LearningAnswer[]
}

interface LearningUser {
    id: number
    name: string
    pivot?: {
        is_complete: boolean
        score: number | null
        completed_at: string | null
    }
}

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Learning {
    id: number
    title: string
    description: string | null
    date: string | null
    pass_score: number | null
    questions?: LearningQuestion[]
    users: LearningUser[]
    current_user?: {
        is_complete: boolean
        score: number | null
        completed_at: string | null
    }
    creator: { id: number; name: string } | null
    updater: { id: number; name: string } | null
    created_at: string | null
    updated_at: string | null
    permissions: UserPermissions
}

const props = defineProps<{ learning: any }>()

const learning = ref<Learning>({
    id: props.learning.id,
    title: props.learning.title ?? '',
    description: props.learning.description ?? null,
    date: props.learning.date ?? null,
    pass_score: props.learning.pass_score ?? null,
    questions: props.learning.questions ?? [],
    users: props.learning.users ?? [],
    creator: props.learning.creator ?? null,
    updater: props.learning.updater ?? null,
    created_at: props.learning.created_at ?? null,
    updated_at: props.learning.updated_at ?? null,
    permissions: props.learning.permissions ?? { view: false, update: false, delete: false },
})

const isComplete = computed(() => learning.value.current_user?.is_complete ?? false)
const score = computed(() => learning.value.current_user?.score ?? null)

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Learnings', href: route('learnings.index') },
    { title: learning.value.title, href: route('learnings.show', { learning: learning.value.id }) },
]

async function loadLearning() {
    const data = await fetchLearning(learning.value.id)
    Object.assign(learning.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this learning?')) return
    await deleteLearnings(learning.value.id)
    window.location.href = route('learnings.index')
}

async function handleComplete() {
    await completeLearning(learning.value.id, score.value??0)
    window.location.href = route('learnings.complete', { learning: learning.value.id })
}

async function handleIncomplete() {
    if (!confirm('Mark this learning as incomplete?')) return
    await incompleteLearning(learning.value.id)
    await loadLearning()
}

onMounted(() => loadLearning())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="learning.title || 'Learning'" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ learning.title }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span
                                v-if="isComplete"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700"
                            >
                                Complete
                            </span>
                            <span
                                v-else
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700"
                            >
                                Incomplete
                            </span>
                            <span
                                v-if="score !== null"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"
                            >
                                Score: {{ score }}%
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 flex-wrap gap-y-2">
                        <button
                            v-if="!isComplete"
                            @click="handleComplete"
                            class="bg-green-600 text-white px-4 py-2 rounded text-sm"
                        >
                            Take Learning
                        </button>
                        <button
                            v-if="isComplete"
                            @click="handleIncomplete"
                            class="bg-amber-500 text-white px-4 py-2 rounded text-sm"
                        >
                            Mark Incomplete
                        </button>
                        <Link
                            v-if="learning.permissions.update"
                            :href="route('learnings.edit', { learning: learning.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded text-sm"
                        >
                            Edit
                        </Link>
                        <Link
                            :href="route('learnings.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm"
                        >
                            Back
                        </Link>
                        <button
                            v-if="learning.permissions.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded text-sm"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <LearningDetailSection :learning="learning" />
            </div>
        </div>
    </AppLayout>
</template>