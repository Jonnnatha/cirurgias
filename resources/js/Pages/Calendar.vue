<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

// TODO: considerar substituição desta implementação por uma biblioteca como
// FullCalendar para melhorar a aparência e responsividade do calendário.

const today = new Date().toISOString().slice(0, 10);
const startDate = ref(today);
const endDate = ref(today);
const reservations = ref([]);

const page = usePage();
const user = computed(() => page.props.auth.user);

const hasRole = (role) => {
    const u = user.value;
    return u.roles ? u.roles.some((r) => r.name === role) : u.hierarquia === role;
};

const canCancel = (reservation) => {
    return hasRole('admin') || reservation.doctor_id === user.value.id;
};

async function fetchReservations() {
    const { data } = await axios.get('/calendar', {
        params: {
            start_date: startDate.value,
            end_date: endDate.value,
        },
    });
    reservations.value = data;
}

watch([startDate, endDate], fetchReservations, { immediate: true });

const days = computed(() => {
    const start = new Date(startDate.value);
    const end = new Date(endDate.value);
    const list = [];
    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        const dateStr = d.toISOString().slice(0, 10);
        const reservation = reservations.value.find((r) => r.date === dateStr);
        list.push({ date: dateStr, reservation });
    }
    return list;
});

async function createReservation(date) {
    await axios.post('/calendar', { date });
    await fetchReservations();
}

async function confirmReservation(id) {
    await axios.post(`/calendar/${id}/confirm`);
    await fetchReservations();
}

async function cancelReservation(id) {
    await axios.delete(`/calendar/${id}`);
    await fetchReservations();
}
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
                    <div class="mb-4 flex gap-4">
                        <div>
                            <label class="block text-sm font-medium">Início</label>
                            <input type="date" v-model="startDate" class="border rounded p-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Fim</label>
                            <input type="date" v-model="endDate" class="border rounded p-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                        <div
                            v-for="day in days"
                            :key="day.date"
                            class="p-4 text-center rounded"
                            :class="[
                                !day.reservation && 'bg-green-200 cursor-pointer',
                                day.reservation?.status === 'pending' && 'bg-orange-400 text-white',
                                day.reservation?.status === 'confirmed' && 'bg-red-500 text-white',
                            ]"
                            @click="!day.reservation && createReservation(day.date)"
                        >
                            <div class="font-semibold">{{ day.date }}</div>
                            <div v-if="day.reservation" class="mt-2 flex flex-col gap-1">
                                <button
                                    v-if="day.reservation.status === 'pending' && hasRole('enfermeiro')"
                                    class="bg-blue-500 text-white px-1 py-0.5 rounded text-[10px]"
                                    @click.stop="confirmReservation(day.reservation.id)"
                                >
                                    Confirmar
                                </button>
                                <button
                                    v-if="canCancel(day.reservation)"
                                    class="bg-gray-500 text-white px-1 py-0.5 rounded text-[10px]"
                                    @click.stop="cancelReservation(day.reservation.id)"
                                >
                                    Desmarcar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

