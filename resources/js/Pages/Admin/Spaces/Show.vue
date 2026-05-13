<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    space: Object,
});
</script>

<template>
    <AppLayout :title="space.name">
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ space.name }}</h2>
                <Link :href="route('admin.spaces.edit', space.slug)" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    Editar
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <dl class="grid gap-6 md:grid-cols-2">
                        <div>
                            <dt class="text-sm text-gray-500">Tipo</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ space.type }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Capacidad</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ space.capacity }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Precio por hora</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ space.price_per_hour }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Estado</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ space.is_active ? 'Activo' : 'Inactivo' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
