<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { type BreadcrumbItem } from '@/types'
import { deletePermissions } from '@/services/permissionService'
import PermissionDetailSection from './components/PermissionDetailSection.vue'

interface Role {
    id: number
    name: string
}

interface User {
    id: number
    name: string
}

interface Permission {
    id: number
    name: string
    label: string
    is_assigned: boolean
    role_count: number
    roles: Role[]
    creator: User | null
    permissions: {
        view: boolean
        update: boolean
        delete: boolean
    }
}

const props = defineProps<{
    permission: Permission
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Permissions', href: route('permissions.index') },
    { title: `Permission ${props.permission.name}`, href: route('permissions.show', { permission: props.permission.id }) },
]

const deleting = ref(false)

async function handleDelete() {
    if (!confirm('Are you sure?')) return

    deleting.value = true
    try {
        await deletePermissions(props.permission.id)
        window.location.href = route('permissions.index')
    } catch {
        deleting.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="permission.name" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <h1 class="text-2xl font-bold">
                            {{ permission.name }}
                        </h1>

                        <p v-if="permission.creator" class="text-gray-600 text-sm">
                            {{ permission.creator.name }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            v-if="permission.permissions.update"
                            :href="route('permissions.edit', { permission: permission.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('permissions.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="permission.permissions.delete && permission.role_count === 0"
                            :disabled="deleting"
                            class="bg-red-600 text-white px-4 py-2 rounded disabled:opacity-50"
                            @click="handleDelete"
                        >
                            {{ deleting ? 'Deleting…' : 'Delete' }}
                        </button>
                    </div>
                </div>

                <PermissionDetailSection :permission="permission" />
            </div>
        </div>
    </AppLayout>
</template>