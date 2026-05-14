<script setup>
import { onMounted, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import NotificationBell from "@/Components/NotificationBell.vue";

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const sidebarCollapsed = ref(true);

const logout = () => {
    router.post(route("logout"));
};

function navClasses(active) {
    return active
        ? "bg-emerald-500 text-slate-950 shadow-lg shadow-emerald-500/20"
        : "text-slate-600 hover:bg-emerald-50 hover:text-emerald-700";
}

function contentRevealClasses() {
    if (!sidebarCollapsed.value) {
        return "";
    }

    return "xl:w-0 xl:overflow-hidden xl:opacity-0 xl:group-hover/sidebar:w-auto xl:group-hover/sidebar:overflow-visible xl:group-hover/sidebar:opacity-100";
}
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="h-screen overflow-hidden bg-[#eef4ef]">
            <div
                class="absolute inset-x-0 top-0 h-72 bg-[radial-gradient(circle_at_top,#dff7e8_0%,#eef4ef_50%,#eef4ef_100%)]"
            ></div>

            <div class="relative flex h-full min-h-0">
                <aside
                    class="group/sidebar hidden h-screen shrink-0 border-r border-emerald-100 bg-[#f7fbf8] transition-all duration-300 xl:sticky xl:top-0 xl:flex xl:flex-col"
                    :class="sidebarCollapsed ? 'xl:w-24 xl:hover:w-72' : 'w-72'"
                >
                    <div class="border-b border-emerald-100 px-4 py-5">
                        <Link
                            :href="route('dashboard')"
                            class="flex items-center gap-3"
                            :class="sidebarCollapsed ? 'xl:justify-center xl:group-hover/sidebar:justify-start' : ''"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500 font-bold text-slate-950 shadow-lg shadow-emerald-500/20"
                            >
                                RC
                            </div>
                            <div :class="contentRevealClasses()">
                                <div
                                    class="whitespace-nowrap text-sm font-semibold text-slate-900"
                                >
                                    ReservaCancha Admin
                                </div>
                                <div class="whitespace-nowrap text-xs text-slate-500">
                                    Panel de gestion de reservas
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div class="flex-1 px-4 py-6">
                        <div
                            class="mb-4 px-3 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400"
                            :class="contentRevealClasses()"
                        >
                            Navegacion
                        </div>

                        <nav class="space-y-2">
                            <Link
                                :href="route('dashboard')"
                                class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    [
                                        navClasses(route().current('dashboard')),
                                        sidebarCollapsed ? 'xl:justify-center xl:px-0 xl:group-hover/sidebar:justify-start xl:group-hover/sidebar:px-4' : '',
                                    ]
                                "
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 12l9-9 9 9"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 21V9h6v12"
                                    />
                                </svg>
                                <span class="whitespace-nowrap" :class="contentRevealClasses()">Dashboard</span>
                            </Link>

                            <Link
                                :href="route('admin.calendar')"
                                class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    [
                                        navClasses(route().current('admin.calendar')),
                                        sidebarCollapsed ? 'xl:justify-center xl:px-0 xl:group-hover/sidebar:justify-start xl:group-hover/sidebar:px-4' : '',
                                    ]
                                "
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <rect
                                        x="3"
                                        y="4"
                                        width="18"
                                        height="18"
                                        rx="2"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16 2v4M8 2v4M3 10h18"
                                    />
                                </svg>
                                <span class="whitespace-nowrap" :class="contentRevealClasses()">Calendario</span>
                            </Link>

                            <Link
                                :href="route('admin.spaces.index')"
                                class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    [
                                        navClasses(route().current('admin.spaces.*')),
                                        sidebarCollapsed ? 'xl:justify-center xl:px-0 xl:group-hover/sidebar:justify-start xl:group-hover/sidebar:px-4' : '',
                                    ]
                                "
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 19l8-14 8 14"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5.5 16h13"
                                    />
                                </svg>
                                <span class="whitespace-nowrap" :class="contentRevealClasses()">Canchas</span>
                            </Link>

                            <Link
                                :href="route('admin.reservations.index')"
                                class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    [
                                        navClasses(route().current('admin.reservations.*')),
                                        sidebarCollapsed ? 'xl:justify-center xl:px-0 xl:group-hover/sidebar:justify-start xl:group-hover/sidebar:px-4' : '',
                                    ]
                                "
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8 7h8M8 12h8M8 17h5"
                                    />
                                    <rect
                                        x="4"
                                        y="3"
                                        width="16"
                                        height="18"
                                        rx="2"
                                    />
                                </svg>
                                <span class="whitespace-nowrap" :class="contentRevealClasses()">Reservas</span>
                            </Link>

                            <Link
                                :href="route('admin.clients.index')"
                                class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    [
                                        navClasses(route().current('admin.clients.*')),
                                        sidebarCollapsed ? 'xl:justify-center xl:px-0 xl:group-hover/sidebar:justify-start xl:group-hover/sidebar:px-4' : '',
                                    ]
                                "
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2"
                                    />
                                    <circle cx="9" cy="7" r="4" />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M22 21v-2a4 4 0 00-3-3.87"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16 3.13a4 4 0 010 7.75"
                                    />
                                </svg>
                                <span class="whitespace-nowrap" :class="contentRevealClasses()">Clientes</span>
                            </Link>

                            <Link
                                :href="route('admin.reports')"
                                class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    [
                                        navClasses(route().current('admin.reports*')),
                                        sidebarCollapsed ? 'xl:justify-center xl:px-0 xl:group-hover/sidebar:justify-start xl:group-hover/sidebar:px-4' : '',
                                    ]
                                "
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 19V9"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M10 19V5"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16 19v-7"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M22 19V3"
                                    />
                                </svg>
                                <span class="whitespace-nowrap" :class="contentRevealClasses()">Reportes</span>
                            </Link>

                            <Link
                                :href="route('admin.blocked-slots.index')"
                                class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    [
                                        navClasses(route().current('admin.blocked-slots.*')),
                                        sidebarCollapsed ? 'xl:justify-center xl:px-0 xl:group-hover/sidebar:justify-start xl:group-hover/sidebar:px-4' : '',
                                    ]
                                "
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle cx="12" cy="12" r="9" />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8 8l8 8"
                                    />
                                </svg>
                                <span class="whitespace-nowrap" :class="contentRevealClasses()">Bloqueos</span>
                            </Link>
                        </nav>
                    </div>

                    <div class="border-t border-emerald-100 px-4 py-5">
                        <div
                            class="rounded-2xl bg-white p-4 shadow-sm shadow-slate-200/60 ring-1 ring-emerald-100"
                            :class="sidebarCollapsed ? 'xl:px-3 xl:group-hover/sidebar:px-4' : ''"
                        >
                            <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'xl:justify-center xl:group-hover/sidebar:justify-start' : ''">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    {{ ($page.props.auth.user.name || 'A').charAt(0).toUpperCase() }}
                                </div>
                                <div :class="contentRevealClasses()">
                                    <div class="whitespace-nowrap text-sm font-semibold text-slate-900">
                                        {{ $page.props.auth.user.name }}
                                    </div>
                                    <div class="mt-1 whitespace-nowrap text-xs text-slate-500">
                                        {{ $page.props.auth.user.email }}
                                    </div>
                                    <Link
                                        :href="route('profile.show')"
                                        class="mt-4 inline-flex text-sm font-medium text-emerald-700 transition hover:text-emerald-800"
                                    >
                                        Ver perfil
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="flex min-w-0 flex-1 min-h-0 flex-col">
                    <header
                        class="sticky top-0 z-30 border-b border-emerald-100 bg-white/85 backdrop-blur-xl"
                    >
                        <div
                            class="flex h-20 items-center justify-between px-4 sm:px-6 lg:px-8"
                        >
                            <div class="flex items-center gap-3">
                                <button
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-emerald-100 bg-white text-slate-600 shadow-sm xl:hidden"
                                    @click="
                                        showingNavigationDropdown =
                                            !showingNavigationDropdown
                                    "
                                >
                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            v-if="!showingNavigationDropdown"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                        <path
                                            v-else
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>

                                <div>
                                    <div
                                        class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-600"
                                    >
                                        Panel administrativo
                                    </div>
                                    <div
                                        class="mt-1 text-lg font-semibold text-slate-900"
                                    >
                                        {{ title }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <NotificationBell />
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-2xl border border-emerald-100 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                                    @click="logout"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M10 17l5-5-5-5"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15 12H3"
                                        />
                                    </svg>
                                    Cerrar sesion
                                </button>
                            </div>
                        </div>
                    </header>

                    <div
                        v-if="showingNavigationDropdown"
                        class="border-b border-emerald-100 bg-white px-4 py-4 shadow-sm xl:hidden"
                    >
                        <nav class="grid gap-2">
                            <Link
                                :href="route('dashboard')"
                                class="rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    navClasses(route().current('dashboard'))
                                "
                                >Dashboard</Link
                            >
                            <Link
                                :href="route('admin.calendar')"
                                class="rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    navClasses(
                                        route().current('admin.calendar'),
                                    )
                                "
                                >Calendario</Link
                            >
                            <Link
                                :href="route('admin.spaces.index')"
                                class="rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    navClasses(
                                        route().current('admin.spaces.*'),
                                    )
                                "
                                >Canchas</Link
                            >
                            <Link
                                :href="route('admin.reservations.index')"
                                class="rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    navClasses(
                                        route().current('admin.reservations.*'),
                                    )
                                "
                                >Reservas</Link
                            >
                            <Link
                                :href="route('admin.clients.index')"
                                class="rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    navClasses(
                                        route().current('admin.clients.*'),
                                    )
                                "
                                >Clientes</Link
                            >
                            <Link
                                :href="route('admin.reports')"
                                class="rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    navClasses(
                                        route().current('admin.reports*'),
                                    )
                                "
                                >Reportes</Link
                            >
                            <Link
                                :href="route('admin.blocked-slots.index')"
                                class="rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :class="
                                    navClasses(
                                        route().current(
                                            'admin.blocked-slots.*',
                                        ),
                                    )
                                "
                                >Bloqueos</Link
                            >
                            <Link
                                :href="route('profile.show')"
                                class="rounded-2xl px-4 py-3 text-sm font-medium text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700"
                            >
                                Perfil
                            </Link>
                        </nav>
                    </div>

                    <header v-if="$slots.header" class="relative">
                        <div
                            class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
                        >
                            <slot name="header" />
                        </div>
                    </header>

                    <main class="relative min-h-0 flex-1 overflow-y-auto pb-10">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>
