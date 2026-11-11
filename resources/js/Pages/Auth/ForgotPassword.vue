<script setup>
import AuthField from "@/Components/AuthField.vue";
import AuthShell from "@/Components/AuthShell.vue";
import { Link, useForm } from "@inertiajs/vue3";

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
    <AuthShell head-title="Восстановление пароля">
        <template #title>Сброс пароля</template>
        <template #subtitle>Ссылка действует ограниченное время.</template>
        <template #footer>
            Вспомнили пароль?
            <Link
                :href="route('login')"
                class="font-semibold text-accent underline underline-offset-[3px] transition-colors hover:text-accent-hover"
            >
                Войти
            </Link>
        </template>

        <p v-if="status" class="mb-5 border-l-2 border-accent bg-surface-muted px-4 py-3 text-[0.9375rem] text-content">
            {{ status }}
        </p>

        <form class="space-y-5" @submit.prevent="submit">
            <AuthField
                id="email"
                v-model="form.email"
                type="email"
                label="Почта"
                autocomplete="username"
                hint="Восстановление работает, только если email указан в профиле. Если почты нет — напишите администратору."
                :error="form.errors.email"
                required
                autofocus
            />

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-accent px-5 py-3 text-[1.0625rem] font-semibold text-accent-contrast transition-colors hover:bg-accent-hover disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ form.processing ? "Отправляем…" : "Отправить ссылку" }}
            </button>
        </form>
    </AuthShell>
</template>
