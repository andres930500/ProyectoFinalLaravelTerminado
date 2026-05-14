<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    reservation: Object,
});

const statusClasses = {
    pending: 'bg-amber-100 text-amber-800',
    confirmed: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-rose-100 text-rose-700',
    cancelled: 'bg-rose-100 text-rose-700',
    finished: 'bg-gray-100 text-gray-700',
};

const rejectOpen = ref(false);
const rejectForm = useForm({ rejection_reason: '' });

const currentStatusIcon = computed(() => ({
    pending: 'P',
    confirmed: 'C',
    rejected: 'R',
    cancelled: 'X',
    finished: 'F',
}[props.reservation.status] ?? 'S'));

function formatDateTime(value) {
    return new Intl.DateTimeFormat('es-CO', { dateStyle: 'full', timeStyle: 'short' }).format(new Date(value));
}

function submitAction(action) {
    const routeName = {
        accept: 'admin.reservations.accept',
        cancel: 'admin.reservations.cancel',
        finish: 'admin.reservations.finish',
    }[action];

    router.post(route(routeName, props.reservation.slug), {}, { preserveScroll: true });
}

function submitReject() {
    rejectForm.post(route('admin.reservations.reject', props.reservation.slug), {
        preserveScroll: true,
        onSuccess: () => {
            rejectOpen.value = false;
            rejectForm.reset();
        },
    });
}
</script>

<template>
    <AppLayout title="Detalle de Reserva">
        <template #header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700">Detalle</div>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Reserva {{ reservation.slug }}</h2>
                </div>
                <Link :href="route('admin.reservations.index')" class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-white hover:bg-white/10">
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:px-8">
                <div class="space-y-6">
                    <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/40">
                        <h3 class="text-lg font-semibold text-slate-950">Datos del cliente</h3>
                        <dl class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <dt class="text-sm text-slate-500">Nombre</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ reservation.user_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-slate-500">Email</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ reservation.user_email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-slate-500">Telefono</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ reservation.user_phone || 'No registrado' }}</dd>
                            </div>
                            <div class="md:col-span-2">
                                <dt class="text-sm text-slate-500">Notas</dt>
                                <dd class="mt-1 whitespace-pre-line font-medium text-slate-900">{{ reservation.notes || 'Sin notas' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/40">
                        <h3 class="text-lg font-semibold text-slate-950">Cancha y horario</h3>
                        <dl class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <dt class="text-sm text-slate-500">Cancha</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ reservation.space?.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-slate-500">Estado</dt>
                                <dd class="mt-1">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClasses[reservation.status]">
                                        {{ reservation.status }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-slate-500">Inicio</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ formatDateTime(reservation.start_time) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-slate-500">Fin</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ formatDateTime(reservation.end_time) }}</dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="rounded-[1.75rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/15">
                        <h3 class="text-lg font-semibold">Historial de estado</h3>
                        <div class="mt-4 flex items-start gap-3 rounded-[1.25rem] bg-white/5 p-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-sm font-semibold">{{ currentStatusIcon }}</div>
                            <div>
                                <p class="font-medium capitalize text-white">{{ reservation.status }}</p>
                                <p class="text-sm text-slate-300">Estado actual de la reserva.</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/40">
                        <h3 class="text-lg font-semibold text-slate-950">Acciones</h3>
                        <div class="mt-4 flex flex-col gap-3">
                            <button v-if="reservation.status === 'pending'" type="button" class="rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white hover:bg-emerald-700" @click="submitAction('accept')">
                                Aprobar reserva
                            </button>
                            <button v-if="reservation.status === 'pending'" type="button" class="rounded-2xl bg-rose-600 px-4 py-3 text-sm font-medium text-white hover:bg-rose-700" @click="rejectOpen = true">
                                Rechazar reserva
                            </button>
                            <button v-if="reservation.status === 'confirmed'" type="button" class="rounded-2xl bg-orange-500 px-4 py-3 text-sm font-medium text-white hover:bg-orange-600" @click="submitAction('cancel')">
                                Cancelar reserva
                            </button>
                            <button v-if="reservation.status === 'confirmed'" type="button" class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-medium text-white hover:bg-black" @click="submitAction('finish')">
                                Marcar como finalizada
                            </button>
                        </div>
                    </section>
                </aside>
            </div>
        </div>

        <div v-if="rejectOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-[1.75rem] bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-semibold text-slate-950">Rechazar reserva</h3>
                <textarea v-model="rejectForm.rejection_reason" rows="4" class="mt-4 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                <p v-if="rejectForm.errors.rejection_reason" class="mt-2 text-sm text-rose-600">{{ rejectForm.errors.rejection_reason }}</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50" @click="rejectOpen = false">
                        Cerrar
                    </button>
                    <button type="button" class="rounded-2xl bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700" @click="submitReject">
                        Confirmar rechazo
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
