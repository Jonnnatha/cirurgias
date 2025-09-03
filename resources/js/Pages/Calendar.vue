<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';

const roomNumber = ref(1);
const today = new Date();
const startDate = ref(new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10));
const endDate = ref(new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().slice(0, 10));

const surgeries = ref([]);
const loadError = ref(null);

async function fetchReservations() {
    loadError.value = null;
    try {
        const params = {
            room_number: roomNumber.value,
            start_date: startDate.value,
            end_date: endDate.value,
        };

        const response = await axios.get('/calendar', { params });
        surgeries.value = response.data;
    } catch (error) {
        console.error('Failed to fetch surgeries', error);
        loadError.value = 'Não foi possível carregar as cirurgias.';
    }
}

onMounted(fetchReservations);

const events = computed(() =>
    surgeries.value.map((s) => ({
        id: s.id,
        title: s.patient_name,
        start: `${s.date}T${s.start_time}`,
        end: `${s.date}T${s.end_time}`,
        backgroundColor: '#3b82f6',
        borderColor: '#3b82f6',
        extendedProps: { surgery: s },
    }))
);

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin],
    initialView: 'dayGridMonth',
    events: events.value,
}));
</script>

<template>
    <Head title="Calendário de Cirurgias" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Calendário de Cirurgias</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                    <form @submit.prevent="fetchReservations" class="flex flex-wrap items-end gap-4 mb-4">
                        <div>
                            <label for="room" class="block text-sm font-medium text-gray-700">Sala</label>
                            <select id="room" v-model.number="roomNumber" class="mt-1 block border-gray-300 rounded-md shadow-sm">
                                <option v-for="n in 8" :key="n" :value="n">Sala {{ n }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="start" class="block text-sm font-medium text-gray-700">Início</label>
                            <input id="start" type="date" v-model="startDate" class="mt-1 block border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label for="end" class="block text-sm font-medium text-gray-700">Fim</label>
                            <input id="end" type="date" v-model="endDate" class="mt-1 block border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Carregar</button>
                        </div>
                    </form>
                    <p v-if="loadError" class="mt-2 text-sm text-red-600">{{ loadError }}</p>
                    <FullCalendar :options="calendarOptions" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
