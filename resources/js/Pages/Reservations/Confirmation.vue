<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({
    layout: PublicLayout,
});

defineProps({
    reservation: { type: Object, required: true },
});

function formatDateTime(value, options) {
    const date = new Date(value.replace(' ', 'T'));
    return new Intl.DateTimeFormat('es-CO', options).format(date);
}
</script>

<template>
    <Head title="Reserva Recibida" />

    <section class="mx-auto flex min-h-[70vh] max-w-3xl items-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full rounded-[2rem] border border-slate-200 bg-white p-8 text-center shadow-2xl shadow-slate-200/50 sm:p-10">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="mt-6 text-3xl font-semibold text-slate-950">Reserva recibida</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">
                Recibiras un email en {{ reservation.user_email }} cuando tu reserva sea confirmada.
            </p>

            <div class="mt-8 rounded-[1.5rem] bg-slate-50 p-6 text-left">
                <dl class="space-y-4 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Reserva</dt>
                        <dd class="font-medium text-slate-900">{{ reservation.slug }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Cancha</dt>
                        <dd class="font-medium text-slate-900">{{ reservation.space.name }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Fecha</dt>
                        <dd class="font-medium text-slate-900">{{ formatDateTime(reservation.start_time, { dateStyle: 'full' }) }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Hora</dt>
                        <dd class="font-medium text-slate-900">
                            {{ formatDateTime(reservation.start_time, { timeStyle: 'short' }) }}
                            -
                            {{ formatDateTime(reservation.end_time, { timeStyle: 'short' }) }}
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Estado</dt>
                        <dd class="font-medium text-amber-700">Pendiente de confirmacion</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <Link href="/" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600">
                    Volver al inicio
                </Link>
                <Link :href="route('public.spaces.show', reservation.space.slug)" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Ver otra cancha
                </Link>
            </div>
        </div>
    </section>
</template>
