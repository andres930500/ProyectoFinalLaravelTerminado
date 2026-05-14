<script setup>
import Pagination from '@/Components/Pagination.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    blockedSlots: Object,
});

function destroyBlockedSlot(id) {
    if (window.confirm('Eliminar este bloqueo?')) {
        router.delete(route('admin.blocked-slots.destroy', id), { preserveScroll: true });
    }
}
</script>

<template>
    <AppLayout title="Bloqueos">
        <template #header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700">Bloqueos</div>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Restricciones manuales</h2>
                </div>
                <Link :href="route('admin.blocked-slots.create')" class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">
                    Nuevo Bloqueo
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-600">Cancha</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-600">Inicio</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-600">Fin</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-600">Motivo</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr v-for="item in blockedSlots.data" :key="item.id">
                                <td class="px-6 py-4 font-medium text-slate-900">{{ item.space?.name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ item.start_time }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ item.end_time }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ item.reason }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-3">
                                        <Link :href="route('admin.blocked-slots.edit', item.id)" class="rounded-full bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">Editar</Link>
                                        <button type="button" class="rounded-full bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100" @click="destroyBlockedSlot(item.id)">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="border-t border-slate-200 px-6 py-4">
                        <Pagination :links="blockedSlots.links" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
