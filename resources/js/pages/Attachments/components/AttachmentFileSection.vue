<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface FormData {
    file: File | null
    attachable_type: string
    attachable_id: number | null
}

const props = defineProps<{
    errors: Record<string, string>
    currentFilename?: string
}>()

const form = defineModel<FormData>({ required: true })

const isDragging = ref(false)
const selectedFileName = ref('')

onMounted(() => {
    if (props.currentFilename) {
        selectedFileName.value = props.currentFilename
    }
})

function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0] ?? null
    if (file) {
        form.value.file = file
        selectedFileName.value = file.name
    }
}

function handleDrop(event: DragEvent) {
    isDragging.value = false
    const file = event.dataTransfer?.files?.[0] ?? null
    if (file) {
        form.value.file = file
        selectedFileName.value = file.name
    }
}
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">File Upload</h2>

        <div>
            <label class="block text-sm font-medium mb-1">File</label>
            <div
                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md transition-colors"
                :class="{ 'border-blue-400 bg-blue-50': isDragging }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
            >
                <div class="space-y-1 text-center">
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 48 48"
                        aria-hidden="true"
                    >
                        <path
                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <div class="flex text-sm text-gray-600 justify-center">
                        <label
                            for="file-upload"
                            class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
                        >
                            <span>Upload a file</span>
                            <input
                                id="file-upload"
                                name="file-upload"
                                type="file"
                                class="sr-only"
                                @change="handleFileChange"
                            />
                        </label>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF, PDF, DOCX, XLSX up to 10MB</p>
                    <p v-if="selectedFileName" class="text-sm text-blue-600 font-medium">
                        Selected: {{ selectedFileName }}
                    </p>
                </div>
            </div>
            <p v-if="errors?.file" class="mt-1 text-sm text-red-600">{{ errors.file }}</p>
        </div>
    </div>
</template>