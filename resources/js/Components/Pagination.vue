<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center gap-2">
        <component
            :is="link.url ? Link : 'span'"
            v-for="(link, index) in links"
            :key="index"
            :href="link.url || undefined"
            class="inline-flex min-w-10 items-center justify-center rounded-full border px-3 py-2 text-sm transition"
            :class="[
                link.active
                    ? 'border-emerald-600 bg-emerald-600 text-white'
                    : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-200 hover:text-emerald-700',
                !link.url && !link.active ? 'cursor-not-allowed opacity-50' : '',
            ]"
        >
            <span v-html="link.label"></span>
        </component>
    </div>
</template>
