<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    surgeries: Array,
});
</script>

<template>
    <Head title="Calendário de Cirurgias" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Calendário de Cirurgias</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p>Visualize as cirurgias agendadas aqui.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

