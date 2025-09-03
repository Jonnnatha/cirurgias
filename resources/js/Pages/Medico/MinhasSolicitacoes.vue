<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    requests: Object,
});

const statusClass = (status) => ({
    requested: 'text-blue-600',
    approved: 'text-green-600',
    rejected: 'text-red-600',
    cancelled: 'text-gray-600',
}[status] || '');

function cancel(id) {
    if (confirm('Cancelar esta solicitação?')) {
        router.delete(route('surgery-requests.destroy', id));
    }
}
</script>

<template>
    <Head title="Minhas Solicitações" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Minhas Solicitações</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="req in requests.data" :key="req.id" class="border-t">
                                <td class="px-3 py-2">{{ req.patient_name }}</td>
                                <td class="px-3 py-2">{{ req.date }}</td>
                                <td class="px-3 py-2" :class="statusClass(req.status)">{{ req.status }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button v-if="req.can_cancel" @click="cancel(req.id)" class="text-red-600 hover:underline">Cancelar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

