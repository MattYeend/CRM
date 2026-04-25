<script setup lang="ts">
import { computed } from 'vue'

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

interface Learning {
    id: number
    title: string
    description: string | null
    date: string | null
    pass_score: number | null
    questions?: LearningQuestion[]
    creator: { id: number; name: string } | null
    updater: { id: number; name: string } | null
    created_at: string | null
    updated_at: string | null
    current_user?: {
        is_complete: boolean
        score: number | null
        completed_at: string | null
    }
}

const props = defineProps<{ learning: Learning }>()

const questionCount = computed(() => props.learning.questions?.length ?? 0)
const completedAt = computed(() => props.learning.current_user?.completed_at ?? null)

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}
</script>

<template>
    <div>
        <!-- Details -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                <div v-if="learning.description" class="md:col-span-2">
                    <span class="font-semibold">Description: </span>
                    <span class="text-gray-300">{{ learning.description }}</span>
                </div>
                <div>
                    <span class="font-semibold">Questions: </span>
                    <span>{{ questionCount }}</span>
                </div>
                <div v-if="completedAt">
                    <span class="font-semibold">Completed: </span>
                    <span>{{ formatDate(completedAt) }}</span>
                </div>
            </div>
        </div>

        <!-- Questions -->
        <div v-if="learning.questions && learning.questions.length > 0" class="mb-6">
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">
                Questions ({{ questionCount }})
            </h2>
            <div class="space-y-4">
                <div
                    v-for="(question, index) in learning.questions"
                    :key="question.id"
                    class="border rounded p-4"
                >
                    <p class="font-medium mb-2">
                        {{ index + 1 }}. {{ question.question }}
                    </p>
                    <ul v-if="question.answers?.length" class="space-y-1 ml-4">
                        <li
                            v-for="answer in question.answers"
                            :key="answer.id"
                            class="flex items-center gap-2 text-sm"
                        >
                            <span
                                class="w-2 h-2 rounded-full flex-shrink-0 bg-gray-300"
                            />
                            <span class='text-gray-300'>
                                {{ answer.answer }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Audit -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-gray-600">
            <div v-if="learning.creator">
                <span class="font-semibold">Created By: </span>
                <span>{{ learning.creator.name }}</span>
            </div>
            <div v-if="learning.created_at">
                <span class="font-semibold">Created: </span>
                <span>{{ formatDate(learning.created_at) }}</span>
            </div>
        </div>
    </div>
</template>