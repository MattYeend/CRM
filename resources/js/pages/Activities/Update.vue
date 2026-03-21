<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import ActivityForm from './components/ActivityForm.vue'
    import { type BreadcrumbItem } from '@/types';

    import AppLayout from '@/layouts/AppLayout.vue';
    import { edit } from '@/routes/appearance';

    defineProps<{
        subjectTypes: string[]
        activity: any
        user: any
    }>()

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Update Activity',
            href: edit().url,
        },
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Activity"/>
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">
                Edit Activity
            </h1>
            <ActivityForm
                :subject-types="subjectTypes"
                :activity="activity"
                :user="user"
                :submit-route="`/activity/${activity.id}`"
                method="put"
            />
        </div>
    </AppLayout>
</template>