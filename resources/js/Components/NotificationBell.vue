<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const isOpen = ref(false);
const loading = ref(true);
const root = ref(null);
const summary = ref({
    pendingReservations: 0,
    todayReservations: 0,
    recentActivity: [],
});

let pollInterval = null;

const badgeLabel = computed(() => {
    const total = Number(summary.value.pendingReservations ?? 0);

    if (total > 9) return '9+';

    return total > 0 ? String(total) : '';
});

const statusClasses = {
    pending: 'bg-amber-500/15 text-amber-700 ring-1 ring-amber-500/20',
    confirmed: 'bg-emerald-500/15 text-emerald-700 ring-1 ring-emerald-500/20',
    rejected: 'bg-rose-500/15 text-rose-700 ring-1 ring-rose-500/20',
    cancelled: 'bg-rose-500/15 text-rose-700 ring-1 ring-rose-500/20',
    finished: 'bg-slate-500/15 text-slate-700 ring-1 ring-slate-500/20',
};

function initials(name) {
    return (name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('') || 'RC';
}

function formatTime(value) {
    if (!value) return '';

    return new Intl.DateTimeFormat('es-CO', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(value));
}

async function loadSummary() {
    try {
        const response = await fetch('/admin/notifications/summary', {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) return;

        summary.value = await response.json();
    } catch {
        // Keep the last known values if polling fails.
    } finally {
        loading.value = false;
    }
}

function toggleDropdown() {
    isOpen.value = !isOpen.value;
}

function handleClickOutside(event) {
    if (root.value && !root.value.contains(event.target)) {
        isOpen.value = false;
    }
}

onMounted(() => {
    loadSummary();
    pollInterval = window.setInterval(loadSummary, 30000);
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    if (pollInterval) {
        window.clearInterval(pollInterval);
    }

    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="root" class="relative">
        <button
            type="button"
            class="relative inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-emerald-100 bg-white text-slate-600 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
            @click="toggleDropdown"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a3 3 0 0 0 6 0" />
            </svg>

            <span
                v-if="badgeLabel"
                class="absolute -right-1 -top-1 inline-flex min-w-[1.35rem] items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold text-white"
            >
                {{ badgeLabel }}
            </span>
        </button>

        <div
            v-if="isOpen"
            class="absolute right-0 z-50 mt-3 w-[22rem] rounded-3xl border border-emerald-100 bg-white p-4 shadow-[0_24px_60px_rgba(15,23,42,0.12)]"
        >
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <div class="text-xs font-medium uppercase tracking-[0.18em] text-amber-700">Por aprobar</div>
                    <div class="mt-2 text-2xl font-semibold text-slate-900">{{ summary.pendingReservations }}</div>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="text-xs font-medium uppercase tracking-[0.18em] text-emerald-700">Hoy</div>
                    <div class="mt-2 text-2xl font-semibold text-slate-900">{{ summary.todayReservations }}</div>
                </div>
            </div>

            <div class="mt-4">
                <div class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Actividad reciente</div>

                <div v-if="loading" class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-8 text-center text-sm text-slate-500">
                    Cargando notificaciones...
                </div>

                <div v-else-if="summary.recentActivity.length" class="space-y-3">
                    <div
                        v-for="(item, index) in summary.recentActivity"
                        :key="`${item.user_name}-${index}`"
                        class="flex items-start gap-3 rounded-2xl border border-emerald-100 bg-[#f7fbf8] p-3"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-xs font-semibold text-emerald-700">
                            {{ initials(item.user_name) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-slate-900">{{ item.user_name }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ item.space_name }}</div>
                            <div class="mt-2 text-[11px] text-slate-400">{{ formatTime(item.created_at) }}</div>
                        </div>

                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="statusClasses[item.status] ?? 'bg-slate-100 text-slate-700 ring-1 ring-slate-200'">
                            {{ item.status }}
                        </span>
                    </div>
                </div>

                <div v-else class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-8 text-center text-sm text-slate-500">
                    No hay actividad reciente.
                </div>
            </div>

            <Link
                :href="route('admin.reservations.index')"
                class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-[#00C853] px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-[#00b84c]"
            >
                Ver todas las reservas
            </Link>
        </div>
    </div>
</template>
