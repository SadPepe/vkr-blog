<script setup>
import GuestLayout from "@/Layouts/Layout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <GuestLayout>
        <div class="mx-auto mt-4 px-4 w-full max-w-[550px] flex flex-col items-center justify-center">
            <Head title="Восстановление пароля" />

            <div class="mb-4 text-sm text-muted">
                Забыли пароль? Укажите почту, и мы отправим ссылку для сброса — по ней вы зададите новый пароль.
            </div>

            <div v-if="status" class="mb-4 text-sm font-medium text-accent">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Почта" />

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4 flex items-center justify-center">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing"> Отправить ссылку </PrimaryButton>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
