<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
    reservations: {
        type: Array,
        default: () => [],
    },
});

const statusConfig = {
    pending: {
        badge: 'bg-amber-500/15 text-amber-300 ring-1 ring-amber-500/30',
        dot: 'bg-amber-400',
        label: 'Pendiente',
    },
    confirmed: {
        badge: 'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-500/30',
        dot: 'bg-emerald-400',
        label: 'Confirmada',
    },
    rejected: {
        badge: 'bg-rose-500/15 text-rose-300 ring-1 ring-rose-500/30',
        dot: 'bg-rose-400',
        label: 'Rechazada',
    },
    cancelled: {
        badge: 'bg-rose-500/15 text-rose-300 ring-1 ring-rose-500/30',
        dot: 'bg-rose-400',
        label: 'Cancelada',
    },
    finished: {
        badge: 'bg-slate-500/15 text-slate-300 ring-1 ring-slate-500/30',
        dot: 'bg-slate-400',
        label: 'Finalizada',
    },
};

const profileStats = computed(() => [
    { label: 'Total reservas', value: props.client.total_reservas },
    { label: 'Confirmadas', value: props.client.reservas_confirmadas },
    { label: 'Total gastado', value: formatCurrency(props.client.total_gastado) },
    { label: 'Cancha favorita', value: props.client.cancha_favorita || '-' },
]);

function initials(name) {
    if (!name) return 'CL';

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));
}

function formatDateTime(value) {
    if (!value) return '-';

    return new Intl.DateTimeFormat('es-CO', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function reservationStatus(status) {
    return statusConfig[status] ?? statusConfig.finished;
}
</script>

<template>
    <AppLayout :title="client.nombre">
        <template #header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700">Perfil de cliente</div>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ client.nombre }}</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-600">
                        Historial completo de reservas, gasto y comportamiento para una atencion mas personalizada.
                    </p>
                </div>

                <Link
                    :href="route('admin.clients.index')"
                    class="inline-flex items-center rounded-2xl border border-[#1e3a24] bg-[#111f16] px-5 py-3 text-sm font-medium text-slate-200 transition hover:bg-[#132218]"
                >
                    Volver a clientes
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/40">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-emerald-50 text-xl font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                {{ initials(client.nombre) }}
                            </div>
                            <div>
                                <div class="text-2xl font-semibold text-slate-950">{{ client.nombre }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ client.email }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ client.telefono || 'Sin telefono registrado' }}</div>
                            </div>
                        </div>

                        <div class="grid w-full gap-4 sm:grid-cols-2 xl:w-auto xl:grid-cols-2 2xl:grid-cols-4">
                            <article
                                v-for="stat in profileStats"
                                :key="stat.label"
                                class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 xl:min-w-[11rem]"
                            >
                                <div class="text-sm text-slate-500">{{ stat.label }}</div>
                                <div class="mt-3 text-2xl font-semibold text-slate-950">{{ stat.value }}</div>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/40">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-950">Timeline de reservas</h3>
                        <p class="mt-1 text-sm text-slate-500">Cada reserva del cliente ordenada desde la mas reciente.</p>
                    </div>

                    <div class="space-y-5">
                        <div
                            v-for="(reservation, index) in reservations"
                            :key="reservation.id"
                            class="relative pl-10"
                        >
                            <div
                                v-if="index !== reservations.length - 1"
                                class="absolute left-[0.93rem] top-8 h-[calc(100%+0.75rem)] w-px bg-slate-200"
                            ></div>
                            <div
                                class="absolute left-0 top-2.5 flex h-8 w-8 items-center justify-center rounded-full ring-4 ring-white"
                                :class="reservationStatus(reservation.status).dot"
                            ></div>

                            <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h4 class="text-base font-semibold text-slate-950">{{ reservation.space?.name || 'Cancha sin nombre' }}</h4>
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="reservationStatus(reservation.status).badge">
                                                {{ reservationStatus(reservation.status).label }}
                                            </span>
                                        </div>
                                        <div class="mt-2 text-sm text-slate-600">{{ formatDateTime(reservation.start_time) }}</div>
                                        <div class="mt-1 text-sm text-slate-500">
                                            Duracion {{ reservation.duration_minutes }} min
                                        </div>
                                    </div>

                                    <div class="grid gap-2 text-sm text-slate-600 sm:grid-cols-2 lg:min-w-[18rem]">
                                        <div>
                                            <span class="text-slate-400">Precio:</span>
                                            {{ formatCurrency(reservation.total_price) }}
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Fin:</span>
                                            {{ formatDateTime(reservation.end_time) }}
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Codigo:</span>
                                            {{ reservation.slug }}
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div v-if="!reservations.length" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-slate-500">
                            Este cliente aun no tiene reservas registradas.
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
