<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
    form: any
}>()

const emit = defineEmits(['update:form'])

const localForm = ref({ ...props.form })

watch(
    localForm,
    (newVal) => emit('update:form', newVal),
    { deep: true }
)
</script>

<template>
    <div class="space-y-5">
        <h2 class="text-lg font-semibold">Password</h2>

        <div>
            <label class="block font-medium">Password</label>
            <input type="password" v-model="localForm.password" class="border rounded w-full p-2"/>
        </div>

        <div>
            <label class="block font-medium">Confirm Password</label>
            <input type="password" v-model="localForm.password_confirmation" class="border rounded w-full p-2"/>
        </div>
    </div>
</template>