<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'

defineProps<{ parts: any }>()
</script>

<template>
    <AppLayout>
        <Head title="Low Stock Parts" />

        <div class="p-6 space-y-6">
            <h1 class="text-2xl font-bold text-red-600">Low Stock</h1>

            <table class="w-full border text-sm">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">SKU</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-right">Reorder</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="part in parts.data" :key="part.id" class="border-t bg-red-50">
                        <td class="p-2">
                            <Link :href="route('parts.stock.show', { part: part.id })">
                                {{ part.name }}
                            </Link>
                        </td>
                        <td class="p-2">{{ part.sku }}</td>
                        <td class="p-2 text-right text-red-600 font-bold">
                            {{ part.quantity }}
                        </td>
                        <td class="p-2 text-right">{{ part.reorder_point }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>