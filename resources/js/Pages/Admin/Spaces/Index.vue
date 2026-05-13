<script setup>
import Pagination from '@/Components/Pagination.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    spaces: Object,
});

function formatCurrency(value) {
    const amount = Number(value ?? 0);
    if (amount <= 0) return 'Gratis';
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(amount);
}

function toggleSpace(space) {
    router.put(route('admin.spaces.update', space.slug), {
        name: space.name,
        type: space.type,
        capacity: space.capacity,
        price_per_hour: space.price_per_hour,
        description: space.description,
        rules: space.rules,
        image: space.image,
        is_active: !space.is_active,
        availabilities: (space.availabilities || []).map((availability) => ({
            day_of_week: availability.day_of_week,
            enabled: true,
            start_time: availability.start_time.slice(0, 5),
            end_time: availability.end_time.slice(0, 5),
        })),
    }, {
        preserveScroll: true,
    });
}

function destroySpace(space) {
    if (window.confirm(`Eliminar ${space.name}?`)) {
        router.delete(route('admin.spaces.destroy', space.slug), { preserveScroll: true });
    }
}
</script>

<template>
    <AppLayout title="Canchas">
        <template #header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-300">Espacios</div>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-white">Gestion de canchas</h2>
                    <p class="mt-2 text-sm text-slate-300">Administra estado, capacidad, precio y disponibilidad semanal.</p>
                </div>
                <Link :href="route('admin.spaces.create')" class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">
                    Nueva Cancha
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">ID</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Nombre</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Tipo</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Capacidad</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Precio/hora</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Estado</th>
                                    <th class="px-6 py-3 text-right font-semibold text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr v-for="space in spaces.data" :key="space.id">
                                    <td class="px-6 py-4 text-slate-500">{{ space.id }}</td>
                                    <td class="px-6 py-4 font-medium text-slate-900">{{ space.name }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ space.type }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ space.capacity }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ formatCurrency(space.price_per_hour) }}</td>
                                    <td class="px-6 py-4">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold transition"
                                            :class="space.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700'"
                                            @click="toggleSpace(space)"
                                        >
                                            {{ space.is_active ? 'Activo' : 'Inactivo' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-3">
                                            <Link :href="route('admin.spaces.edit', space.slug)" class="rounded-full bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">Editar</Link>
                                            <Link :href="route('admin.spaces.edit', space.slug)" class="rounded-full bg-sky-50 px-3 py-2 text-sm font-medium text-sky-700 transition hover:bg-sky-100">Disponibilidad</Link>
                                            <button type="button" class="rounded-full bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100" @click="destroySpace(space)">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-200 px-6 py-4">
                        <Pagination :links="spaces.links" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
