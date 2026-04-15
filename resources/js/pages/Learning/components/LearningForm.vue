<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

interface User {
    id: number
    name: string
}

interface LearningAnswer {
    answer: string
    is_correct: boolean
}

interface LearningQuestion {
    question: string
    answers: LearningAnswer[]
}

interface Learning {
    id?: number
    title?: string
    description?: string | null
    is_test?: boolean
    questions?: LearningQuestion[]
}

const props = defineProps<{
    learning?: Learning
    users?: User[]
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    title: props.learning?.title ?? '',
    description: props.learning?.description ?? '',
    is_test: props.learning?.is_test ?? false,
})

// Local reactive questions state (not part of useForm to allow dynamic add/remove)
const questions = ref<LearningQuestion[]>(
    props.learning?.questions?.map(q => ({
        question: q.question,
        answers: q.answers?.length
            ? q.answers.map(a => ({ answer: a.answer, is_correct: a.is_correct }))
            : [
                { answer: '', is_correct: true },
                { answer: '', is_correct: false },
            ],
    })) ?? []
)

function addQuestion() {
    questions.value.push({
        question: '',
        answers: [
            { answer: '', is_correct: true },
            { answer: '', is_correct: false },
        ],
    })
}

function removeQuestion(qIndex: number) {
    questions.value.splice(qIndex, 1)
}

function addAnswer(qIndex: number) {
    questions.value[qIndex].answers.push({ answer: '', is_correct: false })
}

function removeAnswer(qIndex: number, aIndex: number) {
    questions.value[qIndex].answers.splice(aIndex, 1)
}

function setCorrect(qIndex: number, aIndex: number) {
    // Only one correct answer per question
    questions.value[qIndex].answers.forEach((a, i) => {
        a.is_correct = i === aIndex
    })
}

async function submit() {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const payload = {
            ...form.data(),
            questions: questions.value,
        }

        const response = await axios({
            method: props.method === 'put' ? 'put' : 'post',
            url: props.submitRoute,
            data: payload,
            withCredentials: true,
            headers: { 'Content-Type': 'application/json' },
        })

        router.visit(`/learnings/${response.data.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err)

        if (err.response?.status === 422) {
            const raw = err.response.data.errors as Record<string, string[]>
            const flat = Object.fromEntries(
                Object.entries(raw).map(([key, messages]) => [key, messages[0]])
            ) as Record<string, string>
            form.setError(flat)
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-3xl">

        <!-- Learning Details -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Learning Details</h2>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Title <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.title"
                    type="text"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g. Health & Safety Induction"
                />
                <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Brief description of this learning module..."
                />
                <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
            </div>
        </div>

        <!-- Questions -->
        <div class="space-y-4">
            <div class="flex items-center justify-between border-b pb-2">
                <h2 class="text-lg font-semibold">Questions</h2>
                <button
                    type="button"
                    @click="addQuestion"
                    class="text-sm bg-blue-50 text-blue-600 border border-blue-200 px-3 py-1 rounded hover:bg-blue-100"
                >
                    + Add Question
                </button>
            </div>

            <div v-if="questions.length === 0" class="text-sm text-gray-400 italic">
                No questions added yet. Click "Add Question" to begin.
            </div>

            <div
                v-for="(question, qIndex) in questions"
                :key="qIndex"
                class="border rounded p-4 space-y-3"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-1">
                            Question {{ qIndex + 1 }}
                        </label>
                        <input
                            v-model="question.question"
                            type="text"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter question text..."
                        />
                    </div>
                    <button
                        type="button"
                        @click="removeQuestion(qIndex)"
                        class="text-red-500 text-sm mt-6 hover:text-red-700 shrink-0"
                    >
                        Remove
                    </button>
                </div>

                <!-- Answers -->
                <div class="ml-4 space-y-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        Answers — select the correct one
                    </p>

                    <div
                        v-for="(answer, aIndex) in question.answers"
                        :key="aIndex"
                        class="flex items-center gap-2"
                    >
                        <input
                            type="radio"
                            :name="`question-${qIndex}-correct`"
                            :checked="answer.is_correct"
                            @change="setCorrect(qIndex, aIndex)"
                            class="accent-green-600 shrink-0"
                            title="Mark as correct answer"
                        />
                        <input
                            v-model="answer.answer"
                            type="text"
                            class="flex-1 border rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :placeholder="`Answer ${aIndex + 1}`"
                        />
                        <button
                            v-if="question.answers.length > 2"
                            type="button"
                            @click="removeAnswer(qIndex, aIndex)"
                            class="text-red-400 text-xs hover:text-red-600 shrink-0"
                        >
                            ✕
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="addAnswer(qIndex)"
                        class="text-xs text-blue-500 hover:text-blue-700"
                    >
                        + Add Answer
                    </button>
                </div>
            </div>
        </div>

        <div>
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Learning') }}
            </button>
        </div>
    </form>
</template>