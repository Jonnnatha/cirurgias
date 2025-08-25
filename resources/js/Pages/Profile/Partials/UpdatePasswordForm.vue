<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const senhaInput = ref(null);
const senhaAtualInput = ref(null);

const form = useForm({
    senha_atual: '',
    senha: '',
    senha_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.senha) {
                form.reset('senha', 'senha_confirmation');
                senhaInput.value.focus();
            }
            if (form.errors.senha_atual) {
                form.reset('senha_atual');
                senhaAtualInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Atualizar Senha</h2>

            <p class="mt-1 text-sm text-gray-600">
                Garanta que sua conta use uma senha longa e aleatória para permanecer segura.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <InputLabel for="senha_atual" value="Senha Atual" />

                <TextInput
                    id="senha_atual"
                    ref="senhaAtualInput"
                    v-model="form.senha_atual"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                />

                <InputError :message="form.errors.senha_atual" class="mt-2" />
            </div>

            <div>
                <InputLabel for="senha" value="Nova Senha" />

                <TextInput
                    id="senha"
                    ref="senhaInput"
                    v-model="form.senha"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.senha" class="mt-2" />
            </div>

            <div>
                <InputLabel for="senha_confirmation" value="Confirmar Senha" />

                <TextInput
                    id="senha_confirmation"
                    v-model="form.senha_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.senha_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Salvar</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
