<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import PartImageForm from './components/PartImageForm.vue'
import { route } from 'ziggy-js'

interface Part {
    id: number
    sku: string
    name: string
}

defineProps<{
    parts: Part[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Part Images', href: route('part-images.index') },
    { title: 'Create Image', href: route('part-images.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Part Image" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Part Image</h1>
            <PartImageForm
                :parts="parts"
                method="post"
                submit-label="Save Image"
            />
        </div>
    </AppLayout>
</template>