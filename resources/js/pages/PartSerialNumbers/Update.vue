<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import SerialNumberForm from './components/SerialNumberForm.vue'
import { route } from 'ziggy-js'

const props = defineProps<{
    part: any
    serialNumber: any
}>()

const breadcrumbs = [
    { title: 'Parts', href: route('parts.index') },
    { title: props.part.name, href: route('parts.show', { part: props.part.id }) },
    { title: 'Serial Numbers', href: route('parts.serialNumbers.index', { part: props.part.id }) },
    { title: 'Edit', href: route('parts.serialNumbers.edit', { part: props.part.id, serialNumber: props.serialNumber.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit Serial Number" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Serial Number</h1>

            <SerialNumberForm
                :part="part"
                :serial-number="serialNumber"
                method="put"
                submit-label="Update Serial Number"
            />
        </div>
    </AppLayout>
</template>