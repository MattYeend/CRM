<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import axios from 'axios'

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
    questions: LearningQuestion[]
    permissions: {
        complete: boolean
    }
}

const props = defineProps<{ learning: any }>()

const learning = ref<Learning>({
    id: props.learning.id,
    title: props.learning.title ?? '',
    description: props.learning.description ?? null,
    questions: props.learning.questions ?? [],
    permissions: props.learning.permissions ?? { complete: false },
})

const selectedAnswers = ref<Record<number, number>>({})
const submitting = ref(false)

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Learnings', href: route('learnings.index') },
    { title: learning.value.title, href: route('learnings.show', { learning: learning.value.id }) },
    { title: 'Take Learning', href: route('learnings.complete', { learning: learning.value.id }) },
]

const totalQuestions = computed(() => learning.value.questions.length)
const answeredCount = computed(() => Object.keys(selectedAnswers.value).length)
const allAnswered = computed(() => answeredCount.value === totalQuestions.value)

function selectAnswer(questionId: number, answerId: number) {
    selectedAnswers.value[questionId] = answerId
}

async function handleSubmit() {
    if (!allAnswered.value) return

    let correct = 0
    for (const question of learning.value.questions) {
        const selectedId = selectedAnswers.value[question.id]
        const selected = question.answers.find(a => a.id === selectedId)
        if (selected?.is_correct) correct++
    }

    const score = totalQuestions.value > 0
        ? Math.round((correct / totalQuestions.value) * 100)
        : 100

    submitting.value = true

    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        await axios.post(
            `/api/learnings/${learning.value.id}/complete`,
            { score },
            { withCredentials: true }
        )

        window.location.href = route('learnings.results', {
            learning: learning.value.id
        })
    } catch (err) {
        console.error(err)
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Take: ${learning.title}`" />

        <div class="p-6">
            <div class="mx-auto max-w-2xl">

                <div class="mb-6">
                    <h1 class="text-2xl font-bold">{{ learning.title }}</h1>

                    <p v-if="learning.description" class="text-gray-500 mt-1">
                        {{ learning.description }}
                    </p>

                    <p class="text-sm text-gray-400 mt-2">
                        {{ answeredCount }} / {{ totalQuestions }} answered
                    </p>
                </div>

                <div v-if="learning.questions.length" class="space-y-6">
                    <div
                        v-for="(question, index) in learning.questions"
                        :key="question.id"
                        class="border rounded p-5"
                    >
                        <p class="font-medium mb-3">
                            {{ index + 1 }}. {{ question.question }}
                        </p>

                        <div class="space-y-2">
                            <label
                                v-for="answer in question.answers"
                                :key="answer.id"
                                class="flex items-center gap-3 p-2 rounded cursor-pointer"
                            >
                                <input
                                    type="radio"
                                    :name="`question-${question.id}`"
                                    :checked="selectedAnswers[question.id] === answer.id"
                                    @change="selectAnswer(question.id, answer.id)"
                                    class="accent-blue-600"
                                />
                                <span class="text-sm">{{ answer.answer }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex items-center gap-4">
                    <button
                        @click="handleSubmit"
                        :disabled="!allAnswered || submitting"
                        class="bg-green-600 text-white px-6 py-2 rounded disabled:opacity-50"
                    >
                        {{ submitting ? 'Submitting...' : 'Submit' }}
                    </button>

                    <Link
                        :href="route('learnings.show', { learning: learning.id })"
                        class="text-gray-500 text-sm underline"
                    >
                        Cancel
                    </Link>

                    <span v-if="!allAnswered" class="text-sm text-amber-600">
                        Please answer all questions.
                    </span>
                </div>

            </div>
        </div>
    </AppLayout>
</template>