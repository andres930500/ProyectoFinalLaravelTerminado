<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
});

const search = ref('');

const filteredClients = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return props.clients;
    }

    return props.clients.filter((client) =>
        [client.nombre, client.email]
            .filter(Boolean)
            .some((value) => value.toLowerCase().includes(term))
    );
});

function initials(name) {
    if (!name) return 'CL';

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));
}

function formatDateTime(value) {
    if (!value) return '-';

    return new Intl.DateTimeFormat('es-CO', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function customerBadge(total) {
    if (total >= 5) {
        return {
            label: 'Frecuente*',
            classes: 'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-500/30',
        };
    }

    if (total >= 2) {
        return {
            label: 'Regular',
            classes: 'bg-sky-500/15 text-sky-300 ring-1 ring-sky-500/30',
        };
    }

    return {
        label: 'Nuevo',
        classes: 'bg-slate-500/15 text-slate-300 ring-1 ring-slate-500/30',
    };
}
</script>

<template>
    <AppLayout title="Clientes">
        <template #header>
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700">Clientes</div>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Gestion de clientes</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-600">
                        Revisa frecuencia de compra, gasto acumulado y actividad reciente para entender mejor tu base de clientes.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">Directorio de clientes</h3>
                            <p class="mt-1 text-sm text-slate-500">Busca por nombre o correo para abrir el perfil completo.</p>
                        </div>

                        <div class="w-full max-w-md">
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Buscar por nombre o email"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-0"
                            >
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-slate-500">
                                    <th class="px-5 py-4 font-semibold">Cliente</th>
                                    <th class="px-5 py-4 font-semibold">Telefono</th>
                                    <th class="px-5 py-4 font-semibold">Total reservas</th>
                                    <th class="px-5 py-4 font-semibold">Confirmadas</th>
                                    <th class="px-5 py-4 font-semibold">Total gastado</th>
                                    <th class="px-5 py-4 font-semibold">Ultima reserva</th>
                                    <th class="px-5 py-4 font-semibold">Nivel</th>
                                    <th class="px-5 py-4 font-semibold text-right">Accion</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr v-for="client in filteredClients" :key="client.email" class="transition hover:bg-slate-50/80">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                                {{ initials(client.nombre) }}
                                            </div>
                                            <div>
                                                <div class="font-medium text-slate-950">{{ client.nombre }}</div>
                                                <div class="text-slate-500">{{ client.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ client.telefono || '-' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ client.total_reservas }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ client.reservas_confirmadas }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ formatCurrency(client.total_gastado) }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ formatDateTime(client.ultima_reserva) }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="customerBadge(client.total_reservas).classes">
                                            {{ customerBadge(client.total_reservas).label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <Link
                                            :href="route('admin.clients.show', client.email)"
                                            class="inline-flex items-center rounded-xl bg-[#00C853] px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-[#00b84c]"
                                        >
                                            Ver perfil
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!filteredClients.length">
                                    <td colspan="8" class="px-5 py-10 text-center text-slate-500">
                                        No encontramos clientes con ese termino de busqueda.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
