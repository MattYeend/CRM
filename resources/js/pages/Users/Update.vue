<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import UserForm from './components/UserForm.vue'
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js'
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    user: any
    roles: any[]
    jobTitles: any[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
    { title: props.user.name, href: route('users.show', { user: props.user.id }) },
    { title: `Update ${props.user.name}`, href: route('users.edit', { user: props.user.id}) },
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
                submitLabel="Update User"
            />
        </div>
    </AppLayout>
</template>