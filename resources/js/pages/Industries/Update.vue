<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import IndustryForm from './components/IndustryForm.vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'

const props = defineProps<{ industry: any }>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Industries', href: route('industries.index') },
    { title: 'Edit Industry', href: route('industries.edit', { industry: props.industry.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Industry" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Industry</h1>
            <IndustryForm
                :industry="industry"
                :submit-route="`/api/industries/${industry.id}`"
                method="put"
            />
        </div>
    </AppLayout>
</template>