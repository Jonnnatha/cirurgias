<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';

const roomNumber = ref(1);
const today = new Date();
const startDate = ref(new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10));
const endDate = ref(new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().slice(0, 10));

const surgeries = ref([]);
const loadError = ref(null);
const successMessage = ref('');

const statusColors = {
    requested: '#f97316',
    approved: '#16a34a',
};

const showForm = ref(false);
const form = ref({
    date: '',
    start_time: '',
    duration_minutes: '',
    patient_name: '',
    procedure: '',
});
const formErrors = ref({});

async function fetchReservations() {
    loadError.value = null;
    try {
        const params = {
            room_number: roomNumber.value,
            start_date: new Date(startDate.value).toISOString().slice(0, 10),
            end_date: new Date(endDate.value).toISOString().slice(0, 10),
        };

        const response = await axios.get('/calendar', { params });
        surgeries.value = response.data;
    } catch (error) {
        console.error('Failed to fetch surgeries', error);
        loadError.value = error.response?.data?.message || 'Não foi possível carregar as cirurgias.';
    }
}

onMounted(fetchReservations);

const events = computed(() =>
    surgeries.value.map((s) => ({
        id: s.id,
        title: s.patient_name,
        start: `${s.date}T${s.start_time}`,
        end: `${s.date}T${s.end_time}`,
        backgroundColor: statusColors[s.status],
        borderColor: statusColors[s.status],
        extendedProps: { surgery: s },
    }))
);

function handleDateClick(info) {
    info.jsEvent.preventDefault();
    form.value = {
        date: info.dateStr,
        start_time: '',
        duration_minutes: '',
        patient_name: '',
        procedure: '',
    };
    formErrors.value = {};
    showForm.value = true;
}

async function submitRequest() {
    formErrors.value = {};
    if (
        !form.value.start_time ||
        !form.value.duration_minutes ||
        !form.value.patient_name ||
        !form.value.procedure
    ) {
        formErrors.value.general = ['Preencha todos os campos obrigatórios.'];
        return;
    }

    try {
        const start = form.value.start_time;
        const duration = Number(form.value.duration_minutes);
        const [h, m] = start.split(':').map(Number);
        const endDate = new Date(0, 0, 0, h, m + duration);
        const end_time = `${String(endDate.getHours()).padStart(2, '0')}:${String(
            endDate.getMinutes()
        ).padStart(2, '0')}`;

        await axios.post('/surgery-requests', {
            date: form.value.date,
            start_time: start,
            end_time,
            room_number: roomNumber.value,
            duration_minutes: duration,
            patient_name: form.value.patient_name,
            procedure: form.value.procedure,
        });

        successMessage.value = 'Solicitação criada com sucesso!';
        showForm.value = false;
        await fetchReservations();
    } catch (error) {
        if (error.response?.data?.errors) {
            formErrors.value = error.response.data.errors;
        } else {
            formErrors.value = { general: ['Erro ao criar solicitação.'] };
        }
        if (error.response?.data?.message) {
            alert(error.response.data.message);
        }
    }
}

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    events: events.value,
    selectable: true,
    dateClick: handleDateClick,
    locale: ptBrLocale,
    buttonText: { today: 'hoje' },
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
                            <input id="start" type="date" v-model="startDate" lang="pt-BR" class="mt-1 block border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label for="end" class="block text-sm font-medium text-gray-700">Fim</label>
                            <input id="end" type="date" v-model="endDate" lang="pt-BR" class="mt-1 block border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Carregar</button>
                        </div>
                    </form>
                    <p v-if="loadError" class="mt-2 text-sm text-red-600">{{ loadError }}</p>
                    <p v-if="successMessage" class="mt-2 text-sm text-green-600">{{ successMessage }}</p>
                    <FullCalendar :options="calendarOptions" />

                    <div class="flex gap-4 mt-4">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded mr-2" :style="{ backgroundColor: statusColors.requested }"></span>
                            <span>Solicitado</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded mr-2" :style="{ backgroundColor: statusColors.approved }"></span>
                            <span>Aprovado</span>
                        </div>
                    </div>

                    <Teleport to="body">
                        <div v-if="showForm" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
                            <form @submit.prevent="submitRequest" class="bg-white p-6 rounded shadow w-full max-w-md space-y-4">
                                <h3 class="text-lg font-semibold">Nova Solicitação</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data</label>
                                    <input type="date" v-model="form.date" lang="pt-BR" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Início</label>
                                    <input type="time" v-model="form.start_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                                    <p v-if="formErrors.start_time" class="text-sm text-red-600">{{ formErrors.start_time[0] }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Duração (min)</label>
                                    <input type="number" v-model="form.duration_minutes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                                    <p v-if="formErrors.duration_minutes" class="text-sm text-red-600">{{ formErrors.duration_minutes[0] }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Paciente</label>
                                    <input type="text" v-model="form.patient_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                                    <p v-if="formErrors.patient_name" class="text-sm text-red-600">{{ formErrors.patient_name[0] }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Procedimento</label>
                                    <input type="text" v-model="form.procedure" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                                    <p v-if="formErrors.procedure" class="text-sm text-red-600">{{ formErrors.procedure[0] }}</p>
                                </div>
                                <p v-if="formErrors.general" class="text-sm text-red-600">{{ formErrors.general[0] }}</p>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="showForm = false" class="px-4 py-2 bg-gray-200 rounded">Cancelar</button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Salvar</button>
                                </div>
                            </form>
                        </div>
                    </Teleport>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
