<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { createPartCategory, updatePartCategory } from '@/services/partService'

interface Category {
    id: number
    name: string
}

interface PartCategory {
    id?: number
    name?: string
    description?: string
    parent_id?: number | null
}

const props = defineProps<{
    partCategory?: PartCategory
    categories: Category[]
    method?: 'post' | 'put'
    submitLabel?: string
}>()

const form = useForm({
    name: props.partCategory?.name ?? '',
    description: props.partCategory?.description ?? '',
    parent_id: props.partCategory?.parent_id ?? null,
})

async function submit() {
    try {
        const payload = form.data()
        let result

        if (props.method === 'put' && props.partCategory?.id) {
            result = await updatePartCategory(props.partCategory.id, payload)
        } else {
            result = await createPartCategory(payload)
        }

        router.visit(`/part-categories/${result.id}`)
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
    <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Category Details</h2>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Name <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Fasteners"
                />
                <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                    {{ form.errors.name }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Parent Category</label>
                <select v-model="form.parent_id" class="w-full border rounded px-3 py-2">
                    <option :value="null">-- No parent (top level) --</option>
                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="category.id"
                        :disabled="category.id === partCategory?.id"
                    >
                        {{ category.name }}
                    </option>
                </select>
                <p v-if="form.errors.parent_id" class="text-red-500 text-sm mt-1">
                    {{ form.errors.parent_id }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea
                    v-model="form.description"
                    class="w-full border rounded px-3 py-2"
                    rows="3"
                    placeholder="A brief description of this category..."
                />
                <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                    {{ form.errors.description }}
                </p>
            </div>
        </div>

        <div>
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Category') }}
            </button>
        </div>
    </form>
</template>