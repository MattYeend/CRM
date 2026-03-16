<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import UserForm from './components/UserForm.vue'

    defineProps<{
        roles: any[]
        jobTitles: any[]
    }>()
</script>

<template>
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
        />
    </div>
</template>