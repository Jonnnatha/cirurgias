<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

const form = useForm({
    nome: props.user.nome,
    hierarquia: props.user.hierarquia,
    senha: '',
    senha_confirmation: '',
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id), {
        onFinish: () => form.reset('senha', 'senha_confirmation'),
    });
};
</script>

<template>
    <Head title="Editar Usuário" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Usuário</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <InputLabel for="nome" value="Nome" />
                                <TextInput id="nome" v-model="form.nome" type="text" class="mt-1 block w-full" autofocus />
                                <InputError class="mt-2" :message="form.errors.nome" />
                            </div>

                            <div>
                                <InputLabel for="hierarquia" value="Hierarquia" />
                                <select id="hierarquia" v-model="form.hierarquia" class="mt-1 block w-full">
                                    <option value="" disabled>Selecione</option>
                                    <option value="enfermeiro">Enfermeiro</option>
                                    <option value="medico">Médico</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.hierarquia" />
                            </div>

                            <div>
                                <InputLabel for="senha" value="Senha" />
                                <TextInput id="senha" v-model="form.senha" type="password" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.senha" />
                            </div>

                            <div>
                                <InputLabel for="senha_confirmation" value="Confirmar Senha" />
                                <TextInput id="senha_confirmation" v-model="form.senha_confirmation" type="password" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.senha_confirmation" />
                            </div>

                            <div>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Salvar
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
