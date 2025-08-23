<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    checklists: Array,
});

const toggle = (checklist) => {
    router.patch(route('checklists.update', checklist.id), { active: !checklist.active });
};
</script>

<template>
    <Head title="Checklists" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Checklists</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4 flex justify-end">
                            <Link :href="route('checklists.create')">
                                <PrimaryButton>Nova checklist</PrimaryButton>
                            </Link>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Título</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-sm font-medium text-gray-500">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="checklist in checklists" :key="checklist.id">
                                    <td class="px-4 py-2">{{ checklist.title }}</td>
                                    <td class="px-4 py-2">{{ checklist.active ? 'Ativo' : 'Inativo' }}</td>
                                    <td class="px-4 py-2 flex space-x-2">
                                        <PrimaryButton @click="toggle(checklist)">
                                            {{ checklist.active ? 'Desativar' : 'Ativar' }}
                                        </PrimaryButton>
                                        <Link :href="route('checklists.edit', checklist.id)">
                                            <PrimaryButton>Editar</PrimaryButton>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
