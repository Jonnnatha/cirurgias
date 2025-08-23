<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    checklist: Object,
});

const form = useForm({
    title: props.checklist.title,
    active: props.checklist.active,
});

const submit = () => {
    form.put(route('checklists.update', props.checklist.id));
};
</script>

<template>
    <Head :title="'Editar ' + props.checklist.title" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Checklist</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <InputLabel for="title" value="Título" />
                                <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" />
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <Checkbox v-model:checked="form.active" />
                                    <span class="ms-2 text-sm text-gray-600">Ativo</span>
                                </label>
                            </div>

                            <div>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Atualizar
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
