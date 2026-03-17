<script setup lang="ts">
import { ref, watch } from 'vue'
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
}>()

const emit = defineEmits(['update:form'])

const localForm = ref({ ...props.form })

watch(
    localForm,
    (newVal) => {
        emit('update:form', newVal)
    },
    { deep: true }
)
</script>

<template>
    <div class="space-y-5">
        <div>
            <label class="block font-medium">Name</label>
            <input v-model="localForm.name" class="border rounded w-full p-2"/>
        </div>

        <div>
            <label class="block font-medium">Email</label>
            <input v-model="localForm.email" class="border rounded w-full p-2"/>
        </div>

        <div>
            <label class="block font-medium">Role</label>
            <UserRolesSelect 
                v-model="localForm.role_id"
                :roles="roles"
            />
        </div>

        <div>
            <label class="block font-medium">Job Title</label>
            <UserJobTitlesSelect
                v-model="localForm.job_title_id"
                :jobTitles="jobTitles"
            />
        </div>
    </div>
</template>