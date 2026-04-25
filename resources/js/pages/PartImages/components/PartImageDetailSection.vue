<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

interface PartImage {
    part?: { id: number; name: string; sku: string }
    image_url?: string
    thumbnail_url?: string
    alt?: string
    is_primary: boolean
    sort_order?: number
    creator?: { name: string }
}

defineProps<{ partImage: PartImage }>()
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <!-- Image preview -->
        <div class="space-y-3">
            <h3 class="text-sm font-semibold uppercase tracking-wider">
                Preview
            </h3>

            <img
                v-if="partImage.image_url"
                :src="partImage.image_url"
                :alt="partImage.alt ?? partImage.part?.name"
                class="w-full max-w-sm rounded border object-contain"
            />

            <p v-else class="text-sm text-gray-400">
                No image available.
            </p>

            <div v-if="partImage.thumbnail_url" class="mt-2">
                <p class="text-xs mb-1">Thumbnail</p>
                <img
                    :src="partImage.thumbnail_url"
                    :alt="partImage.alt ?? partImage.part?.name"
                    class="h-16 w-16 rounded border object-cover"
                />
            </div>
        </div>

        <!-- Details -->
        <div class="space-y-3">
            <h3 class="text-sm font-semibold uppercase tracking-wider">
                Details
            </h3>

            <dl class="space-y-2 text-sm">
                <div v-if="partImage.part" class="flex justify-between">
                    <dt>Part</dt>
                    <dd>
                        <Link
                            :href="route('parts.show', { part: partImage.part.id })"
                        >
                            {{ partImage.part.name }}
                        </Link>
                    </dd>
                </div>

                <div v-if="partImage.alt" class="flex justify-between">
                    <dt>Alt Text</dt>
                    <dd>{{ partImage.alt }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt>Primary</dt>
                    <dd>
                        <span
                            class="text-xs px-2 py-0.5 rounded-full"
                            :class="partImage.is_primary
                                ? 'bg-green-100 text-green-700'
                                : 'bg-gray-100 text-gray-600'"
                        >
                            {{ partImage.is_primary ? 'Yes' : 'No' }}
                        </span>
                    </dd>
                </div>

                <div v-if="partImage.sort_order != null" class="flex justify-between">
                    <dt>Sort Order</dt>
                    <dd class="tabular-nums">{{ partImage.sort_order }}</dd>
                </div>
            </dl>
        </div>

    </div>
</template>