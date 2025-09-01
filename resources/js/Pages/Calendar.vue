<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

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
    const { data } = await axios.get('/calendar');
    reservations.value = data;
}

onMounted(fetchReservations);

const events = computed(() =>
    reservations.value.map((r) => ({
        id: r.id,
        title: 'Confirmado',
        start: r.date,
        allDay: true,
        backgroundColor: '#ef4444',
        borderColor: '#ef4444',
        extendedProps: { reservation: r },
    }))
);

async function handleDateSelect(selectionInfo) {
    if (!hasRole('medico')) return;
    await axios.post('/calendar', { date: selectionInfo.startStr });
    await fetchReservations();
}

async function handleEventClick(clickInfo) {
    const reservation = clickInfo.event.extendedProps.reservation;
    if (canCancel(reservation)) {
        if (confirm('Cancelar reserva?')) {
            await axios.delete(`/calendar/${reservation.id}`);
            await fetchReservations();
        }
    }
}

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    selectable: hasRole('medico'),
    select: handleDateSelect,
    events: events.value,
    eventClick: handleEventClick,
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
                    <FullCalendar :options="calendarOptions" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

