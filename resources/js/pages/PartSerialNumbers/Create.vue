<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import SerialNumberForm from './components/SerialNumberForm.vue'
import { route } from 'ziggy-js'

interface Part {
    id: number
    name: string
    sku: string
}

const props = defineProps<{ part: Part }>()

const breadcrumbs = [
    { title: 'Parts', href: route('parts.index') },
    { title: props.part.name, href: route('parts.show', { part: props.part.id }) },
    { title: 'Serial Numbers', href: route('parts.serialNumbers.index', { part: props.part.id }) },
    { title: 'Create', href: route('parts.serialNumbers.create', { part: props.part.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Serial Number" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Add Serial Number</h1>

            <SerialNumberForm
                :part="part"
                method="post"
                submit-label="Create Serial Number"
            />
        </div>
    </AppLayout>
</template>