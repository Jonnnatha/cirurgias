<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    hierarquia: '',
    nome: '',
    senha: '',
    confirmacao_senha: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('senha', 'confirmacao_senha'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="hierarquia" value="Hierarquia" />

                <TextInput
                    id="hierarquia"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.hierarquia"
                    required
                    autofocus
                    autocomplete="hierarquia"
                />

                <InputError class="mt-2" :message="form.errors.hierarquia" />
            </div>

            <div class="mt-4">
                <InputLabel for="nome" value="Nome" />

                <TextInput
                    id="nome"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.nome"
                    required
                    autocomplete="nome"
                />

                <InputError class="mt-2" :message="form.errors.nome" />
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
                <InputLabel for="confirmacao_senha" value="Confirmação de Senha" />

                <TextInput
                    id="confirmacao_senha"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.confirmacao_senha"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.confirmacao_senha" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
