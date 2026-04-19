<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import PermissionForm from './components/PermissionForm.vue'
import { route } from 'ziggy-js'

interface SelectOption {
    id: number
    name: string
}

defineProps<{
    roles: SelectOption[]
}>()
</script>

<template>
    <AppLayout title="Create Permission">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create New Permission
                </h2>
                <Link
                    :href="route('permissions.index')"
                    class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700"
                >
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <PermissionForm
                        :roles="roles"
                        method="post"
                        submit-route="/api/permissions"
                        submit-label="Create Permission"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>