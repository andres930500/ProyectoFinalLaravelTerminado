<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    spaces: {
        type: Array,
        default: () => [],
    },
});

const now = ref(new Date());
let timerId = null;

const typeLabels = {
    cancha_cesped: 'Cesped',
    cancha_sintetica: 'Sintetica',
    cancha_futbol_sala: 'Futbol Sala',
    cancha_futbol_playa: 'Futbol Playa',
};

const statusMap = {
    available: {
        label: 'Libre',
        dot: 'bg-emerald-400 shadow-[0_0_12px_rgba(52,211,153,0.5)]',
        pulse: '',
        badge: 'bg-emerald-500/15 text-emerald-700 ring-1 ring-emerald-500/20',
    },
    confirmed: {
        label: 'Ocupada',
        dot: 'bg-sky-400 shadow-[0_0_12px_rgba(56,189,248,0.45)]',
        pulse: 'animate-pulse',
        badge: 'bg-sky-500/15 text-sky-700 ring-1 ring-sky-500/20',
    },
    pending: {
        label: 'Pendiente',
        dot: 'bg-amber-400 shadow-[0_0_12px_rgba(251,191,36,0.45)]',
        pulse: '',
        badge: 'bg-amber-500/15 text-amber-700 ring-1 ring-amber-500/20',
    },
    blocked: {
        label: 'Bloqueada',
        dot: 'bg-rose-400 shadow-[0_0_12px_rgba(251,113,133,0.45)]',
        pulse: '',
        badge: 'bg-rose-500/15 text-rose-700 ring-1 ring-rose-500/20',
    },
    closed: {
        label: 'Cerrada',
        dot: 'bg-slate-500',
        pulse: '',
        badge: 'bg-slate-500/15 text-slate-700 ring-1 ring-slate-500/20',
    },
};

const currentTime = computed(() =>
    new Intl.DateTimeFormat('es-CO', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(now.value)
);

function describeSpace(space) {
    if (space.estado === 'blocked' && space.bloqueo_actual) {
        return `Bloqueada hasta las ${space.bloqueo_actual.hasta} - ${space.bloqueo_actual.motivo}`;
    }

    if ((space.estado === 'confirmed' || space.estado === 'pending') && space.reserva_actual) {
        return `Ocupada hasta las ${space.reserva_actual.hasta} - ${space.reserva_actual.cliente}`;
    }

    if (space.estado === 'closed') {
        return space.horario_hoy
            ? `Fuera de horario · hoy ${space.horario_hoy}`
            : 'Fuera de horario';
    }

    if (space.proxima_reserva) {
        return `Disponible · proxima reserva a las ${space.proxima_reserva.a_las}`;
    }

    return space.horario_hoy
        ? `Disponible · horario de hoy ${space.horario_hoy}`
        : 'Disponible sin reservas proximas';
}

function statusConfig(state) {
    return statusMap[state] ?? statusMap.closed;
}

onMounted(() => {
    timerId = window.setInterval(() => {
        now.value = new Date();
    }, 60000);
});

onBeforeUnmount(() => {
    if (timerId) {
        window.clearInterval(timerId);
    }
});
</script>

<template>
    <article class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-xl shadow-emerald-100/50">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Estado actual de canchas</h3>
                <p class="mt-1 text-sm text-slate-500">Lectura operativa del momento por disponibilidad, reserva o bloqueo.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-sm font-medium text-slate-500">{{ currentTime }}</div>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 ring-1 ring-emerald-200">
                    <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-400"></span>
                    En vivo
                </span>
            </div>
        </div>

        <div class="space-y-3">
            <div
                v-for="space in spaces"
                :key="space.id"
                class="flex flex-col gap-4 rounded-2xl border border-emerald-100 bg-[#f7fbf8] px-4 py-4 transition hover:border-emerald-200 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="flex items-start gap-4">
                    <span
                        class="mt-1 inline-flex h-3.5 w-3.5 shrink-0 rounded-full"
                        :class="[statusConfig(space.estado).dot, statusConfig(space.estado).pulse]"
                    ></span>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-medium text-slate-900">{{ space.name }}</div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-medium text-slate-500 ring-1 ring-emerald-100">
                                {{ typeLabels[space.type] ?? space.type }}
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-slate-600">{{ describeSpace(space) }}</div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 lg:justify-end">
                    <div v-if="space.horario_hoy" class="text-xs text-slate-500">
                        Hoy {{ space.horario_hoy }}
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="statusConfig(space.estado).badge">
                        {{ statusConfig(space.estado).label }}
                    </span>
                </div>
            </div>

            <div v-if="!spaces.length" class="rounded-2xl border border-dashed border-emerald-100 bg-[#f7fbf8] px-4 py-8 text-center text-sm text-slate-500">
                No hay canchas activas para mostrar en este momento.
            </div>
        </div>
    </article>
</template>
