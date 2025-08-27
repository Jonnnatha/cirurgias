<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    request: Object,
});

const form = useForm({
    date: props.request?.date ?? '',
    start_time: props.request?.start_time ?? '',
    end_time: props.request?.end_time ?? '',
    duration_minutes: props.request?.meta?.duration_minutes ?? '',
    room_number: props.request?.meta?.room_number ?? '',
    patient_name: props.request?.patient_name ?? '',
    procedure: props.request?.procedure ?? '',
    confirm_docs: props.request?.meta?.confirm_docs ?? false,
});

const endTime = computed(() => {
    if (!form.start_time || !form.duration_minutes) return '';
    const [h, m] = form.start_time.split(':').map(Number);
    const total = h * 60 + m + Number(form.duration_minutes);
    const endH = String(Math.floor(total / 60) % 24).padStart(2, '0');
    const endM = String(total % 60).padStart(2, '0');
    return `${endH}:${endM}`;
});

const submit = () => {
    form.end_time = endTime.value;
    if (props.request) {
        form.put(route('surgery-requests.update', props.request.id));
    } else {
        form.post(route('surgery-requests.store'));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="props.request ? 'Editar Solicitação' : 'Nova Solicitação'" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <InputLabel for="date" value="Data" />
                                <TextInput id="date" type="date" class="mt-1 block w-full" v-model="form.date" required />
                                <InputError class="mt-2" :message="form.errors.date" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <InputLabel for="start_time" value="Início" />
                                    <TextInput id="start_time" type="time" class="mt-1 block w-full" v-model="form.start_time" required />
                                    <InputError class="mt-2" :message="form.errors.start_time" />
                                </div>
                                <div>
                                    <InputLabel for="duration_minutes" value="Duração (min)" />
                                    <TextInput id="duration_minutes" type="number" min="1" class="mt-1 block w-full" v-model="form.duration_minutes" required />
                                    <InputError class="mt-2" :message="form.errors.duration_minutes" />
                                </div>
                                <div>
                                    <InputLabel for="end_time" value="Término" />
                                    <TextInput id="end_time" type="time" class="mt-1 block w-full" :value="endTime" readonly />
                                    <InputError class="mt-2" :message="form.errors.end_time" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="room_number" value="Sala" />
                                <select id="room_number" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" v-model="form.room_number" required>
                                    <option value="" disabled>Selecione</option>
                                    <option v-for="n in 9" :key="n" :value="n">Sala {{ n }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.room_number" />
                            </div>

                            <div>
                                <InputLabel for="patient_name" value="Paciente" />
                                <TextInput id="patient_name" type="text" class="mt-1 block w-full" v-model="form.patient_name" required />
                                <InputError class="mt-2" :message="form.errors.patient_name" />
                            </div>

                            <div>
                                <InputLabel for="procedure" value="Procedimento" />
                                <TextInput id="procedure" type="text" class="mt-1 block w-full" v-model="form.procedure" required />
                                <InputError class="mt-2" :message="form.errors.procedure" />
                            </div>

                            <div class="flex items-center">
                                <Checkbox id="confirm_docs" v-model:checked="form.confirm_docs" />
                                <InputLabel for="confirm_docs" value="Documentos conferidos" class="ms-2" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.confirm_docs" />

                            <div>
                                <PrimaryButton :disabled="form.processing">
                                    {{ props.request ? 'Atualizar' : 'Salvar' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

