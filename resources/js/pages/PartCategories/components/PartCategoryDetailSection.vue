<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

interface PartCategory {
    slug?: string
    parent?: { id: number; name: string }
    children?: { id: number; name: string }[]
    parts?: { id: number; name: string; sku: string }[]
    creator?: { name: string }
}

defineProps<{ partCategory: PartCategory }>()
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm">

        <!-- Details -->
        <div class="space-y-3">
            <h3 class="ont-semibold uppercase tracking-wider">
                Details
            </h3>
            <dl class="space-y-2">
                <div v-if="partCategory.slug" class="flex justify-between">
                    <dt>Slug</dt>
                    <dd>{{ partCategory.slug }}</dd>
                </div>

                <div v-if="partCategory.parent" class="flex justify-between">
                    <dt>Parent</dt>
                    <dd>
                        <Link
                            :href="route('part-categories.show', { partCategory: partCategory.parent.id })"
                        >
                            {{ partCategory.parent.name }}
                        </Link>
                    </dd>
                </div>

                <div v-if="partCategory.creator" class="flex justify-between">
                    <dt>Created By</dt>
                    <dd>{{ partCategory.creator.name }}</dd>
                </div>
            </dl>
        </div>

        <!-- Sub-categories -->
        <div class="space-y-3">
            <h3 class="font-semibold uppercase tracking-wider">
                Sub-categories ({{ partCategory.children?.length ?? 0 }})
            </h3>

            <ul v-if="partCategory.children?.length" class="space-y-1">
                <li v-for="child in partCategory.children" :key="child.id">
                    <Link
                        :href="route('part-categories.show', { partCategory: child.id })"
                    >
                        {{ child.name }}
                    </Link>
                </li>
            </ul>

            <p v-else class="text-gray-400">None</p>
        </div>

        <!-- Parts -->
        <div class="space-y-3 md:col-span-2">
            <h3 class="text-sm font-semibold uppercase tracking-wider">
                Parts ({{ partCategory.parts?.length ?? 0 }})
            </h3>

            <table v-if="partCategory.parts?.length" class="w-full border text-sm">
                <thead>
                    <tr>
                        <th class="p-2 text-left">SKU</th>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="part in partCategory.parts"
                        :key="part.id"
                        class="border-t"
                    >
                        <td class="p-2">
                            {{ part.sku }}
                        </td>
                        <td class="p-2">{{ part.name }}</td>
                        <td class="p-2">
                            <Link
                                :href="route('parts.show', { part: part.id })"
                            >
                                View
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p v-else class="text-gray-400 text-sm">
                No parts in this category.
            </p>
        </div>

    </div>
</template>