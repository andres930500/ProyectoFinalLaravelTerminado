<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BlockedSlotForm from './Form.vue';

defineProps({
    spaces: Array,
});
</script>

<template>
    <AppLayout title="Nuevo Bloqueo">
        <template #header>
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-300">Bloqueos</div>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-white">Nuevo bloqueo</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <BlockedSlotForm :spaces="spaces" />
            </div>
        </div>
    </AppLayout>
</template>
