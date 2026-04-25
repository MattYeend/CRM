<script setup lang="ts">
interface Permission {
    id: number
    name: string
}

interface User {
    id: number
    name: string
    email: string
}

interface Role {
    user_count: number
    permissions: Permission[]
    users?: User[] | null
}

const props = defineProps<{ role: Role }>()

function groupPermissionsByResource(permissions: Permission[]) {
    const grouped: Record<string, Permission[]> = {}
    
    permissions.forEach(permission => {
        const parts = permission.name.split('.')
        const resource = parts[0] || 'other'
        
        if (!grouped[resource]) {
            grouped[resource] = []
        }
        grouped[resource].push(permission)
    })
    
    return grouped
}

function toTitleCase(value: string): string {
    return value
        .replace(/([A-Z])/g, ' $1')
        .replace(/[_-]/g, ' ')
        .trim()
        .replace(/\w\S*/g, word =>
            word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
        )
}

const groupedPermissions = groupPermissionsByResource(props.role.permissions)
</script>

<template>
    <div>
        <!-- Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6">
            <div>
                <span class="font-semibold">Total Users: </span>
                <span>{{ role.user_count }}</span>
            </div>
            <div>
                <span class="font-semibold">Total Permissions: </span>
                <span>{{ role.permissions.length }}</span>
            </div>
        </div>

        <!-- Permissions -->
        <div v-if="role.permissions.length > 0">
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">Permissions</h2>
            <div class="space-y-4">
                <div
                    v-for="(permissions, resource) in groupedPermissions"
                    :key="resource"
                    class="border-l-4 border-indigo-500 pl-4"
                >
                    <h3 class="text-sm font-semibold mb-2">
                        {{ toTitleCase(resource) }}
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="permission in permissions"
                            :key="permission.id"
                            class="inline-block px-2 py-1 bg-indigo-50 text-indigo-700 text-xs rounded"
                        >
                            {{ permission.name }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users -->
        <div v-if="role.users && role.users.length > 0" class="mt-6">
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">
                Users with this Role ({{ role.users.length }})
            </h2>
            <table class="w-full border text-sm">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Email</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="user in role.users"
                        :key="user.id"
                        class="border-t"
                    >
                        <td class="p-2 font-medium">{{ user.name }}</td>
                        <td class="p-2">{{ user.email }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>