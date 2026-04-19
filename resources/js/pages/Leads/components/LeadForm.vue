<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

interface User {
    id: number
    name: string
}

interface Lead {
    id?: number
    title?: string
    first_name?: string
    last_name?: string
    email?: string
    phone?: string
    source?: string | null
    owner_id?: number | null
    assigned_to?: { id: number } | number | null
    meta?: Record<string, any> | null
}

const props = defineProps<{
    lead?: Lead
    users: User[]
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const sourceOptions = [
    { value: 'Website', label: 'Website' },
    { value: 'Referral', label: 'Referral' },
    { value: 'Cold Call', label: 'Cold Call' },
    { value: 'Email', label: 'Email' },
    { value: 'Social Media', label: 'Social Media' },
    { value: 'Trade Show', label: 'Trade Show' },
    { value: 'Other', label: 'Other' },
]

const lead = props.lead

const form = useForm({
    title: lead?.title ?? '',
    first_name: lead?.first_name ?? '',
    last_name: lead?.last_name ?? '',
    email: lead?.email ?? '',
    phone: lead?.phone ?? '',
    source: lead?.source ?? null,
    owner_id: lead?.owner_id ?? null,
    assigned_to: lead?.assigned_to
        ? typeof lead.assigned_to === 'object'
            ? lead.assigned_to.id
            : lead.assigned_to
        : null,
})

async function submit() {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const payload = { ...form.data() }

        const response = await axios({
            method: props.method === 'put' ? 'put' : 'post',
            url: props.submitRoute,
            data: payload,
            withCredentials: true,
            headers: { 'Content-Type': 'application/json' },
        })

        router.visit(`/leads/${response.data.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err)

        if (err.response?.status === 422) {
            const raw = err.response.data.errors as Record<string, string[]>
            const flat = Object.fromEntries(
                Object.entries(raw).map(([key, messages]) => [key, messages[0]])
            ) as Record<string, string>
            form.setError(flat)
        }
    }
}console.log(props.lead?.assigned_to)
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-2xl">

        <!-- Personal Details -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Personal Details</h2>

            <div>
                <label class="block text-sm font-medium mb-1">Title</label>
                <input
                    v-model="form.title"
                    type="text"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g. Senior Engineer at Acme Corp"
                />
                <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">First Name</label>
                    <input
                        v-model="form.first_name"
                        type="text"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Jane"
                    />
                    <p v-if="form.errors.first_name" class="text-red-500 text-sm mt-1">{{ form.errors.first_name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Last Name</label>
                    <input
                        v-model="form.last_name"
                        type="text"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Smith"
                    />
                    <p v-if="form.errors.last_name" class="text-red-500 text-sm mt-1">{{ form.errors.last_name }}</p>
                </div>
            </div>
        </div>

        <!-- Contact Details -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Contact Details</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="jane@example.com"
                    />
                    <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input
                        v-model="form.phone"
                        type="text"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="+44 7700 900000"
                    />
                    <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Source</label>
                <select
                    v-model="form.source"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">- None -</option>
                    <option
                        v-for="option in sourceOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <p v-if="form.errors.source" class="text-red-500 text-sm mt-1">{{ form.errors.source }}</p>
            </div>
        </div>

        <!-- Assignment -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Assignment</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Owner</label>
                    <select
                        v-model="form.owner_id"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">- None -</option>
                        <option
                            v-for="user in users"
                            :key="user.id"
                            :value="user.id"
                        >
                            {{ user.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.owner_id" class="text-red-500 text-sm mt-1">{{ form.errors.owner_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Assigned To</label>
                    <select
                        v-model="form.assigned_to"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">- None -</option>
                        <option
                            v-for="user in users"
                            :key="user.id"
                            :value="user.id"
                        >
                            {{ user.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.assigned_to" class="text-red-500 text-sm mt-1">{{ form.errors.assigned_to }}</p>
                </div>
            </div>
        </div>

        <div>
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Lead') }}
            </button>
        </div>
    </form>
</template>