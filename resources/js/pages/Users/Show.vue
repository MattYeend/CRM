<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'

const props = defineProps<{ user: any }>()

interface User {
    id: number
    name: string
    email: string
    avatar_url?: string
    job_title?: { title: string }
    role?: { name: string }
}

const user = ref<User>(props.user)

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
    { title: 'View User', href: route('users.show', { user: user.value.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="user.name || 'User'" />
        <div class="p-6">
            <div class="max-w-xl mx-auto border p-6 rounded shadow">
                <div class="flex items-center space-x-4 mb-4">
                    <img
                        v-if="user.avatar_url"
                        :src="user.avatar_url"
                        class="w-16 h-16 rounded-full"
                    />
                    <div>
                        <h1 class="text-2xl font-bold">{{ user.name }}</h1>
                        <p class="text-gray-600">{{ user.email }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <div>
                        <span class="font-semibold">Job Title: </span>
                        <span>{{ user.job_title?.title || '—' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold">Role: </span>
                        <span>{{ user.role?.name || '—' }}</span>
                    </div>
                </div>

                <div class="mt-6 space-x-2">
                    <Link
                        :href="route('users.edit', { user: user.id })"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        Edit
                    </Link>
                    <Link
                        :href="route('users.index')"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                    >
                        Back to Users
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>