<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const mobileMenuOpen = ref(false);
const page = usePage();
const whatsappExpanded = ref(false);
let whatsappInterval = null;

const user = computed(() => page.props.auth?.user ?? null);
const whatsappUrl = computed(() => {
    return import.meta.env.VITE_WHATSAPP_URL
        || 'https://wa.me/573000000000?text=Hola,%20tengo%20una%20duda%20sobre%20una%20reserva';
});

const navigation = computed(() => {
    const items = [{ label: 'Inicio', href: '/' }];

    if (user.value) {
        items.push({ label: 'Panel Admin', href: route('dashboard') });
    }

    return items;
});

function closeMenu() {
    mobileMenuOpen.value = false;
}

onMounted(() => {
    whatsappInterval = window.setInterval(() => {
        whatsappExpanded.value = !whatsappExpanded.value;
    }, 30000);
});

onBeforeUnmount(() => {
    if (whatsappInterval) {
        window.clearInterval(whatsappInterval);
    }
});
</script>

<template>
    <div class="min-h-screen bg-[#eef6ef] text-white">
        <header class="sticky top-0 z-30 border-b border-slate-700 bg-[#2b302d]">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <Link href="/" class="relative z-10 flex items-center gap-3 text-lg font-semibold text-slate-950">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#00C853] font-bold text-black">RC</span>
                    <span class="flex flex-col leading-tight">
                        <span class="text-sm text-white">ReservaCancha</span>
                        <span class="text-[11px] font-medium text-slate-300">Una cancha perfecta para tu partido</span>
                    </span>
                </Link>

                <nav class="hidden items-center gap-2 md:flex">
                    <Link
                        v-for="item in navigation"
                        :key="item.href"
                        :href="item.href"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-300 transition hover:text-emerald-400"
                    >
                        {{ item.label }}
                    </Link>
                    <Link
                        :href="user ? route('dashboard') : route('login')"
                        class="ml-2 inline-flex items-center rounded-lg bg-[#00C853] px-4 py-1.5 text-sm font-medium text-black transition hover:bg-[#00b84c]"
                    >
                        {{ user ? 'Panel' : 'Registrarse' }}
                    </Link>
                </nav>

                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-600 bg-[#333835] text-slate-100 md:hidden"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
            </div>

            <div v-if="mobileMenuOpen" class="mx-auto max-w-7xl border-t border-slate-700 bg-[#2b302d] md:hidden">
                <nav class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-4 sm:px-6">
                    <Link
                        v-for="item in navigation"
                        :key="item.href"
                        :href="item.href"
                        class="rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-emerald-400"
                        @click="closeMenu"
                    >
                        {{ item.label }}
                    </Link>
                    <Link
                        :href="user ? route('dashboard') : route('login')"
                        class="mt-2 inline-flex items-center justify-center rounded-lg bg-[#00C853] px-4 py-3 text-sm font-medium text-black"
                        @click="closeMenu"
                    >
                        {{ user ? 'Panel' : 'Registrarse' }}
                    </Link>
                </nav>
            </div>
        </header>

        <main class="relative">
            <slot />
        </main>

        <a
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="fixed bottom-5 right-5 z-40 inline-flex h-16 items-center overflow-hidden rounded-full bg-[#25D366] text-sm font-semibold text-[#0f172a] shadow-[0_16px_40px_rgba(37,211,102,0.35)] transition-all duration-500 hover:scale-[1.02] hover:bg-[#20bd5c] focus:outline-none focus:ring-4 focus:ring-[#25D366]/30"
            :class="whatsappExpanded ? 'gap-3 pl-4 pr-5' : 'w-16 justify-center'"
            aria-label="Abrir WhatsApp para resolver dudas"
        >
            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-[#25D366]">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M19.05 4.94A9.93 9.93 0 0 0 12.02 2C6.53 2 2.05 6.46 2.05 11.97c0 1.76.46 3.48 1.34 5L2 22l5.17-1.35a10.02 10.02 0 0 0 4.82 1.23h.01c5.49 0 9.97-4.47 9.97-9.98a9.9 9.9 0 0 0-2.92-6.96zM12 20.19h-.01a8.3 8.3 0 0 1-4.23-1.16l-.3-.18-3.07.8.82-2.99-.2-.31a8.24 8.24 0 0 1-1.27-4.39c0-4.56 3.71-8.28 8.28-8.28a8.2 8.2 0 0 1 5.85 2.43 8.2 8.2 0 0 1 2.42 5.85c0 4.57-3.71 8.29-8.27 8.29zm4.54-6.2c-.25-.13-1.47-.72-1.7-.8-.23-.08-.39-.13-.56.13-.16.25-.64.8-.78.97-.15.17-.29.19-.54.07-.25-.13-1.05-.39-2-1.24-.74-.66-1.23-1.48-1.38-1.73-.14-.25-.01-.38.11-.51.11-.11.25-.29.37-.43.12-.14.16-.24.25-.4.08-.17.04-.31-.02-.44-.07-.13-.56-1.35-.76-1.84-.2-.48-.41-.41-.56-.42h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.09 0 1.23.9 2.42 1.02 2.59.12.17 1.76 2.69 4.25 3.77.59.26 1.05.42 1.41.54.59.19 1.12.16 1.55.1.47-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.22-.16-.47-.28z" />
                </svg>
            </span>
            <span
                class="flex flex-col leading-tight transition-all duration-500 sm:flex"
                :class="whatsappExpanded ? 'max-w-[180px] opacity-100' : 'max-w-0 opacity-0'"
            >
                <span class="text-[11px] font-medium uppercase tracking-[0.18em] text-[#14532d]">Dudas?</span>
                <span class="text-sm font-semibold text-slate-900">Escribenos por WhatsApp</span>
            </span>
        </a>

        <footer class="relative border-t border-emerald-200 bg-[#f7fbf7]">
            <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <div class="rounded-[2rem] border border-emerald-100 bg-white p-6 shadow-xl shadow-emerald-100/50 md:p-8">
                    <div class="grid gap-10 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#00C853] font-bold text-black">RC</span>
                        <div>
                            <div class="font-semibold text-slate-950">ReservaCancha</div>
                            <div class="text-sm text-[#9CA3AF]">Sistema de reservas de canchas de futbol</div>
                        </div>
                    </div>
                    <p class="mt-4 max-w-xs text-xs leading-6 text-[#9CA3AF]">
                        Canchas listas, disponibilidad real y reservas sin fricciones para partidos y torneos.
                    </p>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-[#9CA3AF]">Enlaces</div>
                    <div class="mt-4 flex flex-col gap-2 text-sm text-slate-600">
                        <Link href="/" class="transition hover:text-[#00C853]">Inicio</Link>
                        <Link href="/" class="transition hover:text-[#00C853]">Canchas</Link>
                        <Link href="/" class="transition hover:text-[#00C853]">Reservas</Link>
                        <Link :href="route('login')" class="transition hover:text-[#00C853]">Panel administrativo</Link>
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-[#9CA3AF]">Redes</div>
                    <div class="mt-4 flex items-center gap-3">
                        <a href="#" class="flex h-10 w-10 items-center justify-center rounded-full border border-emerald-200 bg-white text-slate-600 transition hover:border-[#00C853] hover:text-[#00C853]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 9H16V6h-2.5C10.46 6 9 7.74 9 10.41V12H6v3h3v6h3v-6h3l.5-3H12v-1.41c0-.92.27-1.59 1.5-1.59z"/></svg>
                        </a>
                        <a href="#" class="flex h-10 w-10 items-center justify-center rounded-full border border-emerald-200 bg-white text-slate-600 transition hover:border-[#00C853] hover:text-[#00C853]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm0 2A3.75 3.75 0 0 0 4 7.75v8.5A3.75 3.75 0 0 0 7.75 20h8.5A3.75 3.75 0 0 0 20 16.25v-8.5A3.75 3.75 0 0 0 16.25 4zm8.75 1.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
                        </a>
                        <a href="#" class="flex h-10 w-10 items-center justify-center rounded-full border border-emerald-200 bg-white text-slate-600 transition hover:border-[#00C853] hover:text-[#00C853]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2H22l-6.77 7.74L23 22h-6.1l-4.78-6.57L6.37 22H3.25l7.25-8.29L1 2h6.25l4.32 5.95L18.9 2zm-1.07 18h1.69L6.33 3.9H4.52z"/></svg>
                        </a>
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-[#9CA3AF]">Newsletter</div>
                    <p class="mt-4 text-sm leading-6 text-[#9CA3AF]">Recibe horarios destacados y novedades del sistema.</p>
                    <div class="mt-4 rounded-[1.25rem] border border-emerald-100 bg-emerald-50/60 p-3">
                        <div class="flex gap-2">
                            <input type="email" placeholder="email@email.com" class="min-w-0 flex-1 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-[#9CA3AF] focus:border-[#00C853] focus:outline-none">
                            <button type="button" class="rounded-xl bg-[#00C853] px-5 py-3 text-sm font-semibold text-black transition hover:bg-[#00b84c]">
                                Suscribir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
                    <div class="mt-8 border-t border-emerald-100 pt-5 text-center text-xs text-[#9CA3AF]">
                        Copyright {{ new Date().getFullYear() }} ReservaCancha. Todos los derechos reservados.
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
