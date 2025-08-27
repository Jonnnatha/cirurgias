<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const roomNumber = ref(1);
const today = new Date().toISOString().slice(0,10);
const startDate = ref(today);
const endDate = ref(today);
const surgeries = ref([]);

async function fetchSurgeries() {
    const { data } = await axios.get('/calendar', {
        params: {
            room_number: roomNumber.value,
            start_date: startDate.value,
            end_date: endDate.value,
        },
    });
    surgeries.value = data;
}

watch([roomNumber, startDate, endDate], fetchSurgeries, { immediate: true });

const days = computed(() => {
    const start = new Date(startDate.value);
    const end = new Date(endDate.value);
    const list = [];
    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        const dateStr = d.toISOString().slice(0, 10);
        const slots = [];
        for (let h = 8; h < 18; h++) {
            const time = String(h).padStart(2, '0') + ':00';
            const booked = surgeries.value.some(
                (s) =>
                    s.date === dateStr &&
                    h >= parseInt(s.start_time.slice(0, 2)) &&
                    h < parseInt(s.end_time.slice(0, 2))
            );
            slots.push({ time, booked });
        }
        list.push({ date: dateStr, slots });
    }
    return list;
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                    <div class="mb-4 flex gap-4">
                        <div>
                            <label class="block text-sm font-medium">Sala</label>
                            <select v-model.number="roomNumber" class="border rounded p-1">
                                <option v-for="n in 9" :key="n" :value="n">{{ n }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Início</label>
                            <input type="date" v-model="startDate" class="border rounded p-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Fim</label>
                            <input type="date" v-model="endDate" class="border rounded p-1" />
                        </div>
                    </div>

                    <div v-for="day in days" :key="day.date" class="mb-6">
                        <h3 class="font-semibold mb-2">{{ day.date }}</h3>
                        <div class="grid grid-cols-10 gap-2">
                            <div
                                v-for="slot in day.slots"
                                :key="slot.time"
                                class="p-2 text-center text-xs"
                                :class="slot.booked ? 'bg-red-500 text-white' : 'bg-green-200'"
                            >
                                {{ slot.time }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

