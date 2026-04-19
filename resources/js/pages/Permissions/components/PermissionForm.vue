<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

import PermissionDetailsSection from './PermissionDetailsSection.vue'
import PermissionAssociationsSection from './PermissionAssociationsSection.vue'

interface SelectOption {
    id: number
    name: string
}

interface Permission {
    id?: number
    name?: string
    label?: string
    is_test?: boolean
    meta?: Record<string, any> | null
    roles?: Array<{ id: number }>
}

interface PermissionFormData {
    name: string
    label: string
    role_ids: number[]
    is_test: boolean
}

const props = defineProps<{
    permission?: Permission
    roles: SelectOption[]
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm<PermissionFormData>({
    name: props.permission?.name ?? '',
    label: props.permission?.label ?? '',
    role_ids: props.permission?.roles?.map(r => r.id) ?? [],
    is_test: props.permission?.is_test ?? false,
})

async function submit() {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const payload = { ...form.data() }

        const response = await axios({
            method: props.method === 'put' ? 'put' : 'post',
            url: props.submitRoute,
            data: payload,
            withCredentials: true,
            headers: { 'Content-Type': 'application/json' },
        })

        router.visit(`/permissions/${response.data.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err);

        if (err.response?.status === 422 && err.response.data.errors) {
            const errors = err.response.data.errors as Record<string, string[]>
            Object.keys(errors).forEach(key => {
                form.setError(key as keyof PermissionFormData, errors[key][0])
            })
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-2xl">

        <PermissionDetailsSection
            :name="form.name"
            :label="form.label"
            :errors="form.errors"
            @update:name="form.name = $event"
            @update:label="form.label = $event"
        />

        <PermissionAssociationsSection
            :role-ids="form.role_ids"
            :roles="roles"
            :errors="form.errors"
            @update:role-ids="form.role_ids = $event"
        />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Permission') }}
        </button>

    </form>
</template>