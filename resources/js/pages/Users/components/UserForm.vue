<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'
import UserAvatarUpload from './UserAvatarUpload.vue'
import UserDetailsSection from './UserDetailsSection.vue'
import UserPasswordSection from './UserPasswordSection.vue'

interface Role { id: number; name: string }
interface JobTitle { id: number; title: string }
interface User {
    id?: number
    name: string
    email: string
    role_id: number | null
    job_title_id: number | null
    avatar_url?: string | null
}

const props = defineProps<{
    user?: User
    roles: Role[]
    jobTitles: JobTitle[]
    method?: 'post' | 'put'
}>()

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    role_id: props.user?.role_id ?? null,
    job_title_id: props.user?.job_title_id ?? null,
    avatar: null as File | null,
    password: '',
    password_confirmation: ''
})

async function submit() {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true });

        const formData = new FormData();

        Object.entries(form.data()).forEach(([key, value]) => {
            if ((key === 'password' || key === 'password_confirmation') && !value) return;

            if (value !== null && value !== undefined) {
                if (value instanceof File) {
                    formData.append(key, value);
                } else {
                    formData.append(key, String(value));
                }
            }
        });

        const url =
            props.method === 'put' && props.user?.id
                ? `/api/users/${props.user.id}`
                : `/api/users`;

        const response = await axios({
            method: props.method === 'put' ? 'put' : 'post',
            url,
            data: formData,
            withCredentials: true,
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        router.visit(`/users/${response.data.id}`);

    } catch (err: any) {
        console.error(err.response?.data ?? err);

        if (err.response?.status === 422) {
            form.setError(err.response.data.errors);
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-xl">
        <UserAvatarUpload v-model="form.avatar" :avatar-url="props.user?.avatar_url" />

        <UserDetailsSection :form="form" :roles="props.roles" :jobTitles="props.jobTitles" />

        <UserPasswordSection :form="form" />

        <div>
            <button
                class="bg-blue-600 text-white px-5 py-2 rounded"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : 'Save User' }}
            </button>
        </div>
    </form>
</template>