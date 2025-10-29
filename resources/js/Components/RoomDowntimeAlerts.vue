<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const loading = ref(true);
const error = ref('');
const alerts = ref({ downtimes: [], impacted_surgeries: [] });
const showDowntimeBlock = ref(true);
const showImpactedBlock = ref(true);

function formatDateTime(value) {
    if (!value) return '';
    const date = new Date(value);
    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(date);
}

async function loadAlerts() {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await axios.get('/room-downtimes/alerts');
        alerts.value = {
            downtimes: data.downtimes || [],
            impacted_surgeries: data.impacted_surgeries || [],
        };
    } catch (err) {
        console.error('Falha ao carregar alertas de desativação', err);
        error.value = err.response?.data?.message || 'Não foi possível carregar os alertas de salas desativadas.';
    } finally {
        loading.value = false;
    }
}

onMounted(loadAlerts);
</script>

<template>
    <div class="space-y-3" data-testid="room-downtime-alerts">
        <div v-if="loading" class="rounded border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
            Carregando alertas de salas...
        </div>
        <div v-else-if="error" class="rounded border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            {{ error }}
        </div>
        <template v-else>
            <div
                v-if="showDowntimeBlock && alerts.downtimes.length"
                class="rounded border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold">Salas desativadas</h3>
                        <p class="text-sm text-amber-700">
                            As desativações ativas ou previstas para os próximos 30 dias aparecem aqui.
                        </p>
                        <ul class="mt-2 space-y-1 text-sm">
                            <li v-for="downtime in alerts.downtimes" :key="downtime.id" class="flex flex-wrap gap-x-2">
                                <span class="font-medium">Sala {{ downtime.room_number }}</span>
                                <span>
                                    {{ formatDateTime(downtime.starts_at) }} — {{ formatDateTime(downtime.ends_at) }}
                                </span>
                                <span v-if="downtime.reason" class="text-amber-700">({{ downtime.reason }})</span>
                                <span v-if="downtime.active" class="rounded bg-amber-600 px-2 py-0.5 text-xs text-white">
                                    Em andamento
                                </span>
                            </li>
                        </ul>
                    </div>
                    <button
                        type="button"
                        @click="showDowntimeBlock = false"
                        class="text-sm text-amber-700 hover:underline"
                    >
                        Fechar
                    </button>
                </div>
            </div>

            <div
                v-if="showImpactedBlock && alerts.impacted_surgeries.length"
                class="rounded border border-red-200 bg-red-50 px-4 py-3 text-red-800"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold">Cirurgias impactadas</h3>
                        <p class="text-sm text-red-700">
                            Remarque as cirurgias abaixo para retirar o aviso.
                        </p>
                        <ul class="mt-2 space-y-1 text-sm">
                            <li v-for="impact in alerts.impacted_surgeries" :key="impact.id" class="flex flex-col gap-1">
                                <span class="font-medium">
                                    {{ impact.patient_name }} — Sala {{ impact.room_number }}
                                </span>
                                <span>
                                    {{ impact.date }} às {{ impact.start_time?.slice(0, 5) }}
                                </span>
                                <span class="text-red-700">{{ impact.reason }}</span>
                            </li>
                        </ul>
                    </div>
                    <button
                        type="button"
                        @click="showImpactedBlock = false"
                        class="text-sm text-red-700 hover:underline"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>
