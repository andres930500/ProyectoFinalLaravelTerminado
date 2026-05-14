<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Restablecer contrasena" />

    <div class="relative min-h-screen overflow-hidden bg-[#08120d]">
        <div
            class="absolute inset-0 bg-cover bg-center"
            style="background-image: url('/images/banners/FondoBanner.png');"
        ></div>
        <div class="absolute inset-0 bg-[linear-gradient(120deg,rgba(3,14,10,0.9)_0%,rgba(4,22,14,0.72)_42%,rgba(3,14,10,0.88)_100%)]"></div>
        <div class="absolute inset-x-0 top-0 h-36 bg-[linear-gradient(180deg,rgba(2,8,6,0.55)_0%,rgba(2,8,6,0)_100%)]"></div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8">
            <header class="flex items-center">
                <Link href="/" class="inline-flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white ring-1 ring-emerald-200">
                        <img src="/images/ui/soccer-ball-logo.png" alt="ReservaCancha" class="h-8 w-8 object-contain">
                    </span>
                    <span class="flex flex-col leading-tight">
                        <span class="text-sm font-semibold text-white">ReservaCancha</span>
                        <span class="text-[11px] text-emerald-100/75">Actualizacion segura de contrasena</span>
                    </span>
                </Link>
            </header>

            <div class="flex flex-1 items-center py-10 lg:py-14">
                <div class="grid w-full gap-8 lg:grid-cols-[minmax(0,1.05fr)_minmax(420px,0.95fr)] lg:items-center">
                    <section class="max-w-2xl">
                        <p class="text-[11px] font-medium uppercase tracking-[0.35em] text-emerald-300">Restablecimiento</p>
                        <h1 class="mt-5 text-4xl font-black uppercase leading-[0.95] tracking-tight text-white md:text-5xl xl:text-6xl">
                            Define una nueva contrasena para volver al control.
                        </h1>
                        <p class="mt-5 max-w-xl text-sm leading-7 text-white md:text-base">
                            Completa el formulario con tu correo y una nueva contrasena segura para recuperar el acceso al panel administrativo.
                        </p>

                        <div class="mt-8 grid max-w-xl gap-4 sm:grid-cols-3">
                            <div class="rounded-[1.4rem] border border-white/12 bg-white/10 px-5 py-4 backdrop-blur-md">
                                <div class="text-[11px] uppercase tracking-[0.22em] text-emerald-200/80">Verificado</div>
                                <div class="mt-2 text-lg font-semibold text-white">Token</div>
                                <div class="mt-1 text-xs text-emerald-50/70">Llegaste desde un enlace seguro</div>
                            </div>
                            <div class="rounded-[1.4rem] border border-white/12 bg-white/10 px-5 py-4 backdrop-blur-md">
                                <div class="text-[11px] uppercase tracking-[0.22em] text-emerald-200/80">Proteccion</div>
                                <div class="mt-2 text-lg font-semibold text-white">Nueva clave</div>
                                <div class="mt-1 text-xs text-emerald-50/70">Actualiza tus credenciales</div>
                            </div>
                            <div class="rounded-[1.4rem] border border-white/12 bg-white/10 px-5 py-4 backdrop-blur-md">
                                <div class="text-[11px] uppercase tracking-[0.22em] text-emerald-200/80">Acceso</div>
                                <div class="mt-2 text-lg font-semibold text-white">Panel</div>
                                <div class="mt-1 text-xs text-emerald-50/70">Vuelve a gestionar reservas</div>
                            </div>
                        </div>
                    </section>

                    <section class="lg:justify-self-end">
                        <div class="w-full max-w-xl rounded-[2rem] border border-white/15 bg-white/12 p-3 shadow-[0_30px_90px_rgba(0,0,0,0.32)] backdrop-blur-xl">
                            <div class="rounded-[1.65rem] bg-white p-7 shadow-inner shadow-emerald-100/50 sm:p-8">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white ring-1 ring-emerald-200">
                                        <img src="/images/ui/soccer-ball-logo.png" alt="ReservaCancha" class="h-9 w-9 object-contain">
                                    </span>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-950">Restablecer contrasena</div>
                                        <div class="text-xs text-slate-500">Ingresa tu nueva clave para terminar el proceso</div>
                                    </div>
                                </div>

                                <form class="mt-6 space-y-5" @submit.prevent="submit">
                                    <div>
                                        <InputLabel for="email" value="Correo electronico" />
                                        <TextInput
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50/80"
                                            required
                                            autofocus
                                            autocomplete="username"
                                        />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>

                                    <div>
                                        <InputLabel for="password" value="Nueva contrasena" />
                                        <TextInput
                                            id="password"
                                            v-model="form.password"
                                            type="password"
                                            class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50/80"
                                            required
                                            autocomplete="new-password"
                                        />
                                        <InputError class="mt-2" :message="form.errors.password" />
                                    </div>

                                    <div>
                                        <InputLabel for="password_confirmation" value="Confirmar contrasena" />
                                        <TextInput
                                            id="password_confirmation"
                                            v-model="form.password_confirmation"
                                            type="password"
                                            class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50/80"
                                            required
                                            autocomplete="new-password"
                                        />
                                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                                    </div>

                                    <PrimaryButton
                                        class="w-full justify-center rounded-2xl bg-[#00C853] px-4 py-3 text-sm font-semibold text-black hover:bg-[#00b84c]"
                                        :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing"
                                    >
                                        {{ form.processing ? 'Actualizando contrasena...' : 'Guardar nueva contrasena' }}
                                    </PrimaryButton>

                                    <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-4 text-sm">
                                        <Link :href="route('login')" class="font-medium text-slate-600 transition hover:text-emerald-700">
                                            Volver al login
                                        </Link>
                                        <span class="text-xs text-slate-400">Recuperacion segura</span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>
