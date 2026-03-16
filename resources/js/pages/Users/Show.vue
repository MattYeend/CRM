<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3'
    import UserDeleteButton from './components/UserDeleteButton.vue'

    defineProps<{
        user: any
    }>()
</script>

<template>
    <Head :title="user.name"/>
    <div class="p-6 max-w-lg">
        <div class="flex items-center space-x-4 mb-6">
            <img
                v-if="user.avatar_url"
                :src="user.avatar_url"
                class="w-16 h-16 rounded-full"
            />
            <div>
                <h1 class="text-xl font-bold">
                    {{ user.name }}
                </h1>
                <p class="text-gray-500">
                    {{ user.job_title }}
                </p>
            </div>
        </div>
        <p class="mb-4">
           <strong>Email:</strong> {{ user.email }}
        </p>
        <Link
            :href="`/users/${user.id}/edit`"
            class="bg-blue-600 text-white px-4 py-2 rounded"
        >
            Edit User
        </Link>
        <UserDeleteButton
            :user-id="user.id"
        />
    </div>
</template>