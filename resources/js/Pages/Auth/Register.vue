<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nome: '',
    email: '',
    senha: '',
    senha_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('senha', 'senha_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registrar" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="nome" value="Nome" />

                <TextInput
                    id="nome"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.nome"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.nome" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="senha" value="Senha" />

                <TextInput
                    id="senha"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.senha"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.senha" />
            </div>

            <div class="mt-4">
                <InputLabel for="senha_confirmation" value="Confirmar Senha" />

                <TextInput
                    id="senha_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.senha_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.senha_confirmation" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Já registrado?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Registrar
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
