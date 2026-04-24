<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'

defineProps<{
    product: any
    parts: any[]
}>()
</script>

<template>
    <AppLayout>
        <Head :title="`Stock — ${product.name}`" />

        <div class="p-6 space-y-6">

            <div class="flex justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ product.name }}</h1>
                    <p class="text-sm text-gray-500">Stock breakdown</p>
                </div>

                <Link
                    :href="route('products.stock.index')"
                    class="bg-gray-200 px-4 py-2 rounded"
                >
                    Back
                </Link>
            </div>

            <table class="w-full border">
                <thead>
                    <tr>
                        <th>Part</th>
                        <th class="text-right">Available</th>
                        <th class="text-right">Required</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="p in parts" :key="p.id">
                        <td>{{ p.name }}</td>
                        <td class="text-right">{{ p.quantity }}</td>
                        <td class="text-right">{{ p.required_quantity ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </AppLayout>
</template>