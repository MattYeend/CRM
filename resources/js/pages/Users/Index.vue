<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'

interface User {
  id: number
  name: string
  email: string
  avatar_url?: string
  job_title?: string
}

const props = defineProps<{ users: User[] }>()
const users = ref<User[]>(props.users)
</script>

<template>
  <AppLayout>
    <Head title="Users"/>
    <div class="p-6">
      <div class="flex justify-between mb-6">
        <h1 class="text-2xl font-bold">Users</h1>
        <Link href="/users/create" class="bg-blue-600 text-white px-4 py-2 rounded">
          Create
        </Link>
      </div>
      <table class="w-full border">
        <thead>
          <tr>
            <th class="p-2">Avatar</th>
            <th class="p-2">Name</th>
            <th class="p-2">Email</th>
            <th class="p-2">Job</th>
            <th class="p-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="border-t">
            <td class="p-2">
              <img v-if="user.avatar_url" :src="user.avatar_url" class="w-8 h-8 rounded-full"/>
            </td>
            <td class="p-2">{{ user.name }}</td>
            <td class="p-2">{{ user.email }}</td>
            <td class="p-2">{{ user.job_title }}</td>
            <td class="p-2 space-x-2">
              <Link :href="`/users/${user.id}`">View</Link>
              <Link :href="`/users/${user.id}/edit`">Edit</Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>