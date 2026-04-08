<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import IndustryForm from './components/IndustryForm.vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Industries', href: route('industries.index') },
    { title: 'Create Industry', href: route('industries.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Industry" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Industry</h1>
            <IndustryForm submit-route="/api/industries" method="post" />
        </div>
    </AppLayout>
</template>