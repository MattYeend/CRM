<script setup lang="ts">
import { ref, computed, watch } from 'vue'

interface AttachmentFormData {
    file: File | null
    title: string
    description: string
    attachable_type: string
    attachable_id: number | null
    is_public: boolean
    errors: Record<string, string>
    processing: boolean
}

interface EntityOption {
    id: number
    name: string
}

const props = defineProps<{
    form: AttachmentFormData
    cancelHref: string
    submitLabel?: string
    showEntityFields?: boolean
    subjectTypes?: string[]
}>()

const emit = defineEmits<{
    (e: 'update:file', value: File | null): void
    (e: 'update:title', value: string): void
    (e: 'update:description', value: string): void
    (e: 'update:attachable_type', value: string): void
    (e: 'update:attachable_id', value: number | null): void
    (e: 'update:is_public', value: boolean): void
}>()

const isDragging = ref(false)
const selectedFileName = ref('')

const entityOptions = ref<EntityOption[]>([])
const loadingEntities = ref(false)

const typeApiMap: Record<string, string> = {
    lead: 'leads',
    contact: 'contacts',
    company: 'companies',
    deal: 'deals',
    activity: 'activities',
}

watch(
    () => props.form.attachable_type,
    async (type) => {
        emit('update:attachable_id', null)
        entityOptions.value = []

        if (!type) return

        const endpoint = typeApiMap[type.toLowerCase()]
        if (!endpoint) return

        loadingEntities.value = true
        try {
            const { default: axios } = await import('axios')
            const response = await axios.get(`/api/${endpoint}`)
            const items = response.data.data ?? response.data ?? []
            entityOptions.value = items.map((item: any) => ({
                id: item.id,
                name: item.name ?? item.title ?? `#${item.id}`,
            }))
        } catch (err) {
            console.error('Failed to load entities:', err)
            entityOptions.value = []
        } finally {
            loadingEntities.value = false
        }
    }
)

function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0] ?? null
    if (file) {
        emit('update:file', file)
        selectedFileName.value = file.name
    }
}

function handleDrop(event: DragEvent) {
    isDragging.value = false
    const file = event.dataTransfer?.files?.[0] ?? null
    if (file) {
        emit('update:file', file)
        selectedFileName.value = file.name
    }
}

const normalizedSubjectTypes = computed(() =>
    (props.subjectTypes ?? []).map(t => t.split('\\').pop()?.toLowerCase() ?? t)
)
</script>

<template>
    <div class="space-y-6">
        <!-- File Upload -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">File</label>
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
            <p v-if="form.errors?.file" class="mt-1 text-sm text-red-600">{{ form.errors.file }}</p>
        </div>

        <!-- Attachable Type -->
        <div v-if="showEntityFields !== false">
            <label for="attachable_type" class="block text-sm font-medium text-gray-700 mb-1">Attach To</label>
            <select
                id="attachable_type"
                :value="form.attachable_type"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="emit('update:attachable_type', ($event.target as HTMLSelectElement).value)"
            >
                <option value="">Select entity type</option>
                <option
                    v-for="type in normalizedSubjectTypes"
                    :key="type"
                    :value="type"
                >
                    {{ type.charAt(0).toUpperCase() + type.slice(1) }}
                </option>
            </select>
            <p v-if="form.errors?.attachable_type" class="mt-1 text-sm text-red-600">{{ form.errors.attachable_type }}</p>
        </div>

        <!-- Attachable ID -->
        <div v-if="showEntityFields !== false && form.attachable_type">
            <label for="attachable_id" class="block text-sm font-medium text-gray-700 mb-1">Entity</label>
            <select
                id="attachable_id"
                :value="form.attachable_id"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :disabled="loadingEntities"
                @change="emit('update:attachable_id', Number(($event.target as HTMLSelectElement).value) || null)"
            >
                <option :value="null">{{ loadingEntities ? 'Loading...' : 'Select entity' }}</option>
                <option
                    v-for="entity in entityOptions"
                    :key="entity.id"
                    :value="entity.id"
                >
                    {{ entity.name }}
                </option>
            </select>
            <p v-if="form.errors?.attachable_id" class="mt-1 text-sm text-red-600">{{ form.errors.attachable_id }}</p>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 pt-2">
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save') }}
            </button>
            <a
                :href="cancelHref"
                class="px-5 py-2 rounded text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50"
            >
                Cancel
            </a>
        </div>
    </div>
</template>