<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    request: Object,
});

const form = useForm({
    date: props.request?.date ?? '',
    start_time: props.request?.start_time ?? '',
    end_time: props.request?.end_time ?? '',
    patient_name: props.request?.patient_name ?? '',
    procedure: props.request?.procedure ?? '',
    confirm_docs: props.request?.meta?.confirm_docs ?? false,
});

const submit = () => {
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

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="start_time" value="Início" />
                                    <TextInput id="start_time" type="time" class="mt-1 block w-full" v-model="form.start_time" required />
                                    <InputError class="mt-2" :message="form.errors.start_time" />
                                </div>
                                <div>
                                    <InputLabel for="end_time" value="Término" />
                                    <TextInput id="end_time" type="time" class="mt-1 block w-full" v-model="form.end_time" required />
                                    <InputError class="mt-2" :message="form.errors.end_time" />
                                </div>
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

