<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    spaces: Array,
    days: Array,
});

const form = useForm({
    space_id: '',
    day_of_week: 1,
    start_time: '08:00',
    end_time: '20:00',
});

function submit() {
    form.post(route('admin.availabilities.store'));
}
</script>

<template>
    <AppLayout title="Nueva Disponibilidad">
        <template #header>
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-300">Disponibilidad</div>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-white">Nueva disponibilidad</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <form class="space-y-6 rounded-[1.75rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/40" @submit.prevent="submit">
                    <select v-model="form.space_id" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Selecciona una cancha</option>
                        <option v-for="space in spaces" :key="space.id" :value="space.id">{{ space.name }}</option>
                    </select>
                    <select v-model="form.day_of_week" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option v-for="day in days" :key="day.value" :value="day.value">{{ day.label }}</option>
                    </select>
                    <div class="grid gap-4 md:grid-cols-2">
                        <input v-model="form.start_time" type="time" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <input v-model="form.end_time" type="time" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Guardar</button>
                        <Link :href="route('admin.spaces.index')" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancelar</Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
