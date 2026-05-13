<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({
    layout: PublicLayout,
});

const props = defineProps({
    spaces: { type: Array, default: () => [] },
    types: { type: Array, default: () => [] },
    selectedType: { type: String, default: null },
});

const typeLabels = {
    cancha_cesped: 'Cesped',
    cancha_sintetica: 'Sintetica',
    cancha_futbol_sala: 'Futbol Sala',
    cancha_futbol_playa: 'Futbol Playa',
};

const filters = computed(() => [
    { value: null, label: 'Todas' },
    ...props.types.map((type) => ({
        value: type,
        label: typeLabels[type] ?? type,
    })),
]);

const features = [
    {
        title: 'Confirmacion inmediata',
        description: 'Recibe respuesta mas rapida y entra al flujo correcto sin perder tiempo.',
    },
    {
        title: 'Pago seguro',
        description: 'Procesos mas claros para operar tus reservas con confianza.',
    },
    {
        title: 'Mejores precios',
        description: 'Compara canchas y elige la opcion ideal segun formato y presupuesto.',
    },
];

function applyFilter(type) {
    router.get('/', type ? { type } : {}, {
        preserveState: true,
        preserveScroll: true,
    });
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

function typeBadgeClass(type) {
    return {
        cancha_cesped: 'text-lime-300',
        cancha_sintetica: 'text-emerald-300',
        cancha_futbol_sala: 'text-sky-300',
        cancha_futbol_playa: 'text-amber-300',
    }[type] ?? 'text-emerald-300';
}
</script>

<template>
    <Head title="Canchas Disponibles" />

    <section class="pb-0 pt-0">
        <div class="relative min-h-[460px] overflow-hidden bg-[#0d1712] shadow-[0_28px_90px_rgba(0,0,0,0.2)]">
            <div
                class="absolute inset-0 bg-cover"
                style="background-image: url('/images/banners/FondoBanner.png'); background-position: center 32%;"
            ></div>
            <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(11,34,24,0.68)_0%,rgba(11,34,24,0.35)_48%,rgba(7,46,26,0.18)_100%)]"></div>

            <div class="relative mx-auto flex min-h-[460px] max-w-7xl flex-col justify-between px-4 py-10 sm:px-6 lg:px-8 lg:py-8">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                    <div class="pt-4">
                        <p class="text-[11px] font-medium uppercase tracking-[0.4em] text-green-400">ReservaCancha</p>
                        <h1 class="mt-4 max-w-3xl text-4xl font-black uppercase leading-[0.95] tracking-tight text-white md:text-5xl">
                            Reserva tu cancha. Juega al instante.
                        </h1>
                        <p class="mt-4 max-w-md text-sm leading-6 text-gray-300">
                            Encuentra la cancha perfecta para tu partido. Desde Futbol 5 hasta ligas completas,
                            reserva en segundos con disponibilidad real.
                        </p>
                        <div class="mt-6 flex flex-wrap gap-2">
                            <button type="button" class="rounded-lg bg-[#00C853] px-4 py-2 text-sm font-semibold text-black transition hover:bg-[#00b84c]">
                                Consulta disponibilidad real
                            </button>
                            <button type="button" class="rounded-lg border border-green-500 bg-transparent px-4 py-2 text-sm text-green-400 transition hover:bg-green-500/10">
                                Compara por tipo de cancha
                            </button>
                            <button type="button" class="rounded-lg border border-gray-600 bg-transparent px-4 py-2 text-sm text-gray-300 transition hover:bg-white/5">
                                Asegura tu horario
                            </button>
                        </div>
                    </div>

                    <div class="w-full max-w-[22rem] rounded-[1.5rem] border border-white/20 bg-emerald-950/20 p-5 backdrop-blur-md">
                        <div class="grid gap-3">
                            <div class="rounded-xl border border-white/15 bg-white/20 p-4">
                                <div class="text-xs text-emerald-50">Canchas visibles</div>
                                <div class="mt-2 text-3xl font-bold text-white">{{ spaces.length }}</div>
                                <div class="mt-1 text-xs text-emerald-100/80">Disponibles para reservar</div>
                            </div>
                            <div class="rounded-xl border border-emerald-200/30 bg-[#00C853]/85 p-4">
                                <div class="text-xs text-emerald-950/80">Tipos en el sistema</div>
                                <div class="mt-2 text-3xl font-bold text-slate-950">{{ types.length }}</div>
                                <div class="mt-1 text-xs text-emerald-950/80">Configurados en el sistema</div>
                            </div>
                        </div>
                        <div class="mt-3 rounded-xl border border-white/15 bg-white/12 px-4 py-4 text-xs text-emerald-50">
                            Elige una cancha, revisa sus reglas y salta directo al siguiente horario libre.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto -mt-14 max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
        <div class="rounded-[1.9rem] border border-white/20 bg-white/18 p-5 shadow-2xl shadow-emerald-950/10 backdrop-blur-md">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                <div>
                    <h2 class="font-semibold text-white drop-shadow-sm">Explora por tipo de cancha</h2>
                    <p class="mt-1 text-xs font-medium text-white/85">Filtra segun tus preferencias y horarios de juego.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="filter in filters"
                        :key="filter.label"
                        type="button"
                        class="rounded-full px-4 py-1.5 text-xs transition"
                        :class="selectedType === filter.value
                            ? 'bg-[#d4fc79] text-black shadow-[0_0_15px_rgba(212,252,121,0.45)]'
                            : 'border border-emerald-200 bg-white/75 text-slate-600 hover:border-[#00C853] hover:text-emerald-700'"
                        @click="applyFilter(filter.value)"
                    >
                        {{ filter.label }}
                    </button>
                </div>
            </div>
            <div v-if="spaces.length" class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                <article
                    v-for="space in spaces"
                    :key="space.id"
                    class="flex h-full flex-col overflow-hidden rounded-2xl border border-emerald-200 bg-white/95 shadow-lg shadow-emerald-100/60 transition-all hover:-translate-y-1 hover:border-[#00C853] hover:shadow-xl"
                >
                    <div class="relative">
                        <img
                            v-if="space.image"
                            :src="space.image"
                            :alt="space.name"
                            class="h-44 w-full object-cover"
                        >
                        <div v-else class="flex h-44 w-full items-center justify-center bg-[linear-gradient(135deg,#12b76a,#123524)]">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full border border-white/25 bg-white/15 text-white">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M3 12h18M6.5 6.5l11 11M17.5 6.5l-11 11" />
                                </svg>
                            </div>
                        </div>
                        <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-0.5 text-xs backdrop-blur" :class="typeBadgeClass(space.type)">
                            {{ typeLabels[space.type] ?? space.type }}
                        </span>
                        <span class="absolute right-3 top-3 rounded-full bg-white/90 px-2.5 py-0.5 text-sm font-bold text-emerald-700 backdrop-blur">
                            {{ formatCurrency(space.price_per_hour) }}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="text-base font-semibold text-slate-950">{{ space.name }}</h3>

                        <div class="mt-2 flex items-center gap-1 text-xs text-slate-500">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span>{{ space.capacity }} jugadores</span>
                        </div>

                        <div class="mt-3 min-h-[104px] rounded-xl border border-emerald-100 bg-emerald-50 p-3">
                            <p class="line-clamp-3 text-xs leading-5 text-slate-600">
                                {{ space.description || 'Cancha lista para reservas con horarios visibles y gestion sencilla.' }}
                            </p>
                        </div>

                        <Link
                            :href="route('public.spaces.show', space.slug)"
                            class="mt-auto inline-flex w-full items-center justify-center rounded-xl bg-[#00C853] px-4 py-2.5 text-sm font-semibold text-black transition hover:bg-[#00a846]"
                        >
                            Ver disponibilidad
                        </Link>
                    </div>
                </article>
            </div>

            <div v-else class="mt-5 rounded-2xl border border-dashed border-emerald-300 bg-white/90 px-6 py-12 text-center text-slate-500">
                No hay canchas disponibles con ese filtro
            </div>
        </div>

        <div class="mt-10 rounded-[2rem] border border-emerald-100 bg-white p-6 shadow-xl shadow-emerald-100/40 md:p-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Por que elegirnos</h2>
                    <p class="mt-1 text-sm text-slate-500">Una experiencia mas clara, rapida y preparada para reservas reales.</p>
                </div>
            </div>
            <div class="mt-8 grid gap-4 lg:grid-cols-3">
                <article
                    v-for="feature in features"
                    :key="feature.title"
                    class="rounded-[1.5rem] border border-emerald-100 bg-emerald-50/70 p-5 shadow-sm"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500/15 text-green-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-sm font-semibold text-slate-950">{{ feature.title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ feature.description }}</p>
                </article>
            </div>
        </div>
    </section>
</template>
