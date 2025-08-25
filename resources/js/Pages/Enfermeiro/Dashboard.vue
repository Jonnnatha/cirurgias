<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});
</script>

<template>
    <Head title="Dashboard Enfermeiro" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Enfermeiro</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Bem-vindo, enfermeiro {{ props.user.name }}! Acompanhe os pedidos de cirurgia.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

