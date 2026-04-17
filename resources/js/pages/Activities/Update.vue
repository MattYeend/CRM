<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import ActivityForm from './components/ActivityForm.vue'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'

defineProps<{
    subjectTypes: string[]
    activity: any
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Activities', href: route('activities.index') },
    { title: 'Edit Activity', href: '#' },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Activity" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Activity</h1>
            <ActivityForm
                :subject-types="subjectTypes"
                :activity="activity"
                method="put"
                submit-label="Update Activity"
            />
        </div>
    </AppLayout>
</template>