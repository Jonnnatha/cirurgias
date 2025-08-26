<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
});

const deletingUser = ref(null);
const form = useForm({});

const confirmDelete = (user) => {
    deletingUser.value = user;
};

const closeModal = () => {
    deletingUser.value = null;
};

const deleteUser = () => {
    if (!deletingUser.value) return;
    form.delete(route('admin.users.destroy', deletingUser.value.id), {
        onSuccess: () => closeModal(),
    });
};
</script>

<template>
    <Head title="Usuários" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuários</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4">
                            <Link :href="route('admin.users.create')" class="text-blue-500 underline">Criar novo usuário</Link>
                        </div>
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="px-4 py-2">Nome</th>
                                    <th class="px-4 py-2">Hierarquia</th>
                                    <th class="px-4 py-2">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in props.users" :key="user.id" class="border-b">
                                    <td class="px-4 py-2">{{ user.nome }}</td>
                                    <td class="px-4 py-2 capitalize">{{ user.hierarquia }}</td>
                                    <td class="px-4 py-2 space-x-2">
                                        <Link :href="route('admin.users.edit', user.id)" class="text-blue-500 underline">Editar</Link>
                                        <DangerButton @click="confirmDelete(user)">Excluir</DangerButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="deletingUser !== null" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Tem certeza que deseja excluir este usuário?
                </h2>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Excluir
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
