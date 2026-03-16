<script setup lang="ts">
    import { ref } from 'vue'

    const props = defineProps<{
        modelValue: File | null
        avatarUrl?: string | null
    }>()
    const emit = defineEmits(['update:modelValue'])
    const preview = ref<string | null>(props.avatarUrl ?? null)

    function handleFile(e: Event) {
        const file = (e.target as HTMLInputElement).files?.[0]

        if (!file) return
        preview.value = URL.createObjectURL(file)
        emit('update:modelValue', file)
    }
</script>
<template>
    <div class="space-y-3">
        <div v-if="preview">
            <img
                :src="preview"
                class="w-20 h-20 rounded-full object-cover"
            />
        </div>

        <input
            type="file"
            accept="image/*"
            @change="handleFile"
        />
    </div>
</template>