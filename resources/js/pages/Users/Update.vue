<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import UserForm from './components/UserForm.vue'

    defineProps<{
        user: any
        roles: any[]
        jobTitles: any[]
    }>()
</script>

<template>
    <Head title="Edit User"/>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">
            Edit User
        </h1>
        <UserForm
            :user="user"
            :roles="roles"
            :job-titles="jobTitles"
            :submit-route="`/users/${user.id}`"
            method="put"
        />
    </div>
</template>