<script setup>
import AuthField from "@/Components/AuthField.vue";
import AuthShell from "@/Components/AuthShell.vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    password: "",
});

const submit = () => {
    form.post(route("password.confirm"), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AuthShell head-title="Подтверждение пароля">
        <template #title>Подтвердите пароль</template>
        <template #subtitle>Это защищённый раздел. Введите пароль, чтобы продолжить.</template>

        <form class="space-y-5" @submit.prevent="submit">
            <AuthField
                id="password"
                v-model="form.password"
                type="password"
                label="Пароль"
                autocomplete="current-password"
                :error="form.errors.password"
                required
                autofocus
            />

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-accent px-5 py-3 text-[1.0625rem] font-semibold text-accent-contrast transition-colors hover:bg-accent-hover disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ form.processing ? "Проверяем…" : "Подтвердить" }}
            </button>
        </form>
    </AuthShell>
</template>
