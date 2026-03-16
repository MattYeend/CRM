<script setup lang="ts">
    import { useForm } from '@inertiajs/vue3'

    import UserAvatarUpload from './UserAvatarUpload.vue'
    import UserDetailsSection from './UserDetailsSection.vue'
    import UserPasswordSection from './UserPasswordSection.vue'

    interface Role {
        id: number
        name: string
    }

    interface JobTitle {
        id: number
        name: string
    }

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
        submitRoute: string
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

    function submit() {
        if (props.method === 'put') {
            form
                .transform((data) => ({
                    ...data,
                    _method: 'put'
                }))
                .post(props.submitRoute)
        } else {
            form.post(props.submitRoute)
        }
    }
</script>

<template>
    <form
        @submit.prevent="submit"
        class="space-y-8 max-w-xl"
    >
    
        <UserAvatarUpload
            v-model="form.avatar"
            :avatar-url="user?.avatar_url"
        />

        <UserDetailsSection
            :form="form"
            :roles="roles"
            :job-titles="jobTitles"
        />

        <UserPasswordSection
            :form="form"
        />
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