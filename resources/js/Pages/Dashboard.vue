<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SpaceStatusWidget from '@/Components/SpaceStatusWidget.vue';
import { Link, router } from '@inertiajs/vue3';
import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import { computed, reactive, ref, watch } from 'vue';
import { Bar, Doughnut, Line } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

const props = defineProps({
    pendingToday: Number,
    confirmedThisWeek: Number,
    upcomingReservations: Array,
    spaces: Array,
    dailyReservedHours: Array,
    reservationsByStatus: Array,
    spaceOccupancy: Array,
    weeklyIncome: Array,
    estadoActual: Array,
    selectedFrom: String,
    selectedTo: String,
});

const filters = reactive({
    from: props.selectedFrom,
    to: props.selectedTo,
});
const mobileRangeOpen = ref(false);

let filterUpdateTimeout = null;

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

const statusChartColors = {
    pending: '#F59E0B',
    confirmed: '#10B981',
    rejected: '#EF4444',
    cancelled: '#EF4444',
    finished: '#94A3B8',
};

const statusLabels = {
    pending: 'Pendiente',
    confirmed: 'Confirmada',
    rejected: 'Rechazada',
    cancelled: 'Cancelada',
    finished: 'Finalizada',
};

const axisTickColor = '#64748B';
const gridColor = 'rgba(15,23,42,0.08)';

function formatDateTime(value) {
    return new Intl.DateTimeFormat('es-CO', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function formatShortDate(value) {
    return new Intl.DateTimeFormat('es-CO', {
        month: 'short',
        day: 'numeric',
    }).format(new Date(`${value}T00:00:00`));
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));
}

function buildRangeValues(items, from, to, valueKey = 'total') {
    const index = Object.fromEntries((items || []).map((item) => [item.fecha, Number(item[valueKey] ?? 0)]));
    const start = new Date(`${from}T00:00:00`);
    const end = new Date(`${to}T00:00:00`);
    const values = [];

    for (const date = new Date(start); date <= end; date.setDate(date.getDate() + 1)) {
        const key = date.toISOString().slice(0, 10);

        values.push({
            label: formatShortDate(key),
            value: index[key] ?? 0,
        });
    }

    return values;
}

function applyDateFilters() {
    router.get(route('dashboard'), {
        from: filters.from,
        to: filters.to,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function setQuickRange(days) {
    const end = new Date();
    end.setHours(0, 0, 0, 0);

    const start = new Date(end);
    start.setDate(start.getDate() - (days - 1));

    filters.from = start.toISOString().slice(0, 10);
    filters.to = end.toISOString().slice(0, 10);
}

function isQuickRangeActive(days) {
    const end = new Date();
    end.setHours(0, 0, 0, 0);

    const start = new Date(end);
    start.setDate(start.getDate() - (days - 1));

    return filters.from === start.toISOString().slice(0, 10)
        && filters.to === end.toISOString().slice(0, 10);
}

const mobileRangeLabel = computed(() => `${formatShortDate(filters.from)} - ${formatShortDate(filters.to)}`);

watch(() => [filters.from, filters.to], ([nextFrom, nextTo]) => {
    if (!nextFrom || !nextTo) return;
    if (nextFrom === props.selectedFrom && nextTo === props.selectedTo) return;

    if (filterUpdateTimeout) {
        window.clearTimeout(filterUpdateTimeout);
    }

    filterUpdateTimeout = window.setTimeout(() => {
        applyDateFilters();
    }, 250);
});

const baseCartesianOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: axisTickColor,
            },
        },
        tooltip: {
            backgroundColor: '#ffffff',
            borderColor: '#d1fae5',
            borderWidth: 1,
            titleColor: '#0f172a',
            bodyColor: '#334155',
        },
    },
    scales: {
        x: {
            grid: {
                color: gridColor,
            },
            ticks: {
                color: axisTickColor,
            },
        },
        y: {
            grid: {
                color: gridColor,
            },
            ticks: {
                color: axisTickColor,
            },
            beginAtZero: true,
        },
    },
};

const reservedHoursLineData = computed(() => {
    const values = buildRangeValues(props.dailyReservedHours, props.selectedFrom, props.selectedTo);

    return {
        labels: values.map((item) => item.label),
        datasets: [
            {
                label: 'Horas reservadas',
                data: values.map((item) => item.value),
                borderColor: '#00C853',
                backgroundColor: 'rgba(0, 200, 83, 0.18)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 5,
                pointBackgroundColor: '#00C853',
            },
        ],
    };
});

const reservedHoursLineOptions = computed(() => ({
    ...baseCartesianOptions,
    plugins: {
        ...baseCartesianOptions.plugins,
        legend: {
            display: false,
        },
        tooltip: {
            ...baseCartesianOptions.plugins.tooltip,
            callbacks: {
                label: (context) => ` ${context.raw} h reservadas`,
            },
        },
    },
    scales: {
        ...baseCartesianOptions.scales,
        y: {
            ...baseCartesianOptions.scales.y,
            ticks: {
                color: axisTickColor,
                callback: (value) => `${value} h`,
            },
        },
    },
}));

const statusDonutData = computed(() => ({
    labels: props.reservationsByStatus.map((item) => statusLabels[item.status] ?? item.status),
    datasets: [
        {
            data: props.reservationsByStatus.map((item) => Number(item.total ?? 0)),
            backgroundColor: props.reservationsByStatus.map((item) => statusChartColors[item.status] ?? '#94A3B8'),
            borderWidth: 0,
        },
    ],
}));

const statusDonutOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    cutout: '68%',
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                color: axisTickColor,
                padding: 18,
            },
        },
        tooltip: {
            backgroundColor: '#ffffff',
            borderColor: '#d1fae5',
            borderWidth: 1,
            titleColor: '#0f172a',
            bodyColor: '#334155',
        },
    },
}));

const occupancyBarData = computed(() => ({
    labels: props.spaceOccupancy.map((space) => space.name),
    datasets: [
        {
            label: 'Confirmadas',
            data: props.spaceOccupancy.map((space) => Number(space.confirmadas ?? 0)),
            backgroundColor: ['#00C853', '#10B981', '#34D399', '#6EE7B7'],
            borderRadius: 8,
            borderSkipped: false,
        },
    ],
}));

const occupancyBarOptions = computed(() => ({
    ...baseCartesianOptions,
    indexAxis: 'y',
    plugins: {
        ...baseCartesianOptions.plugins,
        legend: {
            display: false,
        },
    },
}));

const weeklyIncomeData = computed(() => {
    const values = buildRangeValues(props.weeklyIncome, props.selectedFrom, props.selectedTo);

    return {
        labels: values.map((item) => item.label),
        datasets: [
            {
                label: 'Ingresos',
                data: values.map((item) => item.value),
                backgroundColor: '#00C853',
                borderRadius: 8,
                borderSkipped: false,
            },
        ],
    };
});

const weeklyIncomeOptions = computed(() => ({
    ...baseCartesianOptions,
    plugins: {
        ...baseCartesianOptions.plugins,
        legend: {
            display: false,
        },
        tooltip: {
            ...baseCartesianOptions.plugins.tooltip,
            callbacks: {
                label: (context) => ` ${formatCurrency(context.raw)}`,
            },
        },
    },
    scales: {
        ...baseCartesianOptions.scales,
        y: {
            ...baseCartesianOptions.scales.y,
            ticks: {
                color: axisTickColor,
                callback: (value) => formatCurrency(value),
            },
        },
    },
}));
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-600">Panel administrativo</div>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Resumen operativo</h2>
                    <p class="mt-2 text-sm text-slate-600">Estado actual de reservas, uso de espacios y actividad inmediata.</p>
                </div>
                <div class="w-full rounded-[1.2rem] border border-emerald-100 bg-white/95 p-3 shadow-lg shadow-emerald-100/30 lg:max-w-[660px]">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-[0.95rem] border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-left lg:hidden"
                        @click="mobileRangeOpen = !mobileRangeOpen"
                    >
                        <div>
                            <div class="text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-700">Rango</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ mobileRangeLabel }}</div>
                        </div>
                        <svg class="h-4 w-4 text-slate-500 transition-transform" :class="mobileRangeOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                        </svg>
                    </button>

                    <div class="mt-3 flex flex-col gap-3 lg:mt-0 lg:flex lg:flex-row lg:items-center lg:justify-end" :class="mobileRangeOpen ? 'flex' : 'hidden lg:flex'">
                        <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:justify-end">
                            <span class="shrink-0 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">Rango</span>
                            <button
                                type="button"
                                class="shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold transition"
                                :class="isQuickRangeActive(7) ? 'bg-emerald-500 text-slate-950' : 'bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700'"
                                @click="setQuickRange(7)"
                            >
                                7 dias
                            </button>
                            <button
                                type="button"
                                class="shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold transition"
                                :class="isQuickRangeActive(15) ? 'bg-emerald-500 text-slate-950' : 'bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700'"
                                @click="setQuickRange(15)"
                            >
                                15 dias
                            </button>
                            <button
                                type="button"
                                class="shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold transition"
                                :class="isQuickRangeActive(30) ? 'bg-emerald-500 text-slate-950' : 'bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700'"
                                @click="setQuickRange(30)"
                            >
                                30 dias
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2 lg:min-w-[320px]">
                            <label class="rounded-[0.95rem] border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm text-slate-600 transition focus-within:border-emerald-300 focus-within:bg-white">
                                <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">Desde</span>
                                <input v-model="filters.from" type="date" class="mt-1 block w-full border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 shadow-none focus:ring-0">
                            </label>
                            <label class="rounded-[0.95rem] border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm text-slate-600 transition focus-within:border-emerald-300 focus-within:bg-white">
                                <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">Hasta</span>
                                <input v-model="filters.to" type="date" class="mt-1 block w-full border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 shadow-none focus:ring-0">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <section class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-[1.75rem] border border-emerald-100 bg-white p-6 shadow-xl shadow-emerald-100/40">
                        <p class="text-sm font-medium text-slate-500">Pendientes Hoy</p>
                        <p class="mt-4 text-4xl font-semibold text-slate-950">{{ pendingToday }}</p>
                        <p class="mt-2 text-sm text-slate-500">Solicitudes esperando decision administrativa.</p>
                    </div>
                    <div class="rounded-[1.75rem] bg-emerald-500 p-6 text-slate-950 shadow-xl shadow-emerald-200">
                        <p class="text-sm font-medium text-emerald-950/70">Confirmadas Esta Semana</p>
                        <p class="mt-4 text-4xl font-semibold">{{ confirmedThisWeek }}</p>
                        <p class="mt-2 text-sm text-emerald-950/70">Reservas aprobadas y listas para operar.</p>
                    </div>
                    <div class="rounded-[1.75rem] border border-emerald-100 bg-[#f7fbf8] p-6 shadow-xl shadow-emerald-100/30">
                        <p class="text-sm font-medium text-slate-500">Total Canchas Activas</p>
                        <p class="mt-4 text-4xl font-semibold text-slate-950">{{ spaces.length }}</p>
                        <p class="mt-2 text-sm text-slate-500">Espacios visibles hoy en el modulo publico.</p>
                    </div>
                </section>

                <section class="grid gap-6 lg:grid-cols-2">
                    <article class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-xl shadow-emerald-100/40">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-slate-900">Horas reservadas por dia</h3>
                            <p class="mt-1 text-sm text-slate-500">Carga operativa real del sistema segun reservas confirmadas en el rango elegido.</p>
                        </div>
                        <div class="h-80">
                            <Line :data="reservedHoursLineData" :options="reservedHoursLineOptions" />
                        </div>
                    </article>

                    <article class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-xl shadow-emerald-100/40">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-slate-900">Reservas por estado</h3>
                            <p class="mt-1 text-sm text-slate-500">Distribucion del flujo de solicitudes en el rango elegido.</p>
                        </div>
                        <div class="h-80">
                            <Doughnut :data="statusDonutData" :options="statusDonutOptions" />
                        </div>
                    </article>

                    <article class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-xl shadow-emerald-100/40">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-slate-900">Reservas por cancha</h3>
                            <p class="mt-1 text-sm text-slate-500">Confirmadas por espacio activo dentro del rango elegido.</p>
                        </div>
                        <div class="h-80">
                            <Bar :data="occupancyBarData" :options="occupancyBarOptions" />
                        </div>
                    </article>

                    <article class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-xl shadow-emerald-100/40">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-slate-900">Ingresos por rango</h3>
                            <p class="mt-1 text-sm text-slate-500">Valor estimado de reservas confirmadas dentro del rango elegido.</p>
                        </div>
                        <div class="h-80">
                            <Bar :data="weeklyIncomeData" :options="weeklyIncomeOptions" />
                        </div>
                    </article>
                </section>

                <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <section class="overflow-hidden rounded-[1.75rem] border border-emerald-100 bg-white shadow-xl shadow-emerald-100/40">
                        <div class="border-b border-emerald-100 px-6 py-5">
                            <h3 class="text-lg font-semibold text-slate-950">Proximas reservas</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-emerald-100 text-sm">
                                <thead class="bg-[#f7fbf8]">
                                    <tr>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-600">Cancha</th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-600">Cliente</th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-600">Fecha/Hora</th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-600">Estado</th>
                                        <th class="px-6 py-3 text-right font-semibold text-slate-600">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-emerald-100 bg-white">
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

                    <SpaceStatusWidget :spaces="estadoActual" />
                </section>

                <section class="overflow-hidden rounded-[1.75rem] border border-emerald-100 bg-white shadow-xl shadow-emerald-100/40">
                    <div class="border-b border-emerald-100 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-950">Canchas</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-emerald-100 text-sm">
                            <thead class="bg-[#f7fbf8]">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Nombre</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Tipo</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Reservas este mes</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100 bg-white">
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
