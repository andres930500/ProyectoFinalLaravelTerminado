<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({
    layout: PublicLayout,
});

const props = defineProps({
    space: { type: Object, required: true },
    nextSlots: { type: Array, default: () => [] },
    dailySlots: { type: Array, default: () => [] },
    availabilities: { type: Array, default: () => [] },
    location: { type: Object, default: () => ({}) },
    selectedDate: { type: String, default: '' },
    selectedTime: { type: String, default: '' },
});

const typeLabels = {
    cancha_cesped: 'Cesped',
    cancha_sintetica: 'Sintetica',
    cancha_futbol_sala: 'Futbol Sala',
    cancha_futbol_playa: 'Futbol Playa',
};

const availabilityForm = reactive({
    date: props.selectedDate,
    time: props.selectedTime,
});
const detailsModalOpen = ref(false);

const activeSelectedSlot = computed(() => props.dailySlots.find((slot) => slot.time === availabilityForm.time) ?? null);
const availableSlotsCount = computed(() => props.dailySlots.filter((slot) => slot.is_available).length);
const unavailableSlotsCount = computed(() => props.dailySlots.filter((slot) => !slot.is_available).length);
const formattedSelectedDate = computed(() => {
    if (!availabilityForm.date) return 'Selecciona una fecha';

    return new Intl.DateTimeFormat('es-CO', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    }).format(new Date(`${availabilityForm.date}T00:00:00`));
});
const dailySlotGroups = computed(() => {
    const groups = [
        { key: 'morning', label: 'Manana', range: [0, 11], slots: [] },
        { key: 'afternoon', label: 'Tarde', range: [12, 17], slots: [] },
        { key: 'night', label: 'Noche', range: [18, 23], slots: [] },
    ];

    props.dailySlots.forEach((slot) => {
        const hour = Number(slot.time.split(':')[0] ?? 0);
        const group = groups.find((item) => hour >= item.range[0] && hour <= item.range[1]);

        if (group) {
            group.slots.push(slot);
        }
    });

    return groups.filter((group) => group.slots.length);
});
const selectedSlotPrice = computed(() => formatCurrency(spacePricePerHour()));
const selectedSlotState = computed(() => {
    if (!activeSelectedSlot.value) {
        return {
            title: 'Selecciona un horario',
            description: 'Toca un bloque para ver el detalle y continuar con la reserva.',
        };
    }

    if (activeSelectedSlot.value.is_available) {
        return {
            title: activeSelectedSlot.value.label,
            description: 'Bloque disponible para continuar con la reserva.',
        };
    }

    return {
        title: activeSelectedSlot.value.label,
        description: 'Ese horario no esta disponible. Elige otro bloque en verde.',
    };
});

function spacePricePerHour() {
    return Number(props.space?.price_per_hour ?? 0);
}

function formatCurrency(value) {
    const amount = Number(value ?? 0);
    if (amount <= 0) return 'Gratis';

    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0,
    }).format(amount);
}

function ruleItems(rules) {
    return (rules ?? '')
        .split('.')
        .map((item) => item.trim())
        .filter(Boolean);
}

function reserveSlot(slot) {
    router.get(route('public.reservations.create'), {
        space: props.space.slug,
        start: slot.start,
    });
}

function changeDate() {
    router.get(route('public.spaces.show', props.space.slug), {
        date: availabilityForm.date,
        time: availabilityForm.time,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function selectSlot(slot) {
    availabilityForm.time = slot.time;
}

function openDetailsModal() {
    detailsModalOpen.value = true;
}

function closeDetailsModal() {
    detailsModalOpen.value = false;
}

function slotCardClasses(slot) {
    if (slot.is_available) {
        return 'border-emerald-200 bg-emerald-50 text-emerald-950 hover:border-emerald-400 hover:bg-emerald-100';
    }

    return 'border-rose-200 bg-rose-50 text-rose-900 hover:border-rose-300';
}
</script>

<template>
    <Head :title="space.name" />

    <section class="mx-auto max-w-7xl px-4 py-8 pb-28 sm:px-6 lg:px-8 lg:pb-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
            <Link href="/" class="transition hover:text-emerald-600">Inicio</Link>
            <span>/</span>
            <span class="font-medium text-slate-900">{{ space.name }}</span>
        </nav>

        <div class="overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-[0_30px_80px_rgba(15,23,42,0.08)]">
            <div class="flex min-h-[calc(100vh-2rem)] flex-col bg-[linear-gradient(180deg,#f8fdf9_0%,#eef7f1_100%)]">
                <div class="border-b border-emerald-100 px-6 py-6 sm:px-8">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="max-w-3xl">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full bg-emerald-100 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">
                                    {{ typeLabels[space.type] ?? space.type }}
                                </span>
                                <span class="rounded-full bg-slate-950 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-white">
                                    {{ formatCurrency(space.price_per_hour) }} / hora
                                </span>
                            </div>
                            <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                                Escoge tu horario sin perder tiempo.
                            </h1>
                            <p class="mt-3 text-sm leading-6 text-slate-500 sm:text-base">
                                Cambia la fecha, revisa todos los bloques de 1 hora y continua con el horario que mas te convenga.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold capitalize text-emerald-700 ring-1 ring-emerald-100">
                                {{ formattedSelectedDate }}
                            </div>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700"
                                @click="openDetailsModal"
                            >
                                Ver detalles de la cancha
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex-1 px-6 py-6 sm:px-8">
                    <div class="grid gap-6 xl:grid-cols-[240px_minmax(0,1fr)]">
                        <div class="rounded-[1.5rem] bg-slate-950 p-5 text-white">
                            <label class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-300">Fecha de juego</label>
                            <input
                                v-model="availabilityForm.date"
                                type="date"
                                class="mt-3 w-full rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm text-white shadow-none focus:border-emerald-400 focus:ring-emerald-400"
                                @change="changeDate"
                            >
                            <div class="mt-5 rounded-[1.25rem] bg-white/8 px-4 py-4">
                                <div class="text-[11px] uppercase tracking-[0.18em] text-slate-300">Resumen del dia</div>
                                <div class="mt-4 space-y-3">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-slate-300">Disponibles</span>
                                        <span class="rounded-full bg-emerald-500/20 px-3 py-1 font-semibold text-emerald-300">{{ availableSlotsCount }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-slate-300">No disponibles</span>
                                        <span class="rounded-full bg-rose-500/20 px-3 py-1 font-semibold text-rose-300">{{ unavailableSlotsCount }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-slate-300">Seleccion</span>
                                        <span class="rounded-full bg-white/10 px-3 py-1 font-semibold text-white">{{ activeSelectedSlot?.time ?? '--:--' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.5rem] border border-slate-200 bg-white p-4 sm:p-5">
                            <div v-if="dailySlotGroups.length" class="space-y-6">
                                <section v-for="group in dailySlotGroups" :key="group.key">
                                    <div class="mb-3 flex items-center justify-between">
                                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">{{ group.label }}</h3>
                                        <span class="text-xs text-slate-400">{{ group.slots.length }} horarios</span>
                                    </div>
                                    <div class="grid grid-cols-[repeat(auto-fill,minmax(80px,1fr))] gap-3">
                                        <button
                                            v-for="slot in group.slots"
                                            :key="slot.start"
                                            type="button"
                                            class="min-h-12 min-w-20 rounded-[1.1rem] border px-3 py-3 text-center text-sm leading-5 transition"
                                            :class="[
                                                slotCardClasses(slot),
                                                availabilityForm.time === slot.time
                                                    ? 'border-slate-950 bg-[#dff7e8] text-slate-950 ring-2 ring-emerald-400'
                                                    : ''
                                            ]"
                                            @click="selectSlot(slot)"
                                        >
                                            <div class="font-semibold">{{ slot.time }}</div>
                                        </button>
                                    </div>
                                </section>
                            </div>
                            <div v-else class="rounded-[1.25rem] border border-dashed border-slate-200 bg-slate-50 px-5 py-10 text-center text-sm text-slate-500">
                                No hay bloques configurados para esta fecha.
                            </div>

                            <div v-if="activeSelectedSlot" class="mt-6 rounded-[1.5rem] px-5 py-5 text-sm" :class="activeSelectedSlot.is_available ? 'bg-emerald-50 text-emerald-950 ring-1 ring-emerald-200' : 'bg-rose-50 text-rose-900 ring-1 ring-rose-200'">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em]" :class="activeSelectedSlot.is_available ? 'text-emerald-700' : 'text-rose-700'">
                                    Horario seleccionado
                                </div>
                                <div class="mt-2 text-xl font-semibold">{{ activeSelectedSlot.label }}</div>
                                <div class="mt-2 leading-6">{{ activeSelectedSlot.message }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hidden border-t border-emerald-100 bg-white/95 px-6 py-4 backdrop-blur lg:sticky lg:bottom-0 lg:block">
                    <div class="flex items-center justify-between gap-6">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Resumen de precio</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-950">{{ selectedSlotPrice }}</div>
                            <div class="mt-1 truncate text-sm text-slate-500">{{ selectedSlotState.title }} | {{ selectedSlotState.description }}</div>
                        </div>
                        <button
                            v-if="activeSelectedSlot?.is_available"
                            type="button"
                            class="inline-flex shrink-0 items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600"
                            @click="reserveSlot(activeSelectedSlot)"
                        >
                            Continuar con esta reserva
                        </button>
                        <div class="flex shrink-0 items-center gap-3">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700"
                                @click="openDetailsModal"
                            >
                                Detalles de la cancha
                            </button>
                            <span v-if="!activeSelectedSlot?.is_available" class="rounded-2xl bg-slate-100 px-4 py-3 text-sm font-medium text-slate-600">
                                Elige un horario disponible
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section
            v-if="location?.address && location?.embedUrl"
            class="mt-8 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40"
        >
            <div class="grid lg:grid-cols-[360px_minmax(0,1fr)]">
                <div class="flex flex-col justify-between gap-5 bg-slate-950 p-6 text-white">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-300">Ubicacion</div>
                        <h2 class="mt-3 text-2xl font-semibold text-white">Encuentra la cancha sin perderte</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-300">
                            Revisa la direccion exacta antes de reservar y abre el mapa si necesitas la ruta.
                        </p>
                    </div>

                    <div class="rounded-[1.5rem] bg-white/10 p-5">
                        <div class="text-[11px] uppercase tracking-[0.18em] text-slate-300">Direccion registrada</div>
                        <p class="mt-3 text-sm leading-6 text-white">{{ location.address }}</p>
                        <a
                            v-if="location.mapsUrl"
                            :href="location.mapsUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-5 inline-flex items-center justify-center rounded-2xl bg-[#00C853] px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-[#00b84c]"
                        >
                            Abrir en Google Maps
                        </a>
                    </div>
                </div>

                <div class="min-h-[360px] bg-slate-100">
                    <iframe
                        :src="location.embedUrl"
                        class="h-full min-h-[360px] w-full border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </section>

        <div class="mt-8 grid gap-8 lg:grid-cols-[0.95fr_1.05fr]">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/40">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-950">Proximos horarios disponibles</h2>
                        <p class="mt-1 text-sm text-slate-500">Si quieres reservar rapido, aqui tienes los siguientes bloques libres.</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ nextSlots.length }} opciones</span>
                </div>
                <div v-if="nextSlots.length" class="mt-5 grid gap-3">
                    <button
                        v-for="slot in nextSlots"
                        :key="slot.start"
                        type="button"
                        class="rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-4 py-4 text-left text-sm font-medium text-emerald-950 transition hover:border-emerald-300 hover:bg-emerald-100"
                        @click="reserveSlot(slot)"
                    >
                        {{ slot.formatted }}
                    </button>
                </div>
                <p v-else class="mt-4 text-sm text-slate-600">
                    No encontramos horarios libres en este momento.
                </p>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/40">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-950">Antes de reservar</h2>
                        <p class="mt-1 text-sm text-slate-500">Todo lo importante en una sola mirada.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ availabilities.length }} dias configurados</span>
                </div>

                <div class="mt-5 grid gap-5 lg:grid-cols-2">
                    <div class="rounded-[1.5rem] bg-slate-50 p-5">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Reglas de uso</h3>
                        <ul class="mt-4 space-y-3 text-sm text-slate-600">
                            <li v-for="(rule, index) in ruleItems(space.rules)" :key="index" class="flex gap-3">
                                <span class="mt-2 h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="leading-6">{{ rule }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-[1.5rem] bg-slate-50 p-5">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Horario semanal</h3>
                        <div class="mt-4 space-y-3">
                            <div
                                v-for="day in availabilities"
                                :key="`weekly-${day.day_of_week}`"
                                class="rounded-[1.1rem] bg-white px-4 py-4 ring-1 ring-slate-200"
                            >
                                <div class="text-sm font-semibold text-slate-900">{{ day.day_name }}</div>
                                <div class="mt-1 text-sm leading-6 text-slate-600">
                                    {{ day.slots.map((slot) => `${slot.start_time} - ${slot.end_time}`).join(' | ') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-emerald-100 bg-white/95 px-4 py-3 shadow-[0_-12px_30px_rgba(15,23,42,0.08)] backdrop-blur lg:hidden">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Resumen</div>
                    <div class="mt-1 text-lg font-semibold text-slate-950">{{ selectedSlotPrice }}</div>
                    <div class="truncate text-xs text-slate-500">{{ selectedSlotState.title }}</div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700"
                        @click="openDetailsModal"
                    >
                        Detalles
                    </button>
                    <button
                        v-if="activeSelectedSlot?.is_available"
                        type="button"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600"
                        @click="reserveSlot(activeSelectedSlot)"
                    >
                        Reservar
                    </button>
                    <span v-else class="rounded-2xl bg-slate-100 px-4 py-3 text-sm font-medium text-slate-600">
                        Elige un horario
                    </span>
                </div>
            </div>
        </div>

        <button
            type="button"
            class="fixed bottom-24 left-4 z-30 inline-flex items-center rounded-full bg-[#1a3a2a] px-4 py-3 text-sm font-semibold text-white shadow-[0_18px_35px_rgba(15,23,42,0.18)] transition hover:bg-[#214a35] lg:bottom-6 lg:left-6"
            @click="openDetailsModal"
        >
            Ver detalles de la cancha
        </button>

        <div v-if="detailsModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm" @click.self="closeDetailsModal">
            <div class="w-full max-w-3xl overflow-hidden rounded-[2rem] bg-white shadow-[0_30px_80px_rgba(15,23,42,0.22)]">
                <div class="bg-[#1a3a2a] px-6 py-6 text-white sm:px-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-100">
                                    {{ typeLabels[space.type] ?? space.type }}
                                </span>
                                <span class="rounded-full bg-[#9FE870] px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-[#14311f]">
                                    {{ formatCurrency(space.price_per_hour) }} / hora
                                </span>
                            </div>
                            <h2 class="mt-4 text-3xl font-black tracking-tight">{{ space.name }}</h2>
                            <p class="mt-3 max-w-2xl text-sm leading-6 text-emerald-50/80">{{ space.description }}</p>
                        </div>
                        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20" @click="closeDetailsModal">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[70vh] overflow-y-auto px-6 py-6 sm:px-8">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Capacidad</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-950">{{ space.capacity }}</div>
                            <div class="mt-1 text-sm text-slate-500">jugadores</div>
                        </div>
                        <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Disponibles</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-950">{{ availableSlotsCount }}</div>
                            <div class="mt-1 text-sm text-slate-500">horarios hoy</div>
                        </div>
                        <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Seleccion</div>
                            <div class="mt-2 text-lg font-semibold text-slate-950">{{ activeSelectedSlot?.label ?? 'Sin horario' }}</div>
                            <div class="mt-1 text-sm text-slate-500">bloque actual</div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Como reservar</div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[1.4rem] border border-slate-200 bg-white p-4">
                                <div class="text-xs font-bold text-emerald-700">Paso 1</div>
                                <div class="mt-2 text-base font-semibold text-slate-950">Elige la fecha</div>
                                <div class="mt-1 text-sm leading-6 text-slate-600">Selecciona el dia en el calendario y revisa cuantas horas quedan libres.</div>
                            </div>
                            <div class="rounded-[1.4rem] border border-slate-200 bg-white p-4">
                                <div class="text-xs font-bold text-emerald-700">Paso 2</div>
                                <div class="mt-2 text-base font-semibold text-slate-950">Toca un bloque horario</div>
                                <div class="mt-1 text-sm leading-6 text-slate-600">Los bloques verdes se pueden reservar. Los rojos ya estan ocupados o bloqueados.</div>
                            </div>
                            <div class="rounded-[1.4rem] border border-slate-200 bg-white p-4">
                                <div class="text-xs font-bold text-emerald-700">Paso 3</div>
                                <div class="mt-2 text-base font-semibold text-slate-950">Confirma la solicitud</div>
                                <div class="mt-1 text-sm leading-6 text-slate-600">Continua con el horario elegido y completa tus datos para dejar la reserva enviada.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Reglas de uso</div>
                            <ul class="mt-4 space-y-3 text-sm text-slate-600">
                                <li v-for="(rule, index) in ruleItems(space.rules)" :key="index" class="flex gap-3">
                                    <span class="mt-2 h-2 w-2 rounded-full bg-emerald-500"></span>
                                    <span class="leading-6">{{ rule }}</span>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Horario semanal</div>
                            <div class="mt-4 space-y-3">
                                <div
                                    v-for="day in availabilities"
                                    :key="`modal-${day.day_of_week}`"
                                    class="rounded-[1.1rem] border border-slate-200 bg-slate-50 px-4 py-4"
                                >
                                    <div class="text-sm font-semibold text-slate-900">{{ day.day_name }}</div>
                                    <div class="mt-1 text-sm leading-6 text-slate-600">
                                        {{ day.slots.map((slot) => `${slot.start_time} - ${slot.end_time}`).join(' | ') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
