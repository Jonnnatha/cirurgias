<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    requests: Object,
    filters: Object,
});

const room = ref(props.filters.room ?? '');

function filter() {
    const params = room.value ? { room: room.value } : {};
    router.get(route('surgery-requests.index', params));
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
                    <form @submit.prevent="filter" class="mb-4 flex items-center">
                        <label for="room" class="mr-2 text-sm font-medium text-gray-700">Sala</label>
                        <select id="room" v-model="room" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Todas</option>
                            <option v-for="n in 9" :key="n" :value="n">{{ n }}</option>
                        </select>
                        <button type="submit" class="ms-2 px-3 py-2 bg-indigo-600 text-white rounded-md text-sm">Filtrar</button>
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

