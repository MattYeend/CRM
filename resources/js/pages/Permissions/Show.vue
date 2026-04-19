<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { route } from 'ziggy-js'
import { fetchPermission, deletePermissions } from '@/services/permissionService'

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
    id: number
}>()

const permission = ref<Permission | null>(null)
const loading = ref(true)
const deleting = ref(false)

async function loadPermission() {
    loading.value = true
    try {
        permission.value = await fetchPermission(props.id)
    } finally {
        loading.value = false
    }
}

async function handleDelete() {
    if (!permission.value) return
    if (!confirm('Are you sure?')) return

    deleting.value = true
    try {
        await deletePermissions(permission.value.id)
        window.location.href = route('permissions.index')
    } finally {
        deleting.value = false
    }
}

onMounted(loadPermission)
</script>

<template>
    <AppLayout title="Permission">

        <Head :title="permission ? permission.name : 'Permission'" />

        <div class="p-6">

            <div v-if="loading">Loading...</div>

            <div v-else-if="permission" class="border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex justify-between mb-6">
                    <h1 class="text-2xl font-bold">
                        {{ permission.name }}
                    </h1>

                    <div class="space-x-2">
                        <Link
                            :href="route('permissions.index')"
                            class="bg-gray-200 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <Link
                            v-if="permission.permissions.update"
                            :href="route('permissions.edit', permission.id)"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <button
                            v-if="permission.permissions.delete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                            :disabled="deleting"
                            @click="handleDelete"
                        >
                            {{ deleting ? 'Deleting…' : 'Delete' }}
                        </button>
                    </div>
                </div>

                <!-- Details -->
                <div class="space-y-2">
                    <div><strong>Label:</strong> {{ permission.label }}</div>
                    <div><strong>Roles:</strong> {{ permission.role_count }}</div>
                    <div><strong>Status:</strong> {{ permission.is_assigned ? 'Assigned' : 'Unassigned' }}</div>
                    <div v-if="permission.creator">
                        <strong>Created By:</strong> {{ permission.creator.name }}
                    </div>
                </div>

                <!-- Roles -->
                <div class="mt-6">
                    <h2 class="font-semibold mb-2">
                        Roles ({{ permission.roles.length }})
                    </h2>

                    <div v-if="permission.roles.length">
                        <div
                            v-for="role in permission.roles"
                            :key="role.id"
                            class="flex justify-between p-2 bg-gray-50 rounded"
                        >
                            <span>{{ role.name }}</span>
                            <Link
                                :href="route('roles.show', role.id)"
                                class="text-blue-600"
                            >
                                View
                            </Link>
                        </div>
                    </div>

                    <div v-else class="text-gray-500">
                        No roles assigned.
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>