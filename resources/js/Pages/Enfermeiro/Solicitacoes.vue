<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    requests: Object,
    filters: Object,
});

const params = reactive({
    status: props.filters.status || '',
    room_number: props.filters.room_number || '',
});

function applyFilters() {
    router.get(route('surgery-requests.index'), params, {
        preserveState: true,
        replace: true,
    });
}

function cancel(id) {
    if (confirm('Cancelar esta solicitação?')) {
        router.delete(route('surgery-requests.destroy', id));
    }
}
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
                    <div class="mb-4 flex gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select v-model="params.status" @change="applyFilters" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Todos</option>
                                <option value="requested">Solicitado</option>
                                <option value="approved">Aprovado</option>
                                <option value="rejected">Rejeitado</option>
                                <option value="cancelled">Cancelado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sala</label>
                            <select v-model="params.room_number" @change="applyFilters" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Todas</option>
                                <option v-for="n in 9" :key="n" :value="n">{{ n }}</option>
                            </select>
                        </div>
                    </div>
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
                                <td class="px-3 py-2">{{ req.status }}</td>
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

