<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    pendingToday: Number,
    confirmedThisWeek: Number,
    upcomingReservations: Array,
    spaces: Array,
});

const statusClasses = {
    pending: 'bg-amber-100 text-amber-800',
    confirmed: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-rose-100 text-rose-700',
    cancelled: 'bg-rose-100 text-rose-700',
    finished: 'bg-gray-100 text-gray-700',
};

const typeLabels = {
    cancha_cesped: 'Cesped',
    cancha_sintetica: 'Sintetica',
    cancha_futbol_sala: 'Futbol Sala',
    cancha_futbol_playa: 'Futbol Playa',
};

function formatDateTime(value) {
    return new Intl.DateTimeFormat('es-CO', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-300">Panel administrativo</div>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-white">Resumen operativo</h2>
                    <p class="mt-2 text-sm text-slate-300">Estado actual de reservas, uso de espacios y actividad inmediata.</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <section class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-[1.75rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/15">
                        <p class="text-sm font-medium text-slate-300">Pendientes Hoy</p>
                        <p class="mt-4 text-4xl font-semibold">{{ pendingToday }}</p>
                        <p class="mt-2 text-sm text-slate-400">Solicitudes esperando decision administrativa.</p>
                    </div>
                    <div class="rounded-[1.75rem] bg-emerald-500 p-6 text-slate-950 shadow-2xl shadow-emerald-200">
                        <p class="text-sm font-medium text-emerald-950/70">Confirmadas Esta Semana</p>
                        <p class="mt-4 text-4xl font-semibold">{{ confirmedThisWeek }}</p>
                        <p class="mt-2 text-sm text-emerald-950/70">Reservas aprobadas y listas para operar.</p>
                    </div>
                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/40">
                        <p class="text-sm font-medium text-slate-500">Total Canchas Activas</p>
                        <p class="mt-4 text-4xl font-semibold text-slate-950">{{ spaces.length }}</p>
                        <p class="mt-2 text-sm text-slate-500">Espacios visibles hoy en el modulo publico.</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-950">Proximas reservas</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Cancha</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Cliente</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Fecha/Hora</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Estado</th>
                                    <th class="px-6 py-3 text-right font-semibold text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr v-for="reservation in upcomingReservations" :key="reservation.id">
                                    <td class="px-6 py-4 text-slate-900">{{ reservation.space?.name }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ reservation.user_name }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ formatDateTime(reservation.start_time) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClasses[reservation.status]">
                                            {{ reservation.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('admin.reservations.show', reservation.slug)" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
                                            Ver
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!upcomingReservations.length">
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-500">No hay reservas confirmadas proximas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-950">Canchas</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Nombre</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Tipo</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Reservas este mes</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr v-for="space in spaces" :key="space.id">
                                    <td class="px-6 py-4 font-medium text-slate-900">{{ space.name }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ typeLabels[space.type] ?? space.type }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ space.reservations_this_month }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="space.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700'">
                                            {{ space.is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
