<script setup lang="ts">
interface SelectOption {
    id: number
    name: string
}

const props = defineProps<{
    roleIds: number[]
    roles: SelectOption[]
    errors: {
        role_ids?: string
    }
}>()

const emit = defineEmits<{
    'update:roleIds': [value: number[]]
}>()

function toggleRole(roleId: number) {
    const current = [...props.roleIds]
    const index = current.indexOf(roleId)
    
    if (index > -1) {
        current.splice(index, 1)
    } else {
        current.push(roleId)
    }
    
    emit('update:roleIds', current)
}

function isRoleSelected(roleId: number): boolean {
    return props.roleIds.includes(roleId)
}
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Role Assignments</h2>

        <div>
            <label class="block text-sm font-medium mb-2">
                Assign to Roles
            </label>
            
            <div class="space-y-2">
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="flex items-center"
                >
                    <input
                        :id="`role-${role.id}`"
                        type="checkbox"
                        :checked="isRoleSelected(role.id)"
                        @change="toggleRole(role.id)"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label
                        :for="`role-${role.id}`"
                        class="ml-2 text-sm text-gray-700 cursor-pointer"
                    >
                        {{ role.name }}
                    </label>
                </div>
            </div>

            <p v-if="errors.role_ids" class="text-red-500 text-sm mt-1">
                {{ errors.role_ids }}
            </p>
            
            <p class="text-gray-500 text-sm mt-2">
                Select which roles should have this permission
            </p>
        </div>
    </div>
</template>