<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchUsers } from '@/services/userService'

// User interface
interface User {
    id: number
    name: string
    email: string
    avatar_url?: string
    job_title?: { title: string }
    role?: { name: string }
}

// Pagination interface
interface PaginationMeta {
    current_page: number
    last_page: number
    total: number
}

const users = ref<User[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: route('users.index'),
    },
]

// Fetch users from API
async function loadUsers(page = 1) {
    loading.value = true
    try {
        const data = await fetchUsers(perPage, page)
        users.value = data.data
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

// Pagination navigation
function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadUsers(page)
    }
}

onMounted(() => loadUsers())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Users" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Users</h1>
                <Link href="/users/create" class="bg-blue-600 text-white px-4 py-2 rounded">
                Create
                </Link>
            </div>

            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <table v-else class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2">Avatar</th>
                        <th class="p-2">Name</th>
                        <th class="p-2">Email</th>
                        <th class="p-2">Job</th>
                        <th class="p-2">Role</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id" class="border-t">
                        <td class="p-2">
                            <img v-if="user.avatar_url" :src="user.avatar_url" class="w-8 h-8 rounded-full" />
                        </td>
                        <td class="p-2">{{ user.name }}</td>
                        <td class="p-2">{{ user.email }}</td>
                        <td class="p-2">{{ user.job_title?.title || '—' }}</td>
                        <td class="p-2">{{ user.role?.name || '—' }}</td>
                        <td class="p-2 space-x-2">
                            <Link :href="route('users.show', { user: user.id })">View</Link>
                            <Link :href="route('users.edit', { user: user.id })">Edit</Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    Previous
                </button>

                <button
                    v-for="page in pagination.last_page"
                    :key="page"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-blue-600 text-white': page === currentPage }"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>

                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === pagination.last_page"
                    @click="goToPage(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>