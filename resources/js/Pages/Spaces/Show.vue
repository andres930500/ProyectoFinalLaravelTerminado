<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({
    layout: PublicLayout,
});

const props = defineProps({
    space: { type: Object, required: true },
    nextSlots: { type: Array, default: () => [] },
    availabilities: { type: Array, default: () => [] },
    availabilityCheck: { type: Object, default: null },
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

function checkAvailability() {
    router.get(route('public.spaces.show', props.space.slug), {
        date: availabilityForm.date,
        time: availabilityForm.time,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="space.name" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="mb-8 flex items-center gap-2 text-sm text-slate-400">
            <Link href="/" class="transition hover:text-emerald-300">Inicio</Link>
            <span>/</span>
            <span class="text-white">{{ space.name }}</span>
        </nav>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-2xl shadow-slate-200/50">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-600">
                            {{ typeLabels[space.type] ?? space.type }}
                        </p>
                        <h1 class="mt-3 text-4xl font-semibold text-slate-950">{{ space.name }}</h1>
                        <div class="mt-5 flex flex-wrap gap-3 text-sm text-slate-600">
                            <span class="rounded-full bg-slate-100 px-4 py-2">{{ space.capacity }} jugadores</span>
                            <span class="rounded-full bg-emerald-50 px-4 py-2 text-emerald-800">{{ formatCurrency(space.price_per_hour) }} / hora</span>
                        </div>
                    </div>

                    <div v-if="space.images?.length" class="grid w-full gap-3 sm:w-56">
                        <div class="h-36 overflow-hidden rounded-[1.5rem] bg-slate-100">
                            <img :src="space.images[0]" :alt="space.name" class="h-full w-full object-cover">
                        </div>
                        <div v-if="space.images.length > 1" class="grid grid-cols-2 gap-3">
                            <div
                                v-for="(image, index) in space.images.slice(1, 3)"
                                :key="`${image}-${index}`"
                                class="h-20 overflow-hidden rounded-[1rem] bg-slate-100"
                            >
                                <img :src="image" :alt="`${space.name} ${index + 2}`" class="h-full w-full object-cover">
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex h-36 w-full items-center justify-center rounded-[1.5rem] bg-[linear-gradient(135deg,#047857,#111827)] text-white sm:w-48">
                        <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M3 12h18M6.5 6.5l11 11M17.5 6.5l-11 11" />
                        </svg>
                    </div>
                </div>

                <div class="mt-8 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Descripcion</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ space.description }}</p>

                        <div class="mt-8">
                            <h2 class="text-lg font-semibold text-slate-950">Horarios disponibles esta semana</h2>
                            <div class="mt-4 overflow-hidden rounded-[1.5rem] border border-slate-200">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Dia</th>
                                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Franjas</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 bg-white">
                                        <tr v-for="day in availabilities" :key="day.day_of_week">
                                            <td class="px-4 py-4 font-medium text-slate-900">{{ day.day_name }}</td>
                                            <td class="px-4 py-4 text-slate-600">
                                                <div class="flex flex-wrap gap-2">
                                                    <span
                                                        v-for="slot in day.slots"
                                                        :key="slot.id"
                                                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700"
                                                    >
                                                        {{ slot.start_time }} - {{ slot.end_time }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] bg-slate-950 p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-slate-300">Consulta puntual</div>
                                <div class="mt-1 text-2xl font-semibold">Fecha y hora</div>
                            </div>
                            <div class="rounded-2xl bg-white/10 px-3 py-2 text-sm text-slate-200">Backend validado</div>
                        </div>

                        <div class="mt-5 grid gap-4">
                            <input v-model="availabilityForm.date" type="date" class="rounded-2xl border-white/10 bg-white/10 text-sm text-white shadow-none focus:border-emerald-400 focus:ring-emerald-400">
                            <input v-model="availabilityForm.time" type="time" class="rounded-2xl border-white/10 bg-white/10 text-sm text-white shadow-none focus:border-emerald-400 focus:ring-emerald-400">
                            <button type="button" class="rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400" @click="checkAvailability">
                                Consultar disponibilidad
                            </button>
                        </div>

                        <div v-if="availabilityCheck" class="mt-5 rounded-2xl px-4 py-4 text-sm" :class="availabilityCheck.available ? 'bg-emerald-500/15 text-emerald-100 ring-1 ring-emerald-400/30' : 'bg-rose-500/15 text-rose-100 ring-1 ring-rose-400/30'">
                            <div class="font-medium">{{ availabilityCheck.message }}</div>
                            <button
                                v-if="availabilityCheck.available"
                                type="button"
                                class="mt-4 rounded-2xl bg-white px-4 py-2 font-semibold text-slate-950 transition hover:bg-emerald-50"
                                @click="reserveSlot(availabilityCheck)"
                            >
                                Reservar este horario
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-lg font-semibold text-slate-950">Proximos horarios disponibles</h2>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ nextSlots.length }} slots</span>
                    </div>
                    <div v-if="nextSlots.length" class="mt-4 flex flex-col gap-3">
                        <button
                            v-for="slot in nextSlots"
                            :key="slot.start"
                            type="button"
                            class="rounded-[1.25rem] border border-emerald-200 bg-emerald-50 px-4 py-4 text-left text-sm font-medium text-emerald-950 transition hover:border-emerald-300 hover:bg-emerald-100"
                            @click="reserveSlot(slot)"
                        >
                            {{ slot.formatted }}
                        </button>
                    </div>
                    <p v-else class="mt-4 text-sm text-slate-600">
                        No encontramos horarios libres en este momento.
                    </p>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50">
                    <h2 class="text-lg font-semibold text-slate-950">Reglas de uso</h2>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600">
                        <li v-for="(rule, index) in ruleItems(space.rules)" :key="index" class="flex gap-3">
                            <span class="mt-2 h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span class="leading-6">{{ rule }}</span>
                        </li>
                    </ul>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50">
                    <h2 class="text-lg font-semibold text-slate-950">Disponibilidad semanal</h2>
                    <div class="mt-4 space-y-3">
                        <div
                            v-for="day in availabilities"
                            :key="`weekly-${day.day_of_week}`"
                            class="rounded-[1.25rem] bg-slate-50 px-4 py-4"
                        >
                            <div class="text-sm font-semibold text-slate-900">{{ day.day_name }}</div>
                            <div class="mt-1 text-sm leading-6 text-slate-600">
                                {{ day.slots.map((slot) => `${slot.start_time} - ${slot.end_time}`).join(' | ') }}
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</template>
