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

const props = defineProps<{
    partImage: any
    parts: Part[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Part Images', href: route('part-images.index') },
    { title: props.partImage.part?.name ?? `Image #${props.partImage.id}`, href: route('part-images.show', { partImage: props.partImage.id }) },
    { title: 'Edit', href: route('part-images.edit', { partImage: props.partImage.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Part Image" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Part Image</h1>
            <PartImageForm
                :partImage="partImage"
                :parts="parts"
                method="put"
                submit-label="Update Image"
            />
        </div>
    </AppLayout>
</template>