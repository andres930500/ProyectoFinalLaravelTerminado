<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    spaces: Array,
    blockedSlot: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    space_id: props.blockedSlot?.space_id ?? props.spaces[0]?.id ?? '',
    start_time: props.blockedSlot?.start_time?.slice(0, 16) ?? '',
    end_time: props.blockedSlot?.end_time?.slice(0, 16) ?? '',
    reason: props.blockedSlot?.reason ?? '',
});

function submit() {
    if (props.blockedSlot) {
        form.put(route('admin.blocked-slots.update', props.blockedSlot.id));
        return;
    }

    form.post(route('admin.blocked-slots.store'));
}
</script>

<template>
    <form class="space-y-6 rounded-[1.75rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/40" @submit.prevent="submit">
        <div>
            <InputLabel for="space_id" value="Cancha" />
            <select id="space_id" v-model="form.space_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option v-for="space in spaces" :key="space.id" :value="space.id">{{ space.name }}</option>
            </select>
            <InputError class="mt-2" :message="form.errors.space_id" />
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <InputLabel for="start_time" value="Inicio" />
                <input id="start_time" v-model="form.start_time" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <InputError class="mt-2" :message="form.errors.start_time" />
            </div>

            <div>
                <InputLabel for="end_time" value="Fin" />
                <input id="end_time" v-model="form.end_time" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <InputError class="mt-2" :message="form.errors.end_time" />
            </div>
        </div>

        <div>
            <InputLabel for="reason" value="Motivo" />
            <textarea id="reason" v-model="form.reason" rows="4" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
            <InputError class="mt-2" :message="form.errors.reason" />
        </div>

        <div class="flex gap-3">
            <PrimaryButton :disabled="form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </PrimaryButton>
            <Link :href="route('admin.blocked-slots.index')" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Cancelar
            </Link>
        </div>
    </form>
</template>
