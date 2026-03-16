<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3'
    import UserDeleteButton from './components/UserDeleteButton.vue'
    import { type BreadcrumbItem } from '@/types';

    import AppLayout from '@/layouts/AppLayout.vue';
    import { edit } from '@/routes/appearance';

    defineProps<{
        user: any
    }>()

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Show user',
            href: edit().url,
        },
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
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
                        <strong>Role:</strong> {{ user.role?.name }}
                    </p>
                    <p class="text-gray-500">
                        <strong>Job title:</strong> {{ user.job_title?.title }}
                    </p>
                </div>
            </div>
            <p class="mb-4">
                <strong>Email:</strong> {{ user.email }}
            </p>
            <div class="flex space-x-2 mt-4 p-4 border rounded bg-gray-50 dark:bg-gray-800">
                <Link
                    :href="`/users/${user.id}/edit`"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Edit User
                </Link>
                <UserDeleteButton :user-id="user.id" />
            </div>
        </div>
    </AppLayout>
</template>