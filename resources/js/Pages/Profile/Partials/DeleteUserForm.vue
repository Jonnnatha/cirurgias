<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const senhaInput = ref(null);

const form = useForm({
    senha: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => senhaInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => senhaInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">Delete Account</h2>

            <p class="mt-1 text-sm text-gray-600">
                Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting
                your account, please download any data or information that you wish to retain.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Delete Account</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Tem certeza de que deseja excluir sua conta?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Após excluir sua conta, todos os recursos e dados serão removidos permanentemente. Informe sua senha para confirmar que deseja excluir sua conta.
                </p>

                <div class="mt-6">
                    <InputLabel for="senha" value="Senha" class="sr-only" />

                    <TextInput
                        id="senha"
                        ref="senhaInput"
                        v-model="form.senha"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Senha"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.senha" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal"> Cancelar </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Excluir Conta
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
