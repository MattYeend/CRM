<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import UserForm from './components/UserForm.vue'
    import { type BreadcrumbItem } from '@/types';

    import AppLayout from '@/layouts/AppLayout.vue';
    import { edit } from '@/routes/appearance';

    defineProps<{
        user: any
        roles: any[]
        jobTitles: any[]
    }>()

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Update user',
            href: edit().url,
        },
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit User"/>
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">
                Edit User
            </h1>
            <UserForm
                :user="user"
                :roles="roles"
                :jobTitles="jobTitles"
                :submit-route="`/users/${user.id}`"
                method="put"
            />
        </div>
    </AppLayout>
</template>