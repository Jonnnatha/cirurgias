<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RoomDowntimeAlerts from '@/Components/RoomDowntimeAlerts.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    requests: Object,
    filters: Object,
});

const filterForm = reactive({
    status: props.filters.status || '',
    room: props.filters.room || '',
});

function applyFilters() {
    router.get(route('surgery-requests.index'), filterForm, {
        preserveState: true,
        replace: true,
    });
}

function cancel(id) {
    if (confirm('Cancelar esta solicitação?')) {
        router.delete(route('surgery-requests.destroy', id));
    }
}

const statusClass = (status) => ({
    requested: 'text-blue-600',
    approved: 'text-green-600',
    rejected: 'text-red-600',
    cancelled: 'text-gray-600',
}[status] || '');
</script>

<template>
    <Head title="Solicitações" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Solicitações</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <RoomDowntimeAlerts class="mb-6" />
                    <form @submit.prevent="applyFilters" class="mb-4 flex space-x-2">
                        <input
                            type="text"
                            v-model="filterForm.room"
                            placeholder="Sala"
                            class="border rounded px-2 py-1"
                        />
                        <select v-model="filterForm.status" class="border rounded px-2 py-1">
                            <option value="">Todos</option>
                            <option value="requested">Solicitado</option>
                            <option value="approved">Aprovado</option>
                            <option value="rejected">Rejeitado</option>
                        </select>
                        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded">Filtrar</button>
                    </form>
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

