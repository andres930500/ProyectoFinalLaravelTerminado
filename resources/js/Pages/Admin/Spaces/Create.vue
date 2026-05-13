<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SpaceForm from './Form.vue';

defineProps({
    types: Array,
});
</script>

<template>
    <AppLayout title="Nueva Cancha">
        <template #header>
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-300">Espacios</div>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-white">Nueva cancha</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <SpaceForm :types="types" />
            </div>
        </div>
    </AppLayout>
</template>
