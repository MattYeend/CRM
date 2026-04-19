<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import ActivityForm from './components/ActivityForm.vue'
    import { type BreadcrumbItem } from '@/types';

    import AppLayout from '@/layouts/AppLayout.vue';
    import { edit } from '@/routes/appearance';

    defineProps<{
        subjectTypes: string[]
    }>()

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Create activity',
            href: edit().url,
        },
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Activity" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">
                Create Activity
            </h1>
            <ActivityForm
                :subject-types="subjectTypes"
                submit-route="/activities"
                method="post"
                submitLabel="Save Activity"
            />
        </div>
    </AppLayout>
</template>