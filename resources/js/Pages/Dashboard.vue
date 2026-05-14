<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SpaceStatusWidget from '@/Components/SpaceStatusWidget.vue';
import { Link } from '@inertiajs/vue3';
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
import { computed } from 'vue';
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
    reservationsByDay: Array,
    reservationsByStatus: Array,
    spaceOccupancy: Array,
    weeklyIncome: Array,
    estadoActual: Array,
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

const statusChartColors = {
    pending: '#F59E0B',
    confirmed: '#10B981',
    rejected: '#EF4444',
    cancelled: '#EF4444',
    finished: '#94A3B8',
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

function buildLastSevenDays(items, valueKey = 'total') {
    const index = Object.fromEntries((items || []).map((item) => [item.fecha, Number(item[valueKey] ?? 0)]));

    return Array.from({ length: 7 }, (_, offset) => {
        const date = new Date();
        date.setHours(0, 0, 0, 0);
        date.setDate(date.getDate() - (6 - offset));
        const key = date.toISOString().slice(0, 10);

        return {
            label: formatShortDate(key),
            value: index[key] ?? 0,
        };
    });
}

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

const reservationsLineData = computed(() => {
    const values = buildLastSevenDays(props.reservationsByDay);

    return {
        labels: values.map((item) => item.label),
        datasets: [
            {
                label: 'Reservas',
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

const reservationsLineOptions = computed(() => ({
    ...baseCartesianOptions,
    plugins: {
        ...baseCartesianOptions.plugins,
        legend: {
            display: false,
        },
    },
}));

const statusDonutData = computed(() => ({
    labels: props.reservationsByStatus.map((item) => item.status),
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
    const values = buildLastSevenDays(props.weeklyIncome);

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
                            <h3 class="text-lg font-semibold text-slate-900">Reservas ultimos 7 dias</h3>
                            <p class="mt-1 text-sm text-slate-500">Actividad reciente de creacion de reservas.</p>
                        </div>
                        <div class="h-80">
                            <Line :data="reservationsLineData" :options="reservationsLineOptions" />
                        </div>
                    </article>

                    <article class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-xl shadow-emerald-100/40">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-slate-900">Reservas por estado</h3>
                            <p class="mt-1 text-sm text-slate-500">Distribucion actual del flujo de solicitudes.</p>
                        </div>
                        <div class="h-80">
                            <Doughnut :data="statusDonutData" :options="statusDonutOptions" />
                        </div>
                    </article>

                    <article class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-xl shadow-emerald-100/40">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-slate-900">Reservas por cancha</h3>
                            <p class="mt-1 text-sm text-slate-500">Confirmadas este mes por espacio activo.</p>
                        </div>
                        <div class="h-80">
                            <Bar :data="occupancyBarData" :options="occupancyBarOptions" />
                        </div>
                    </article>

                    <article class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-xl shadow-emerald-100/40">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-slate-900">Ingresos por semana</h3>
                            <p class="mt-1 text-sm text-slate-500">Valor estimado de reservas confirmadas recientes.</p>
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
