<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'

interface FormData {
    file: File | null
    attachable_type: string
    attachable_id: number | null
}

interface EntityOption {
    id: number
    name: string
}

const props = defineProps<{
    errors: Record<string, string>
    attachableType?: string[]
}>()

const form = defineModel<FormData>({ required: true })

const entityOptions = ref<EntityOption[]>([])
const loadingEntities = ref(false)

const typeApiMap: Record<string, string> = {
    lead: 'leads',
    contact: 'contacts',
    company: 'companies',
    deal: 'deals',
    activity: 'activities',
    task: 'tasks',
    user: 'users',
    order: 'orders',
}

const normalizedAttachableTypes = computed(() =>
    (props.attachableType ?? []).map(t => {
        const short = t.split('\\').pop()?.toLowerCase() ?? t.toLowerCase()
        return {
            value: short,
            label: short.charAt(0).toUpperCase() + short.slice(1),
            short,
        }
    })
)

async function loadEntities(type: string) {
    if (!type) {
        entityOptions.value = []
        return
    }

    const short = type.split('\\').pop()?.toLowerCase() ?? type.toLowerCase()
    const endpoint = typeApiMap[short]
    if (!endpoint) {
        console.warn(`No API endpoint found for type: ${type} (short: ${short})`)
        entityOptions.value = []
        return
    }

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

watch(
    () => form.value.attachable_type,
    async (newType, oldType) => {
        // Only reset attachable_id if type actually changed (and not on initial mount)
        if (newType !== oldType && oldType !== undefined) {
            form.value.attachable_id = null
        }
        await loadEntities(newType)
    }
)

onMounted(() => {
    // Load entities on mount if type is already set (edit mode)
    if (form.value.attachable_type) {
        loadEntities(form.value.attachable_type)
    }
})
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Attach To</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="attachable_type" class="block text-sm font-medium mb-1">
                    Entity Type
                </label>
                <select
                    id="attachable_type"
                    v-model="form.attachable_type"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">— Select Type —</option>
                    <option
                        v-for="type in normalizedAttachableTypes"
                        :key="type.value"
                        :value="type.value"
                    >
                        {{ type.label }}
                    </option>
                </select>
                <p v-if="errors?.attachable_type" class="mt-1 text-sm text-red-600">
                    {{ errors.attachable_type }}
                </p>
            </div>

            <div v-if="form.attachable_type">
                <label for="attachable_id" class="block text-sm font-medium mb-1">
                    Entity Name
                </label>
                <select
                    id="attachable_id"
                    v-model.number="form.attachable_id"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :disabled="loadingEntities"
                >
                    <option :value="null">
                        {{ loadingEntities ? 'Loading...' : '— Select Entity —' }}
                    </option>
                    <option
                        v-for="entity in entityOptions"
                        :key="entity.id"
                        :value="entity.id"
                    >
                        {{ entity.name }}
                    </option>
                </select>
                <p v-if="errors?.attachable_id" class="mt-1 text-sm text-red-600">
                    {{ errors.attachable_id }}
                </p>
            </div>
        </div>
    </div>
</template>