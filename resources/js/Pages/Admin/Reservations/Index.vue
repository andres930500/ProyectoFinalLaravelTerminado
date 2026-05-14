<script setup>
import Pagination from '@/Components/Pagination.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    reservations: Object,
    spaces: Array,
    filters: Object,
    statuses: Array,
    summary: Object,
});

const filterForm = useForm({
    space_id: props.filters?.space_id ?? '',
    status: props.filters?.status ?? '',
    date: props.filters?.date ?? '',
});

const confirmationAction = ref(null);
const currentReservation = ref(null);
const rejectionForm = useForm({ rejection_reason: '' });

const statusClasses = {
    pending: 'bg-amber-100 text-amber-800',
    confirmed: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-rose-100 text-rose-700',
    cancelled: 'bg-rose-100 text-rose-700',
    finished: 'bg-gray-100 text-gray-700',
};

function applyFilters() {
    router.get(route('admin.reservations.index'), { ...filterForm.data() }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function shortId(value) {
    return `#${String(value).padStart(4, '0')}`;
}

function formatDateTime(value) {
    return new Intl.DateTimeFormat('es-CO', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(value || 0));
}

function durationMinutes(reservation) {
    const start = new Date(reservation.start_time);
    const end = new Date(reservation.end_time);
    return Math.round((end - start) / 60000);
}

function openAction(action, reservation) {
    confirmationAction.value = action;
    currentReservation.value = reservation;
    rejectionForm.reset();
}

function closeAction() {
    confirmationAction.value = null;
    currentReservation.value = null;
    rejectionForm.reset();
    rejectionForm.clearErrors();
}

const modalTitle = computed(() => {
    if (confirmationAction.value === 'accept') return 'Confirmar reserva';
    if (confirmationAction.value === 'reject') return 'Rechazar reserva';
    if (confirmationAction.value === 'cancel') return 'Cancelar reserva';
    if (confirmationAction.value === 'finish') return 'Finalizar reserva';
    return '';
});

function submitAction() {
    if (!currentReservation.value || !confirmationAction.value) return;

    const routeName = {
        accept: 'admin.reservations.accept',
        reject: 'admin.reservations.reject',
        cancel: 'admin.reservations.cancel',
        finish: 'admin.reservations.finish',
    }[confirmationAction.value];

    const payload = confirmationAction.value === 'reject' ? rejectionForm.data() : {};

    router.post(route(routeName, currentReservation.value.slug), payload, {
        preserveScroll: true,
        onSuccess: () => closeAction(),
    });
}
</script>

<template>
    <AppLayout title="Reservas">
        <template #header>
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700">Reservas</div>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Control de solicitudes</h2>
                <p class="mt-2 text-sm text-slate-600">Filtra por estado, espacio y fecha para operar rapido.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-[1.75rem] border border-amber-200 bg-amber-50 p-5 shadow-xl shadow-amber-100/40">
                        <div class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-700">Pendientes</div>
                        <div class="mt-3 text-3xl font-semibold text-slate-900">{{ summary.pending }}</div>
                        <p class="mt-2 text-sm text-slate-600">Estas solicitudes aparecen primero para aprobar o rechazar rapido.</p>
                    </div>
                    <div class="rounded-[1.75rem] border border-emerald-200 bg-emerald-50 p-5 shadow-xl shadow-emerald-100/40">
                        <div class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-700">Confirmadas</div>
                        <div class="mt-3 text-3xl font-semibold text-slate-900">{{ summary.confirmed }}</div>
                        <p class="mt-2 text-sm text-slate-600">Reservas ya aceptadas por el equipo administrativo.</p>
                    </div>
                    <div class="rounded-[1.75rem] border border-sky-200 bg-sky-50 p-5 shadow-xl shadow-sky-100/40">
                        <div class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-700">Solicitudes de hoy</div>
                        <div class="mt-3 text-3xl font-semibold text-slate-900">{{ summary.today }}</div>
                        <p class="mt-2 text-sm text-slate-600">Nuevas reservas creadas en la jornada actual.</p>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                    <div class="grid gap-4 md:grid-cols-4">
                        <select v-model="filterForm.space_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" @change="applyFilters">
                            <option value="">Todas las canchas</option>
                            <option v-for="space in spaces" :key="space.id" :value="space.id">{{ space.name }}</option>
                        </select>
                        <select v-model="filterForm.status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" @change="applyFilters">
                            <option value="">Todos los estados</option>
                            <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                        </select>
                        <input v-model="filterForm.date" type="date" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" @change="applyFilters">
                        <button type="button" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50" @click="filterForm.space_id=''; filterForm.status=''; filterForm.date=''; applyFilters();">
                            Limpiar filtros
                        </button>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">ID</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Cancha</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Cliente</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Fecha/Hora</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Duracion</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Precio</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Estado</th>
                                    <th class="px-6 py-3 text-right font-semibold text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr v-for="reservation in reservations.data" :key="reservation.id">
                                    <td class="px-6 py-4 text-slate-500">{{ shortId(reservation.id) }}</td>
                                    <td class="px-6 py-4 font-medium text-slate-900">{{ reservation.space?.name }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">{{ reservation.user_name }}</div>
                                        <div class="text-slate-500">{{ reservation.user_email }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ formatDateTime(reservation.start_time) }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ durationMinutes(reservation) }} min</td>
                                    <td class="px-6 py-4 text-slate-600">{{ formatCurrency((durationMinutes(reservation) / 60) * Number(reservation.space?.price_per_hour || 0)) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClasses[reservation.status]">
                                            {{ reservation.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <button v-if="reservation.status === 'pending'" type="button" class="rounded-full bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700" @click="openAction('accept', reservation)">Aprobar</button>
                                            <button v-if="reservation.status === 'pending'" type="button" class="rounded-full bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700" @click="openAction('reject', reservation)">Rechazar</button>
                                            <button v-if="reservation.status === 'confirmed'" type="button" class="rounded-full bg-orange-500 px-3 py-2 text-xs font-semibold text-white hover:bg-orange-600" @click="openAction('cancel', reservation)">Cancelar</button>
                                            <button v-if="reservation.status === 'confirmed'" type="button" class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-black" @click="openAction('finish', reservation)">Finalizar</button>
                                            <Link :href="route('admin.reservations.show', reservation.slug)" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Ver</Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-200 px-6 py-4">
                        <Pagination :links="reservations.links" />
                    </div>
                </section>
            </div>
        </div>

        <div v-if="confirmationAction" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-[1.75rem] bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-semibold text-slate-950">{{ modalTitle }}</h3>
                <p class="mt-2 text-sm text-slate-600">
                    Esta accion actualizara la reserva de {{ currentReservation?.user_name }}.
                </p>

                <div v-if="confirmationAction === 'reject'" class="mt-4">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Motivo del rechazo</label>
                    <textarea v-model="rejectionForm.rejection_reason" rows="4" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    <p v-if="rejectionForm.errors.rejection_reason" class="mt-2 text-sm text-rose-600">{{ rejectionForm.errors.rejection_reason }}</p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50" @click="closeAction">
                        Cerrar
                    </button>
                    <button type="button" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600" @click="submitAction">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
