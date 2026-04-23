<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'

interface Part {
    id: number
    name: string
    sku: string
}

const props = defineProps<{ part: Part }>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Parts', href: route('parts.index') },
    { title: props.part.name, href: route('parts.show', { part: props.part.id }) },
    { title: 'Serial Numbers', href: route('parts.serialNumbers.index', { part: props.part.id }) },
    { title: 'Create', href: route('parts.serialNumbers.create', { part: props.part.id }) },
]

const form = useForm({
    part_id: props.part.id,
    serial_number: '',
    status: '',
    batch_number: '',
    manufactured_at: '',
    expires_at: '',
})

function submit() {
    form.post(route('parts.serialNumbers.store', { part: props.part.id }))
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Add Serial Number — ${part.name}`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Add Serial Number</h1>
                    <p class="text-sm text-gray-500 mt-1 font-mono">{{ part.name }} · {{ part.sku }}</p>
                </div>
                <Link
                    :href="route('parts.serialNumbers.index', { part: part.id })"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                >
                    Back
                </Link>
            </div>

            <div class="border rounded p-6 shadow max-w-lg">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Serial Number <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.serial_number"
                            type="text"
                            class="w-full border rounded px-3 py-2 text-sm font-mono"
                            placeholder="SN-000000"
                        />
                        <p v-if="form.errors.serial_number" class="text-red-600 text-xs mt-1">
                            {{ form.errors.serial_number }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <input
                            v-model="form.status"
                            type="text"
                            class="w-full border rounded px-3 py-2 text-sm"
                            placeholder="e.g. active"
                        />
                        <p v-if="form.errors.status" class="text-red-600 text-xs mt-1">
                            {{ form.errors.status }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                        <input
                            v-model="form.batch_number"
                            type="text"
                            class="w-full border rounded px-3 py-2 text-sm font-mono"
                        />
                        <p v-if="form.errors.batch_number" class="text-red-600 text-xs mt-1">
                            {{ form.errors.batch_number }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Manufactured At</label>
                        <input
                            v-model="form.manufactured_at"
                            type="date"
                            class="w-full border rounded px-3 py-2 text-sm"
                        />
                        <p v-if="form.errors.manufactured_at" class="text-red-600 text-xs mt-1">
                            {{ form.errors.manufactured_at }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expires At</label>
                        <input
                            v-model="form.expires_at"
                            type="date"
                            class="w-full border rounded px-3 py-2 text-sm"
                        />
                        <p v-if="form.errors.expires_at" class="text-red-600 text-xs mt-1">
                            {{ form.errors.expires_at }}
                        </p>
                    </div>

                    <div class="pt-2">
                        <button
                            :disabled="form.processing"
                            class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50"
                            @click="submit"
                        >
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>