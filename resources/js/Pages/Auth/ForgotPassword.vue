<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    nome: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Esqueceu a Senha" />

        <div class="mb-4 text-sm text-gray-600">
    Esqueceu sua senha? Sem problemas. Informe seu usuário e enviaremos um link para redefinição.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="nome" value="Usuário" />

                <TextInput
                    id="nome"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.nome"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.nome" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Enviar Link de Redefinição
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
