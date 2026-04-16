<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
// eslint-disable-next-line @typescript-eslint/no-unused-vars
import { fetchLearning } from '@/services/learningService'
interface LearningUser {
    id: number
    name: string
    pivot?: {
        is_complete: boolean
        score: number | null
        completed_at: string | null
    }
}

interface Learning {
    id: number
    title: string
    description: string | null
    users: LearningUser[]
    permissions: {
        view: boolean
    }
}

const props = defineProps<{ learning: any }>()

const learning = ref<Learning>({
    id: props.learning.id,
    title: props.learning.title ?? '',
    description: props.learning.description ?? null,
    users: props.learning.users ?? [],
    permissions: props.learning.permissions ?? { view: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Learnings', href: route('learnings.index') },
    { title: learning.value.title, href: route('learnings.show', { learning: learning.value.id }) },
    { title: 'Results', href: route('learnings.results', { learning: learning.value.id }) },
]

const userPivot = computed(() => learning.value.users?.[0]?.pivot ?? null)
const score = computed(() => userPivot.value?.score ?? null)
const completedAt = computed(() => userPivot.value?.completed_at ?? null)

const scoreLabel = computed(() => {
    const s = score.value
    if (s === null) return null
    if (s >= 80) return { label: 'Excellent', classes: 'text-green-700' }
    if (s >= 60) return { label: 'Good', classes: 'text-blue-700' }
    if (s >= 40) return { label: 'Needs Improvement', classes: 'text-amber-700' }
    return { label: 'Not Passed', classes: 'text-red-700' }
})

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Results: ${learning.title}`" />

        <div class="p-6">
            <div class="mx-auto max-w-lg border rounded shadow p-8 text-center">

                <!-- Icon -->
                <div class="text-5xl mb-4">
                    {{ score !== null && score >= 60 ? '🎉' : '📋' }}
                </div>

                <h1 class="text-2xl font-bold mb-1">{{ learning.title }}</h1>
                <p class="text-gray-500 mb-6">Learning complete</p>

                <!-- Score -->
                <div v-if="score !== null" class="mb-6">
                    <div class="text-6xl font-bold mb-1" :class="scoreLabel?.classes">
                        {{ score }}%
                    </div>
                    <p class="text-sm font-medium" :class="scoreLabel?.classes">
                        {{ scoreLabel?.label }}
                    </p>
                </div>

                <div v-if="completedAt" class="text-sm text-gray-400 mb-8">
                    Completed {{ formatDate(completedAt) }}
                </div>

                <!-- Actions -->
                <div class="flex justify-center gap-3 flex-wrap">
                    <Link
                        :href="route('learnings.show', { learning: learning.id })"
                        class="bg-blue-600 text-white px-5 py-2 rounded"
                    >
                        View Learning
                    </Link>
                    <Link
                        :href="route('learnings.index')"
                        class="bg-gray-200 text-gray-700 px-5 py-2 rounded"
                    >
                        All Learnings
                    </Link>
                </div>

            </div>
        </div>
    </AppLayout>
</template>