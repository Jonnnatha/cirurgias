<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});
</script>

<template>
    <Head title="Detalhes do Usuário" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalhes do Usuário</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p><strong>Nome:</strong> {{ user.nome }}</p>
                        <p class="mt-2 capitalize"><strong>Hierarquia:</strong> {{ user.hierarquia }}</p>
                        <div class="mt-4">
                            <Link :href="route('admin.users.index')" class="text-blue-500 underline">Voltar</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
