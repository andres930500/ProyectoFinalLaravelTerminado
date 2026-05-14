<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import TextInput from '@/Components/TextInput.vue';

defineOptions({
    layout: PublicLayout,
});

const props = defineProps({
    space: { type: Object, required: true },
    startTime: { type: String, required: true },
    endTime: { type: String, required: true },
    totalPrice: { type: Number, required: true },
});

const form = useForm({
    space_id: props.space.id,
    start_time: props.startTime,
    end_time: props.endTime,
    user_name: '',
    user_email: '',
    user_phone: '',
    notes: '',
});

const durationInMinutes = computed(() => {
    const start = new Date(props.startTime.replace(' ', 'T'));
    const end = new Date(props.endTime.replace(' ', 'T'));

    return Math.round((end.getTime() - start.getTime()) / 60000);
});

function formatDateTime(value, options) {
    const date = new Date(value.replace(' ', 'T'));
    return new Intl.DateTimeFormat('es-CO', options).format(date);
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));
}

function submit() {
    form.post(route('public.reservations.store'));
}
</script>

<template>
    <Head title="Nueva Reserva" />

    <section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,0.95fr)_minmax(320px,0.8fr)]">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-2xl shadow-slate-200/50">
                <div class="mb-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-600">Reserva</p>
                    <h1 class="mt-3 text-4xl font-semibold text-slate-950">Confirma tu horario</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                        Completa tus datos y deja la solicitud lista para aprobacion administrativa.
                    </p>
                </div>

                <form class="space-y-5" @submit.prevent="submit">
                    <input v-model="form.space_id" type="hidden">
                    <input v-model="form.start_time" type="hidden">
                    <input v-model="form.end_time" type="hidden">

                    <div>
                        <InputLabel for="user_name" value="Nombre completo" />
                        <TextInput id="user_name" v-model="form.user_name" type="text" class="mt-1 block w-full" autocomplete="name" placeholder="Ej: Juan David Perez" />
                        <InputError class="mt-2" :message="form.errors.user_name" />
                    </div>

                    <div>
                        <InputLabel for="user_email" value="Email" />
                        <TextInput id="user_email" v-model="form.user_email" type="email" class="mt-1 block w-full" autocomplete="email" placeholder="Ej: juan@example.com" />
                        <InputError class="mt-2" :message="form.errors.user_email" />
                    </div>

                    <div>
                        <InputLabel for="user_phone" value="Telefono" />
                        <TextInput id="user_phone" v-model="form.user_phone" type="text" class="mt-1 block w-full" autocomplete="tel" placeholder="Ej: 3001234567" />
                        <InputError class="mt-2" :message="form.errors.user_phone" />
                    </div>

                    <div>
                        <InputLabel for="notes" value="Notas u observaciones" />
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            class="mt-1 block w-full rounded-2xl border-slate-300 bg-white text-sm text-slate-900 placeholder:text-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Ej: Necesitamos dos balones y preferimos iluminación completa"
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.notes" />
                    </div>

                    <InputError class="mt-2" :message="form.errors.space_id || form.errors.start_time || form.errors.end_time || form.errors.duration || form.errors.slot_alignment || form.errors.availability || form.errors.collision || form.errors.blocked_slot" />

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-70"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Enviando solicitud...' : 'Confirmar Reserva' }}
                        </button>

                        <Link
                            :href="route('public.spaces.show', space.slug)"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                        >
                            Volver a la cancha
                        </Link>
                    </div>
                </form>
            </div>

            <aside class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/20">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">Resumen de la reserva</h2>
                        <p class="mt-1 text-sm text-slate-300">Verifica antes de enviar la solicitud.</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-3 py-2 text-sm text-slate-200">{{ durationInMinutes }} min</div>
                </div>
                <dl class="mt-6 space-y-4 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-400">Cancha</dt>
                        <dd class="text-right font-medium text-white">{{ space.name }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-400">Fecha</dt>
                        <dd class="text-right font-medium text-white">
                            {{ formatDateTime(startTime, { dateStyle: 'full' }) }}
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-400">Hora inicio</dt>
                        <dd class="font-medium text-white">{{ formatDateTime(startTime, { timeStyle: 'short' }) }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-400">Hora fin</dt>
                        <dd class="font-medium text-white">{{ formatDateTime(endTime, { timeStyle: 'short' }) }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-400">Duracion</dt>
                        <dd class="font-medium text-white">{{ durationInMinutes }} minutos</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 border-t border-white/10 pt-4">
                        <dt class="text-slate-400">Precio total</dt>
                        <dd class="text-lg font-semibold text-emerald-300">{{ formatCurrency(totalPrice) }}</dd>
                    </div>
                </dl>
                <div class="mt-6 rounded-[1.5rem] bg-white/5 px-4 py-4 text-sm leading-6 text-slate-300">
                    Recibiras una confirmacion cuando el administrador apruebe el horario.
                </div>
            </aside>
        </div>
    </section>
</template>
