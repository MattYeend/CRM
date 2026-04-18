<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchIndustry, deleteIndustries } from '@/services/industryService'

interface Industry {
    id: number
    name: string
    slug: string
    has_companies: boolean
    permissions: { view: boolean; update: boolean; delete: boolean }
}

const props = defineProps<{ industry: any }>()

const industry = ref<Industry>({
    id: props.industry.id,
    name: props.industry.name,
    slug: props.industry.slug,
    has_companies: props.industry.has_companies,
    permissions: { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Industries', href: route('industries.index') },
    { title: props.industry.name, href: route('industries.show', { industry: industry.value.id }) },
]

async function loadIndustry() {
    const data = await fetchIndustry(industry.value.id)
    Object.assign(industry.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure?')) return
    await deleteIndustries(industry.value.id)
    window.location.href = route('industries.index')
}

onMounted(() => loadIndustry())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="industry.name || 'Industry'" />
        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">
                <div class="flex items-start justify-between mb-6">
                    <h1 class="text-2xl font-bold">{{ industry.name }}</h1>
                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="industry.permissions.update && !industry.has_companies"
                            :href="route('industries.edit', { industry: industry.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >Edit</Link>
                        <Link
                            :href="route('industries.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >Back</Link>
                        <button
                            v-if="industry.permissions.delete && !industry.has_companies"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >Delete</button>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <span class="font-semibold">Slug: </span>
                        <span>{{ industry.slug }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>