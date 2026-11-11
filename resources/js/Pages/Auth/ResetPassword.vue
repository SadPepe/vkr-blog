<script setup>
import AuthField from "@/Components/AuthField.vue";
import AuthShell from "@/Components/AuthShell.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const showPassword = ref(false);

const submit = () => {
    form.post(route("password.store"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <AuthShell head-title="Новый пароль">
        <template #title>Новый пароль</template>
        <template #subtitle>Почта уже подставлена — осталось задать пароль.</template>

        <form class="space-y-5" @submit.prevent="submit">
            <AuthField
                id="email"
                v-model="form.email"
                type="email"
                label="Почта"
                autocomplete="username"
                :error="form.errors.email"
                required
                autofocus
            />

            <AuthField
                id="password"
                v-model="form.password"
                label="Пароль"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                hint="Минимум 8 символов."
                :error="form.errors.password"
                required
            >
                <template #aside>
                    <button
                        type="button"
                        class="text-[0.8125rem] text-accent transition-colors hover:text-accent-hover"
                        :aria-pressed="showPassword"
                        @click="showPassword = !showPassword"
                    >
                        {{ showPassword ? "Скрыть" : "Показать" }}
                    </button>
                </template>
            </AuthField>

            <AuthField
                id="password_confirmation"
                v-model="form.password_confirmation"
                label="Подтверждение пароля"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                :error="form.errors.password_confirmation"
                required
            />

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-accent px-5 py-3 text-[1.0625rem] font-semibold text-accent-contrast transition-colors hover:bg-accent-hover disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ form.processing ? "Сохраняем…" : "Сохранить пароль" }}
            </button>
        </form>
    </AuthShell>
</template>
