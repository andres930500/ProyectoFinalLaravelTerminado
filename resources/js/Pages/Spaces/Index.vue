<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
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

const availableSpacesToday = computed(() => props.spaces.length);

const features = [
    {
        title: 'Confirmacion inmediata',
        description: 'Recibe respuesta mas rapida y entra al flujo correcto sin perder tiempo.',
        image: '/images/ui/why-confirmacion.jpg',
    },
    {
        title: 'Pago seguro',
        description: 'Procesos mas claros para operar tus reservas con confianza.',
        image: '/images/ui/why-pago-seguro.jpg',
    },
    {
        title: 'Mejores precios',
        description: 'Compara canchas y elige la opcion ideal segun formato y presupuesto.',
        image: '/images/ui/why-mejores-precios.jpg',
    },
];

const activeSlides = ref({});
const visibleCards = ref(3);
const cardStartIndex = ref(0);
let carouselTimer = null;

const normalizedSpaces = computed(() => props.spaces.map((space) => ({
    ...space,
    gallery: resolveGallery(space),
})));

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

function updateVisibleCards() {
    if (typeof window === 'undefined') return;

    if (window.innerWidth >= 1280) {
        visibleCards.value = 3;
        return;
    }

    if (window.innerWidth >= 768) {
        visibleCards.value = 2;
        return;
    }

    visibleCards.value = 1;
}

function maxCardStart() {
    return Math.max(normalizedSpaces.value.length - visibleCards.value, 0);
}

function syncCardViewport() {
    cardStartIndex.value = Math.min(cardStartIndex.value, maxCardStart());
}

function showNextCards() {
    cardStartIndex.value = Math.min(cardStartIndex.value + 1, maxCardStart());
}

function showPreviousCards() {
    cardStartIndex.value = Math.max(cardStartIndex.value - 1, 0);
}

function resolveGallery(space) {
    if (Array.isArray(space.images) && space.images.length) {
        return space.images;
    }

    return space.image ? [space.image] : [];
}

function currentSlide(space) {
    return activeSlides.value[space.id] ?? 0;
}

function setSlide(spaceId, index) {
    activeSlides.value = {
        ...activeSlides.value,
        [spaceId]: index,
    };
}

function syncSlideState() {
    const nextState = {};

    normalizedSpaces.value.forEach((space) => {
        const maxIndex = Math.max(space.gallery.length - 1, 0);
        nextState[space.id] = Math.min(activeSlides.value[space.id] ?? 0, maxIndex);
    });

    activeSlides.value = nextState;
}

function advanceSlides() {
    if (!normalizedSpaces.value.length) return;

    const nextState = { ...activeSlides.value };

    normalizedSpaces.value.forEach((space) => {
        if (space.gallery.length <= 1) return;

        const currentIndex = nextState[space.id] ?? 0;
        nextState[space.id] = (currentIndex + 1) % space.gallery.length;
    });

    activeSlides.value = nextState;
}

function startCarousel() {
    stopCarousel();

    carouselTimer = window.setInterval(() => {
        advanceSlides();
    }, 3500);
}

function stopCarousel() {
    if (carouselTimer) {
        window.clearInterval(carouselTimer);
        carouselTimer = null;
    }
}

watch(normalizedSpaces, () => {
    syncSlideState();
    syncCardViewport();
}, { immediate: true });

onMounted(() => {
    updateVisibleCards();
    window.addEventListener('resize', updateVisibleCards);
    startCarousel();
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateVisibleCards);
    stopCarousel();
});

watch(visibleCards, () => {
    syncCardViewport();
});
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
                                <div class="text-xs text-emerald-950/80">Espacios listos</div>
                                <div class="mt-2 text-3xl font-bold text-slate-950">{{ availableSpacesToday }}</div>
                                <div class="mt-1 text-xs text-emerald-950/80">Para reservar hoy o filtrar por tipo</div>
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
            <div v-if="spaces.length" class="mt-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500">
                        Mostrando {{ Math.min(visibleCards, normalizedSpaces.length) }} de {{ normalizedSpaces.length }} canchas
                    </p>
                    <div v-if="normalizedSpaces.length > visibleCards" class="flex items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-emerald-200 bg-white text-slate-700 transition hover:border-[#00C853] hover:text-emerald-700 disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="cardStartIndex === 0"
                            @click="showPreviousCards"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-emerald-200 bg-white text-slate-700 transition hover:border-[#00C853] hover:text-emerald-700 disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="cardStartIndex >= maxCardStart()"
                            @click="showNextCards"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden">
                    <div
                        class="flex gap-5 transition-transform duration-500 ease-out"
                        :style="{ transform: `translateX(calc(-${cardStartIndex * (100 / visibleCards)}% - ${cardStartIndex * (20 / visibleCards)}px))` }"
                    >
                        <article
                            v-for="space in normalizedSpaces"
                            :key="space.id"
                            class="flex h-full shrink-0 flex-col overflow-hidden rounded-2xl border border-emerald-200 bg-white/95 shadow-lg shadow-emerald-100/60 transition-all hover:-translate-y-1 hover:border-[#00C853] hover:shadow-xl"
                            :class="{
                                'basis-full': visibleCards === 1,
                                'basis-[calc(50%-10px)]': visibleCards === 2,
                                'basis-[calc(33.333%-13.333px)]': visibleCards === 3,
                            }"
                        >
                    <div class="relative h-44 overflow-hidden bg-slate-200">
                        <div
                            v-if="space.gallery.length"
                            class="flex h-full transition-transform duration-700 ease-out"
                            :style="{ transform: `translateX(-${currentSlide(space) * 100}%)` }"
                        >
                            <img
                                v-for="(image, imageIndex) in space.gallery"
                                :key="`${space.id}-${imageIndex}`"
                                :src="image"
                                :alt="`${space.name} ${imageIndex + 1}`"
                                class="h-44 w-full shrink-0 object-cover"
                            >
                        </div>
                        <div v-else class="flex h-44 w-full items-center justify-center bg-[linear-gradient(135deg,#12b76a,#123524)]">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full border border-white/25 bg-white/15 text-white">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M3 12h18M6.5 6.5l11 11M17.5 6.5l-11 11" />
                                </svg>
                            </div>
                        </div>
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-slate-950/35 via-slate-950/10 to-transparent"></div>
                        <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-0.5 text-xs backdrop-blur" :class="typeBadgeClass(space.type)">
                            {{ typeLabels[space.type] ?? space.type }}
                        </span>
                        <span class="absolute right-3 top-3 rounded-full bg-white/90 px-2.5 py-0.5 text-sm font-bold text-emerald-700 backdrop-blur">
                            {{ formatCurrency(space.price_per_hour) }}
                        </span>
                        <div
                            v-if="space.gallery.length > 1"
                            class="absolute bottom-3 left-1/2 flex -translate-x-1/2 items-center gap-1.5 rounded-full bg-slate-950/55 px-3 py-1.5 backdrop-blur-sm"
                        >
                            <button
                                v-for="(image, imageIndex) in space.gallery"
                                :key="`${space.id}-dot-${imageIndex}`"
                                type="button"
                                class="h-2.5 rounded-full transition-all duration-300"
                                :class="currentSlide(space) === imageIndex ? 'w-6 bg-[#d4fc79]' : 'w-2.5 bg-white/60 hover:bg-white'"
                                @click="setSlide(space.id, imageIndex)"
                            ></button>
                        </div>
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
                </div>

                <div v-if="normalizedSpaces.length > visibleCards" class="mt-4 flex justify-center gap-2">
                    <button
                        v-for="index in maxCardStart() + 1"
                        :key="`card-page-${index - 1}`"
                        type="button"
                        class="h-2.5 rounded-full transition-all duration-300"
                        :class="cardStartIndex === index - 1 ? 'w-8 bg-emerald-500' : 'w-2.5 bg-emerald-200 hover:bg-emerald-300'"
                        @click="cardStartIndex = index - 1"
                    ></button>
                </div>
            </div>

            <div v-else class="mt-5 rounded-2xl border border-dashed border-emerald-300 bg-white/90 px-6 py-12 text-center text-slate-500">
                No hay canchas disponibles con ese filtro
            </div>
        </div>

    </section>

    <section class="relative mt-2 overflow-hidden bg-[#0d1712] py-20">
        <div
            class="absolute inset-0 bg-cover bg-center"
            style="background-image: url('/images/banners/FondoPorqueElegirnos.png');"
        ></div>
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(7,28,17,0.82)_0%,rgba(8,33,20,0.62)_48%,rgba(5,22,14,0.9)_100%)]"></div>
        <div class="absolute inset-x-0 top-10 mx-auto h-40 max-w-3xl rounded-full bg-emerald-400/10 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-[11px] font-medium uppercase tracking-[0.35em] text-emerald-300">ReservaCancha</p>
                <h2 class="mt-4 text-3xl font-black uppercase tracking-tight text-white md:text-4xl">
                    Por que elegirnos
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-emerald-50/85 md:text-base">
                    Una experiencia mas clara, rapida y preparada para reservas reales, con herramientas pensadas para
                    que consultar, reservar y organizar partidos se sienta simple desde el primer click.
                </p>
            </div>

            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                <article
                    v-for="feature in features"
                    :key="feature.title"
                    class="group flex min-h-[390px] flex-col rounded-[1.85rem] border border-white/15 bg-white/10 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.18)] backdrop-blur-lg transition-all duration-300 hover:-translate-y-1 hover:border-emerald-300/40 hover:bg-white/14"
                >
                    <div class="h-48 overflow-hidden rounded-[1.5rem] border border-white/15 bg-white/92 shadow-inner shadow-emerald-100/40">
                        <img
                            :src="feature.image"
                            :alt="feature.title"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.05]"
                        >
                    </div>
                    <div class="mt-8 flex flex-1 flex-col">
                        <div class="inline-flex w-fit rounded-full border border-emerald-300/25 bg-emerald-400/10 px-3 py-1 text-[11px] font-medium uppercase tracking-[0.18em] text-emerald-200">
                            Beneficio clave
                        </div>
                        <h3 class="mt-5 min-h-[56px] text-xl font-semibold leading-7 text-white">
                            {{ feature.title }}
                        </h3>
                        <p class="min-h-[84px] pt-3 text-sm leading-7 text-emerald-50/80">
                            {{ feature.description }}
                        </p>
                        <div class="mt-auto border-t border-white/10 pt-5 text-sm font-medium text-emerald-300">
                            Diseñado para ayudarte a reservar mejor
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
