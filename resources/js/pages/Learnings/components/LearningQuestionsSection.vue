<script setup lang="ts">
interface LearningAnswer {
    answer: string
    is_correct: boolean
}

interface LearningQuestion {
    question: string
    answers: LearningAnswer[]
}

defineProps<{
    questions: LearningQuestion[]
    addQuestion: () => void
    removeQuestion: (qIndex: number) => void
    addAnswer: (qIndex: number) => void
    removeAnswer: (qIndex: number, aIndex: number) => void
    setCorrect: (qIndex: number, aIndex: number) => void
}>()
</script>

<template>
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
</template>