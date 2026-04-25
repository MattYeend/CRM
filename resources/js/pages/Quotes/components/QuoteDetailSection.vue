<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

interface Quote {
    deal?: { id: number; title: string } | null
    currency: string
    formatted_subtotal: string
    formatted_tax: string
    formatted_total: string
    sent_at?: string | null
    accepted_at?: string | null
    creator?: { name: string }
}

defineProps<{ quote: Quote }>()

function formatDate(date?: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-GB')
}
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">

        <div v-if="quote.deal">
            <span class="font-semibold">Deal</span>
            <div>
                <Link :href="route('deals.show', { deal: quote.deal.id })">
                    {{ quote.deal.title }}
                </Link>
            </div>
        </div>

        <div>
            <span class="font-semibold">Currency</span>
            <div>{{ quote.currency }}</div>
        </div>

        <div>
            <span class="font-semibold">Subtotal</span>
            <div>{{ quote.formatted_subtotal }}</div>
        </div>

        <div>
            <span class="font-semibold">Tax</span>
            <div>{{ quote.formatted_tax }}</div>
        </div>

        <div>
            <span class="font-semibold">Total</span>
            <div class="font-bold">{{ quote.formatted_total }}</div>
        </div>

        <div v-if="quote.sent_at">
            <span class="font-semibold">Sent At</span>
            <div>{{ formatDate(quote.sent_at) }}</div>
        </div>

        <div v-if="quote.accepted_at">
            <span class="font-semibold">Accepted At</span>
            <div>{{ formatDate(quote.accepted_at) }}</div>
        </div>

        <div v-if="quote.creator">
            <span class="font-semibold">Created By</span>
            <div>{{ quote.creator.name }}</div>
        </div>

    </div>
</template>