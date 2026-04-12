<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

interface SelectOption {
    id: number
    name: string
}

interface Deal {
    id?: number
    title?: string
    company_id?: number | null
    owner_id?: number | null
    pipeline_id?: number | null
    stage_id?: number | null
    value?: number
    currency?: string
    close_date?: string | null
    status?: 'open' | 'won' | 'lost' | 'archived'
    is_test?: boolean
    meta?: Record<string, any> | null
}

const props = defineProps<{
    deal?: Deal
    companies: SelectOption[]
    owners: SelectOption[]
    pipelines: SelectOption[]
    stages: SelectOption[]
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const statusOptions = [
    { value: 'open', label: 'Open' },
    { value: 'won', label: 'Won' },
    { value: 'lost', label: 'Lost' },
    { value: 'archived', label: 'Archived' },
]

const currencyOptions = ['USD', 'GBP', 'EUR', 'CAD', 'AUD']

const form = useForm({
    title: props.deal?.title ?? '',
    company_id: props.deal?.company_id ?? null,
    owner_id: props.deal?.owner_id ?? null,
    pipeline_id: props.deal?.pipeline_id ?? null,
    stage_id: props.deal?.stage_id ?? null,
    value: props.deal?.value ?? 0,
    currency: props.deal?.currency ?? 'USD',
    close_date: props.deal?.close_date ?? '',
    status: props.deal?.status ?? 'open',
    is_test: props.deal?.is_test ?? false,
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

        router.visit(`/deals/${response.data.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err)
        if (err.response?.status === 422) {
            form.setError(err.response.data.errors)
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-2xl">

        <!-- Core Details -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Deal Details</h2>

            <div>
                <label class="block text-sm font-medium mb-1">Title <span class="text-red-500">*</span></label>
                <input
                    v-model="form.title"
                    type="text"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Deal title"
                />
                <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Value</label>
                    <input
                        v-model="form.value"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="form.errors.value" class="text-red-500 text-sm mt-1">{{ form.errors.value }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Currency</label>
                    <select
                        v-model="form.currency"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="c in currencyOptions" :key="c" :value="c">{{ c }}</option>
                    </select>
                    <p v-if="form.errors.currency" class="text-red-500 text-sm mt-1">{{ form.errors.currency }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select
                        v-model="form.status"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                    <p v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Close Date</label>
                    <input
                        v-model="form.close_date"
                        type="date"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="form.errors.close_date" class="text-red-500 text-sm mt-1">{{ form.errors.close_date }}</p>
                </div>
            </div>
        </div>

        <!-- Associations -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Associations</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Company</label>
                    <select
                        v-model="form.company_id"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">— None —</option>
                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <p v-if="form.errors.company_id" class="text-red-500 text-sm mt-1">{{ form.errors.company_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Owner</label>
                    <select
                        v-model="form.owner_id"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">— None —</option>
                        <option v-for="o in owners" :key="o.id" :value="o.id">{{ o.name }}</option>
                    </select>
                    <p v-if="form.errors.owner_id" class="text-red-500 text-sm mt-1">{{ form.errors.owner_id }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Pipeline</label>
                    <select
                        v-model="form.pipeline_id"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">— None —</option>
                        <option v-for="p in pipelines" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <p v-if="form.errors.pipeline_id" class="text-red-500 text-sm mt-1">{{ form.errors.pipeline_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Stage</label>
                    <select
                        v-model="form.stage_id"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">— None —</option>
                        <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <p v-if="form.errors.stage_id" class="text-red-500 text-sm mt-1">{{ form.errors.stage_id }}</p>
                </div>
            </div>
        </div>

        <!-- Flags -->
        <div class="space-y-2">
            <h2 class="text-lg font-semibold border-b pb-2">Flags</h2>
            <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.is_test" type="checkbox" class="w-4 h-4" />
                <span class="text-sm">Test record</span>
            </label>
        </div>

        <div>
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Deal') }}
            </button>
        </div>
    </form>
</template>