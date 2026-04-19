<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import UserForm from './components/UserForm.vue'
    import { type BreadcrumbItem } from '@/types';

    import AppLayout from '@/layouts/AppLayout.vue';
    import { edit } from '@/routes/appearance';

    defineProps<{
        roles: any[]
        jobTitles: any[]
    }>()

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Create user',
            href: edit().url,
        },
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create User" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">
                Create User
            </h1>
            <UserForm
                :roles="roles"
                :job-titles="jobTitles"
                submit-route="/users"
                method="post"
                submitLabel="Save User"
            />
        </div>
    </AppLayout>
</template>