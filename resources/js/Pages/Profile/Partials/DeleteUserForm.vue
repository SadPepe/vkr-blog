<script setup>
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import { nextTick, ref } from "vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route("profile.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div>
        <h2 class="text-lg font-semibold text-content">Удаление аккаунта</h2>
        <p class="mt-1 text-sm text-muted">
            После удаления аккаунта все ваши статьи комментарии исчезнут безвозвратно.
        </p>

        <DangerButton class="mt-4" @click="confirmUserDeletion"> Удалить аккаунт </DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-content">Удалить аккаунт?</h2>

                <p class="mt-2 text-sm text-muted">
                    Действие необратимо. Введите пароль, чтобы подтвердить удаление аккаунта.
                </p>

                <div class="mt-5">
                    <InputLabel for="delete-password" value="Пароль" class="sr-only" />

                    <TextInput
                        id="delete-password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1.5 block w-full"
                        placeholder="Пароль"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-2.5">
                    <SecondaryButton @click="closeModal"> Отмена </SecondaryButton>

                    <DangerButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing" @click="deleteUser">
                        Удалить аккаунт
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </div>
</template>
