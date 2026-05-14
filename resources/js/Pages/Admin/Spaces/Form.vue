<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    space: { type: Object, default: null },
    types: { type: Array, default: () => [] },
});

const dayLabels = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];

function buildAvailabilities() {
    return dayLabels.map((label, dayOfWeek) => {
        const existing = props.space?.availabilities?.find((item) => item.day_of_week === dayOfWeek);

        return {
            day_of_week: dayOfWeek,
            label,
            enabled: Boolean(existing),
            start_time: existing?.start_time?.slice(0, 5) ?? '08:00',
            end_time: existing?.end_time?.slice(0, 5) ?? '20:00',
        };
    });
}

const form = useForm({
    name: props.space?.name ?? '',
    type: props.space?.type ?? props.types[0] ?? '',
    capacity: props.space?.capacity ?? 10,
    price_per_hour: props.space?.price_per_hour ?? '',
    description: props.space?.description ?? '',
    rules: props.space?.rules ?? '',
    address: props.space?.address ?? '',
    images: [],
    is_active: props.space?.is_active ?? true,
    availabilities: buildAvailabilities(),
});

const previewImages = computed(() => {
    if (Array.isArray(form.images) && form.images.length) {
        return form.images.map((file) => URL.createObjectURL(file));
    }

    return props.space?.images ?? [];
});

function handleImagesChange(event) {
    const files = Array.from(event.target.files ?? []).slice(0, 3);
    form.images = files;
}

function submit() {
    const options = { forceFormData: true, preserveScroll: true };

    if (props.space) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.spaces.update', props.space.slug), options);
        return;
    }

    form.post(route('admin.spaces.store'), options);
}
</script>

<template>
    <form class="space-y-8" @submit.prevent="submit">
        <section class="rounded-[1.75rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/40">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-slate-950">Ficha del espacio</h3>
                <p class="mt-1 text-sm text-slate-500">Informacion principal visible en el modulo publico.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <InputLabel for="name" value="Nombre" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="type" value="Tipo" />
                    <select id="type" v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option v-for="type in types" :key="type" :value="type">{{ type }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.type" />
                </div>

                <div>
                    <InputLabel for="capacity" value="Capacidad" />
                    <TextInput id="capacity" v-model="form.capacity" type="number" min="1" class="mt-1 block w-full" />
                    <InputError class="mt-2" :message="form.errors.capacity" />
                </div>

                <div>
                    <InputLabel for="price_per_hour" value="Precio por hora" />
                    <TextInput id="price_per_hour" v-model="form.price_per_hour" type="number" min="0" step="0.01" class="mt-1 block w-full" />
                    <InputError class="mt-2" :message="form.errors.price_per_hour" />
                </div>

                <div>
                    <InputLabel for="images" value="Imagenes de la cancha" />
                    <input id="images" type="file" accept="image/*" multiple class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-medium file:text-slate-700" @change="handleImagesChange" />
                    <p class="mt-2 text-xs text-slate-500">Puedes subir maximo 3 imagenes. Si cargas nuevas imagenes al editar, reemplazaran las actuales.</p>
                    <InputError class="mt-2" :message="form.errors.images" />
                    <InputError class="mt-2" :message="form.errors['images.0'] || form.errors['images.1'] || form.errors['images.2']" />
                </div>

                <div class="md:col-span-2">
                    <div v-if="previewImages.length" class="mb-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="(previewImage, index) in previewImages" :key="`${previewImage}-${index}`" class="overflow-hidden rounded-[1.5rem] border border-slate-200">
                            <img :src="previewImage" alt="Vista previa" class="h-40 w-full object-cover">
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        Cancha activa
                    </label>
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="description" value="Descripcion" />
                    <textarea id="description" v-model="form.description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="address" value="Direccion de la cancha" />
                    <TextInput
                        id="address"
                        v-model="form.address"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Ej: Avenida Kevin Angel # 58-120, Manizales"
                    />
                    <p class="mt-2 text-xs text-slate-500">Esta direccion se usara para mostrar el mapa de Google en la vista publica.</p>
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="rules" value="Reglas" />
                    <textarea id="rules" v-model="form.rules" rows="5" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    <InputError class="mt-2" :message="form.errors.rules" />
                </div>
            </div>
        </section>

        <section class="rounded-[1.75rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/40">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-950">Disponibilidad semanal</h3>
                <p class="mt-1 text-sm text-slate-500">Activa o desactiva cada dia y define la franja horaria.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Dia</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Disponible</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Inicio</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Fin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="(availability, index) in form.availabilities" :key="availability.day_of_week">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ availability.label }}</td>
                            <td class="px-4 py-3">
                                <input v-model="availability.enabled" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            </td>
                            <td class="px-4 py-3">
                                <input v-model="availability.start_time" type="time" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" :disabled="!availability.enabled">
                                <InputError class="mt-2" :message="form.errors[`availabilities.${index}.start_time`]" />
                            </td>
                            <td class="px-4 py-3">
                                <input v-model="availability.end_time" type="time" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" :disabled="!availability.enabled">
                                <InputError class="mt-2" :message="form.errors[`availabilities.${index}.end_time`]" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="flex flex-col gap-3 sm:flex-row">
            <PrimaryButton :disabled="form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </PrimaryButton>
            <Link :href="route('admin.spaces.index')" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                Cancelar
            </Link>
        </div>
    </form>
</template>
