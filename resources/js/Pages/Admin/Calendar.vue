<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    space: Object,
    spaces: Array,
    weekStart: String,
    weekEnd: String,
    calendar: Array,
    prevWeek: String,
    nextWeek: String,
});

const slotClasses = {
    available: 'border border-emerald-200 bg-white text-gray-700 hover:border-emerald-400 hover:bg-emerald-50 cursor-pointer',
    reserved_confirmed: 'bg-emerald-100 text-emerald-900 border border-emerald-200',
    reserved_pending: 'bg-amber-100 text-amber-900 border border-amber-200',
    blocked: 'bg-rose-100 text-rose-900 border border-rose-200',
    closed: 'bg-gray-100 text-gray-400 border border-gray-200',
};

function changeSpace(slug) {
    router.get(route('admin.calendar'), { space: slug, week: props.weekStart }, { preserveState: true, preserveScroll: true });
}

function moveWeek(week) {
    router.get(route('admin.calendar'), { space: props.space?.slug, week }, { preserveState: true, preserveScroll: true });
}

function handleSlot(slot) {
    if (slot.status !== 'available' || !props.space) return;

    router.get(route('public.reservations.create'), {
        space: props.space.slug,
        start: slot.start,
    });
}

function formatRange(start, end) {
    return `Semana del ${new Intl.DateTimeFormat('es-CO', { dateStyle: 'medium' }).format(new Date(start))} al ${new Intl.DateTimeFormat('es-CO', { dateStyle: 'medium' }).format(new Date(end))}`;
}
</script>

<template>
    <AppLayout title="Calendario">
        <template #header>
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-300">Calendario</div>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-white">Vista semanal operativa</h2>
                <p class="mt-2 text-sm text-slate-300">Reserva, bloqueo y disponibilidad por espacio en una sola pantalla.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <select
                            class="rounded-2xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            :value="space?.slug || ''"
                            @change="changeSpace($event.target.value)"
                        >
                            <option v-for="item in spaces" :key="item.slug" :value="item.slug">{{ item.name }}</option>
                        </select>

                        <div class="flex items-center justify-between gap-3">
                            <button type="button" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50" @click="moveWeek(prevWeek)">
                                Anterior
                            </button>
                            <div class="text-sm font-medium text-slate-700">{{ formatRange(weekStart, weekEnd) }}</div>
                            <button type="button" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50" @click="moveWeek(nextWeek)">
                                Siguiente
                            </button>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 xl:grid-cols-7">
                    <div v-for="day in calendar" :key="day.date" class="rounded-[1.5rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/30">
                        <div class="border-b border-slate-200 px-4 py-4">
                            <div class="text-sm font-semibold text-slate-900">{{ day.day_name }}</div>
                            <div class="text-xs text-slate-500">{{ day.date }}</div>
                        </div>
                        <div class="max-h-[70vh] space-y-2 overflow-y-auto p-3">
                            <button
                                v-for="slot in day.slots"
                                :key="`${day.date}-${slot.start}`"
                                type="button"
                                class="w-full rounded-md px-3 py-2 text-left text-xs font-medium transition"
                                :class="slotClasses[slot.status]"
                                :title="slot.meta || ''"
                                @click="handleSlot(slot)"
                            >
                                <div>{{ slot.label }}</div>
                                <div v-if="slot.meta" class="mt-1 truncate text-[11px] opacity-80">{{ slot.meta }}</div>
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
