<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

import LearningDetailsSection from './LearningDetailsSection.vue'
import LearningQuestionsSection from './LearningQuestionsSection.vue'

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
    pass_score?: number
    questions?: LearningQuestion[]
    users?: User[]
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
    pass_score: props.learning?.pass_score ?? null,
    users: props.learning?.users?.map(u => u.id) ?? [],
})

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

        <LearningDetailsSection
            :form="form"
            :users="users"
        />

        <LearningQuestionsSection
            :questions="questions"
            :addQuestion="addQuestion"
            :removeQuestion="removeQuestion"
            :addAnswer="addAnswer"
            :removeAnswer="removeAnswer"
            :setCorrect="setCorrect"
        />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Learning') }}
        </button>
    </form>
</template>