<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nome: '',
    hierarquia: '',
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
        <div class="max-w-md mx-auto">
            <form
                @submit.prevent="submit"
                class="bg-white p-8 rounded-lg shadow-lg flex flex-col gap-4"
            >
                <h1 class="text-2xl font-semibold text-center text-gray-800">
                    Criar conta
                </h1>

                <div class="flex flex-col gap-1">
                    <InputLabel for="nome" value="Nome" />

                    <TextInput
                        id="nome"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.nome"
                        required
                        autofocus
                        autocomplete="name"
                    />

                    <InputError class="text-sm" :message="form.errors.nome" />
                </div>

                <div class="flex flex-col gap-1">
                    <InputLabel for="hierarquia" value="Hierarquia" />

                    <TextInput
                        id="hierarquia"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.hierarquia"
                        required
                    />

                    <InputError class="text-sm" :message="form.errors.hierarquia" />
                </div>

                <div class="flex flex-col gap-1">
                    <InputLabel for="senha" value="Senha" />

                    <TextInput
                        id="senha"
                        type="password"
                        class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.senha"
                        required
                        autocomplete="new-password"
                    />

                    <InputError class="text-sm" :message="form.errors.senha" />
                </div>

                <div class="flex flex-col gap-1">
                    <InputLabel
                        for="senha_confirmation"
                        value="Confirmar Senha"
                    />

                    <TextInput
                        id="senha_confirmation"
                        type="password"
                        class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.senha_confirmation"
                        required
                        autocomplete="new-password"
                    />

                    <InputError class="text-sm" :message="form.errors.senha_confirmation" />
                </div>

                <div class="flex items-center justify-between pt-4">
                    <Link
                        :href="route('login')"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Já registrado?
                    </Link>

                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Registrar
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
