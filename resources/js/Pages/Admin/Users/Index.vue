<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
});

const destroyUser = (id) => {
    if (confirm('Tem certeza que deseja excluir este usuário?')) {
        router.delete(route('admin.users.destroy', id));
    }
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
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Nome</th>
                                    <th class="px-4 py-2 text-left">Hierarquia</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users" :key="user.id" class="border-t">
                                    <td class="px-4 py-2">{{ user.nome }}</td>
                                    <td class="px-4 py-2">{{ user.hierarquia }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <Link :href="route('admin.users.edit', user.id)" class="text-blue-500 mr-2">Editar</Link>
                                        <button @click="destroyUser(user.id)" class="text-red-500">Excluir</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

