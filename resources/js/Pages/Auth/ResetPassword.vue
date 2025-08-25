<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    senha: '',
    senha_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('senha', 'senha_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Redefinir Senha" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
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
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Redefinir Senha
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
