<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { createPartImage, updatePartImage } from '@/services/partService'

interface Part {
    id: number
    sku: string
    name: string
}

interface PartImage {
    id?: number
    part_id?: number | null
    alt?: string
    is_primary?: boolean
    sort_order?: number | null
}

const props = defineProps<{
    partImage?: PartImage
    parts: Part[]
    method?: 'post' | 'put'
    submitLabel?: string
}>()

const form = useForm({
    part_id: props.partImage?.part_id ?? null,
    alt: props.partImage?.alt ?? '',
    is_primary: props.partImage?.is_primary ?? false,
    sort_order: props.partImage?.sort_order ?? null,
    image: null as File | null,
})

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement
    form.image = target.files?.[0] ?? null
}

async function submit() {
    try {
        const payload = new FormData()
        payload.append('part_id', String(form.part_id ?? ''))
        payload.append('alt', form.alt)
        payload.append('is_primary', form.is_primary ? '1' : '0')
        if (form.sort_order != null) payload.append('sort_order', String(form.sort_order))
        if (form.image) payload.append('image', form.image)

        let result

        if (props.method === 'put' && props.partImage?.id) {
            payload.append('_method', 'PUT')
            result = await updatePartImage(props.partImage.id, payload)
        } else {
            result = await createPartImage(payload)
        }

        router.visit(`/part-images/${result.id}`)
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
    <form @submit.prevent="submit" class="space-y-6 max-w-2xl" enctype="multipart/form-data">
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Image Details</h2>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Part <span class="text-red-500">*</span>
                </label>
                <select v-model="form.part_id" class="w-full border rounded px-3 py-2">
                    <option :value="null">-- Select a part --</option>
                    <option
                        v-for="part in parts"
                        :key="part.id"
                        :value="part.id"
                    >
                        {{ part.sku }} — {{ part.name }}
                    </option>
                </select>
                <p v-if="form.errors.part_id" class="text-red-500 text-sm mt-1">
                    {{ form.errors.part_id }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Image <span v-if="!partImage?.id" class="text-red-500">*</span>
                </label>
                <input
                    type="file"
                    accept="image/*"
                    class="w-full border rounded px-3 py-2"
                    @change="handleFileChange"
                />
                <p v-if="form.errors.image" class="text-red-500 text-sm mt-1">
                    {{ form.errors.image }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Alt Text</label>
                <input
                    v-model="form.alt"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="A descriptive label for this image"
                />
                <p v-if="form.errors.alt" class="text-red-500 text-sm mt-1">
                    {{ form.errors.alt }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Sort Order</label>
                <input
                    v-model="form.sort_order"
                    type="number"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0"
                />
                <p v-if="form.errors.sort_order" class="text-red-500 text-sm mt-1">
                    {{ form.errors.sort_order }}
                </p>
            </div>

            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        v-model="form.is_primary"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300"
                    />
                    <div>
                        <span class="text-sm font-medium">Primary Image</span>
                        <p class="text-xs text-gray-500">
                            Set this as the main display image for the part
                        </p>
                    </div>
                </label>
                <p v-if="form.errors.is_primary" class="text-red-500 text-sm mt-1">
                    {{ form.errors.is_primary }}
                </p>
            </div>
        </div>

        <div>
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Image') }}
            </button>
        </div>
    </form>
</template>