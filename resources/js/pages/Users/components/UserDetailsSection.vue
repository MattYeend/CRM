<script setup lang="ts">
import UserRolesSelect from './UserRolesSelect.vue'
import UserJobTitlesSelect from './UserJobTitlesSelect.vue'

interface Role {
    id: number
    name: string
}

interface JobTitle {
    id: number
    title: string
}

const props = defineProps<{
    form: any
    roles: Role[]
    jobTitles: JobTitle[]
    errors?: Partial<Record<'name' | 'email' | 'role_id' | 'job_title_id', string>>

}>()

const form = props.form
</script>

<template>
    <div class="space-y-5">
        <div>
            <label class="block font-medium">Name</label>
            <input
                v-model="form.name"
                class="border rounded w-full p-2"
                :class="{ 'border-red-500': errors?.name }"
            />
            <p v-if="errors?.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
        </div>

        <div>
            <label class="block font-medium">Email</label>
            <input
                v-model="form.email"
                class="border rounded w-full p-2"
                :class="{ 'border-red-500': errors?.email }"
            />
            <p v-if="errors?.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
        </div>

        <div>
            <label class="block font-medium">Role</label>
            <UserRolesSelect 
                v-model="form.role_id"
                :roles="roles"
                :class="{ 'border-red-500': errors?.role_id }"
            />
            <p v-if="errors?.role_id" class="mt-1 text-sm text-red-600">{{ errors.role_id }}</p>
        </div>

        <div>
            <label class="block font-medium">Job Title</label>
            <UserJobTitlesSelect
                v-model="form.job_title_id"
                :jobTitles="jobTitles"
                :class="{ 'border-red-500': errors?.job_title_id }"
            />
            <p v-if="errors?.job_title_id" class="mt-1 text-sm text-red-600">{{ errors.job_title_id }}</p>
        </div>
    </div>
</template>