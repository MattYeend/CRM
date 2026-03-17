<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchUser, deleteUser } from '@/services/userService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface User {
    id: number
    name: string
    email: string
    avatar_url?: string
    job_title?: { title: string }
    role?: { name: string }
    permissions: UserPermissions
}

const props = defineProps<{ user: any }>()
const user = ref<User>({
    id: props.user.id,
    name: props.user.name,
    email: props.user.email,
    avatar_url: props.user.avatar_url,
    job_title: props.user.job_title,
    role: props.user.role,
    permissions: { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
    { title: 'View User', href: route('users.show', { user: user.value.id }) },
]

// Fetch the user via API to get correct permissions
async function loadUser() {
    const data = await fetchUser(user.value.id)
    Object.assign(user.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure?')) return
    await deleteUser(user.value.id)
    window.location.href = route('users.index')
}

onMounted(() => {
    loadUser()
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="user.name || 'User'" />
        <div class="p-6">
            <div class="max-w-xl mx-auto border p-6 rounded shadow">
                <div class="flex items-center space-x-4 mb-4">
                    <img v-if="user.avatar_url" :src="user.avatar_url" class="w-16 h-16 rounded-full" />
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
                        v-if="user.permissions?.update"
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
                    <button
                        v-if="user.permissions?.delete"
                        @click="handleDelete"
                        class="bg-red-600 text-white px-4 py-2 rounded"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>