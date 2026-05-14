<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';
import { router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps({
    filters: Object,
    totalReservaciones: Number,
    reservasConfirmadas: Number,
    reservasPendientes: Number,
    reservasRechazadas: Number,
    ingresosTotales: Number,
    tasaConversion: Number,
    rankingCanchas: Array,
    reservasPorHora: Array,
    clientesFrecuentes: Array,
});

const form = reactive({
    from: props.filters?.from ?? '',
    to: props.filters?.to ?? '',
});

const gridColor = 'rgba(255,255,255,0.05)';
const tickColor = '#9CA3AF';

const promedioPorDia = computed(() => {
    if (!form.from || !form.to) return 0;

    const from = new Date(`${form.from}T00:00:00`);
    const to = new Date(`${form.to}T00:00:00`);
    const diff = Math.max(1, Math.round((to - from) / 86400000) + 1);

    return (props.totalReservaciones / diff).toFixed(1);
});

const maxRanking = computed(() => Math.max(...props.rankingCanchas.map((item) => item.total), 1));

const horasActivasData = computed(() => ({
    labels: props.reservasPorHora.map((item) => `${String(item.hour).padStart(2, '0')}:00`),
    datasets: [
        {
            label: 'Reservas',
            data: props.reservasPorHora.map((item) => item.total),
            backgroundColor: '#00C853',
            borderRadius: 8,
            borderSkipped: false,
        },
    ],
}));

const horasActivasOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: '#0b1510',
            borderColor: '#1e3a24',
            borderWidth: 1,
            titleColor: '#ffffff',
            bodyColor: '#d1d5db',
        },
    },
    scales: {
        x: {
            grid: {
                color: gridColor,
            },
            ticks: {
                color: tickColor,
                maxRotation: 0,
                autoSkip: true,
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: gridColor,
            },
            ticks: {
                color: tickColor,
            },
        },
    },
}));

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

function customerBadge(total) {
    if (total >= 6) {
        return {
            label: 'Frecuente',
            classes: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
        };
    }

    if (total >= 3) {
        return {
            label: 'Regular',
            classes: 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
        };
    }

    return {
        label: 'Nuevo',
        classes: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
    };
}

function applyFilters() {
    router.get(route('admin.reports'), {
        from: form.from,
        to: form.to,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function exportCsv() {
    const query = new URLSearchParams({
        from: form.from,
        to: form.to,
    });

    window.location.href = `${route('admin.reports.export')}?${query.toString()}`;
}
</script>

<template>
    <AppLayout title="Reportes">
        <template #header>
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700">Reportes</div>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Analitica de reservas</h2>
                    <p class="mt-2 text-sm text-slate-600">Mide conversion, horas pico, rendimiento por cancha y clientes recurrentes.</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Desde</label>
                                <input v-model="form.from" type="date" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-0">
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Hasta</label>
                                <input v-model="form.to" type="date" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-0">
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button type="button" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50" @click="applyFilters">
                                Aplicar filtro
                            </button>
                            <button type="button" class="inline-flex items-center justify-center rounded-2xl bg-[#00C853] px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-[#00b84c]" @click="exportCsv">
                                Exportar CSV
                            </button>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                        <div class="text-sm text-slate-500">Total reservas</div>
                        <div class="mt-4 text-3xl font-semibold text-slate-950">{{ totalReservaciones }}</div>
                    </article>
                    <article class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                        <div class="text-sm text-slate-500">Ingresos totales</div>
                        <div class="mt-4 text-3xl font-semibold text-slate-950">{{ formatCurrency(ingresosTotales) }}</div>
                    </article>
                    <article class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                        <div class="text-sm text-slate-500">Tasa conversion</div>
                        <div class="mt-4 text-3xl font-semibold text-slate-950">{{ tasaConversion }}%</div>
                    </article>
                    <article class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                        <div class="text-sm text-slate-500">Promedio por dia</div>
                        <div class="mt-4 text-3xl font-semibold text-slate-950">{{ promedioPorDia }}</div>
                    </article>
                </section>

                <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                    <article class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                        <div class="mb-5">
                            <h3 class="text-lg font-semibold text-slate-950">Ranking de canchas</h3>
                            <p class="mt-1 text-sm text-slate-500">Ordenadas por reservas confirmadas dentro del rango.</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div
                                v-for="space in rankingCanchas"
                                :key="space.id"
                                class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="font-medium text-slate-950">{{ space.name }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ space.total }} confirmadas</div>
                                    </div>
                                    <div class="text-right text-sm font-medium text-emerald-700">{{ formatCurrency(space.ingresos) }}</div>
                                </div>
                                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-200">
                                    <div
                                        class="h-full rounded-full bg-[#00C853]"
                                        :style="{ width: `${(space.total / maxRanking) * 100}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                        <div class="mb-5">
                            <h3 class="text-lg font-semibold text-slate-950">Horas mas activas</h3>
                            <p class="mt-1 text-sm text-slate-500">Comportamiento por hora segun inicio de reserva.</p>
                        </div>
                        <div class="h-[26rem]">
                            <Bar :data="horasActivasData" :options="horasActivasOptions" />
                        </div>
                    </article>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                    <div class="mb-5">
                        <h3 class="text-lg font-semibold text-slate-950">Clientes frecuentes</h3>
                        <p class="mt-1 text-sm text-slate-500">Top 10 usuarios por correo dentro del rango filtrado.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-slate-500">
                                    <th class="px-4 py-3 font-semibold">Cliente</th>
                                    <th class="px-4 py-3 font-semibold">Email</th>
                                    <th class="px-4 py-3 font-semibold">Reservas</th>
                                    <th class="px-4 py-3 font-semibold">Ultima reserva</th>
                                    <th class="px-4 py-3 font-semibold">Perfil</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr v-for="customer in clientesFrecuentes" :key="customer.user_email">
                                    <td class="px-4 py-4 text-slate-950">{{ customer.user_name }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ customer.user_email }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ customer.total_reservas }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ formatDateTime(customer.ultima_reserva) }}</td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="customerBadge(customer.total_reservas).classes">
                                            {{ customerBadge(customer.total_reservas).label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!clientesFrecuentes.length">
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">No hay clientes dentro del rango seleccionado.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
