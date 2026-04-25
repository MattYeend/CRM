<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'

interface JobTitle {
    id: number
    title: string
    short_code: string | null
    group: string | null
    is_csuite: boolean
    is_executive: boolean
    is_director: boolean
    user_count: number
    users?: Array<{ id: number; name: string }>
    creator?: { name: string } | null
    updater?: { name: string } | null
}

const props = defineProps<{ jobTitle: JobTitle }>()

const hasUsers = computed(() => props.jobTitle.user_count > 0)
</script>

<template>
    <div>
        <!-- Locked notice -->
        <div v-if="hasUsers" class="mb-6 px-4 py-3 rounded border border-amber-200 bg-amber-50 text-amber-800 text-sm">
            This job title has {{ jobTitle.user_count }} assigned {{ jobTitle.user_count === 1 ? 'user' : 'users' }} and cannot be edited or deleted.
        </div>

        <!-- Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6">
            <div>
                <span class="font-semibold">Users with this title: </span>
                <span>{{ jobTitle.user_count }}</span>
            </div>

            <div v-if="jobTitle.creator">
                <span class="font-semibold">Created By: </span>
                <span>{{ jobTitle.creator.name }}</span>
            </div>

            <div v-if="jobTitle.updater">
                <span class="font-semibold">Last Updated By: </span>
                <span>{{ jobTitle.updater.name }}</span>
            </div>
        </div>

        <!-- Users -->
        <div v-if="jobTitle.users && jobTitle.users.length > 0">
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">Users</h2>
            <table class="w-full border text-sm">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in jobTitle.users" :key="user.id" class="border-t">
                        <td class="p-2">{{ user.name }}</td>
                        <td class="p-2 text-right">
                            <Link
                                :href="route('users.show', { user: user.id })"
                                class="text-xs"
                            >
                                View
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>