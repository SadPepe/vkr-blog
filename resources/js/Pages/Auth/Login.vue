<script setup>
import AuthField from "@/Components/AuthField.vue";
import AuthShell from "@/Components/AuthShell.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    username: "",
    password: "",
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <AuthShell head-title="Вход">
        <template #title>Вход в аккаунт</template>
        <template #subtitle>Рады видеть вас снова.</template>
        <template #footer>
            Ещё нет аккаунта?
            <Link
                :href="route('register')"
                class="font-semibold text-accent underline underline-offset-[3px] transition-colors hover:text-accent-hover"
            >
                Зарегистрироваться
            </Link>
        </template>

        <p v-if="status" class="mb-5 border-l-2 border-accent bg-surface-muted px-4 py-3 text-[0.9375rem] text-content">
            {{ status }}
        </p>

        <form class="space-y-5" @submit.prevent="submit">
            <AuthField
                id="username"
                v-model="form.username"
                label="Логин"
                autocomplete="username"
                :error="form.errors.username"
                required
                autofocus
                spellcheck="false"
            />

            <AuthField
                id="password"
                v-model="form.password"
                label="Пароль"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
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

            <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2">
                <label class="flex cursor-pointer items-center gap-2.5 text-[0.9375rem] text-content">
                    <input v-model="form.remember" type="checkbox" name="remember" class="h-4 w-4 border-line-strong accent-accent" />
                    Запомнить меня
                </label>
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-[0.9375rem] text-accent underline underline-offset-[3px] transition-colors hover:text-accent-hover"
                >
                    Забыли пароль?
                </Link>
            </div>

            <p class="text-[0.8125rem] leading-relaxed text-muted">
                Восстановление по почте работает, только если email указан в профиле. Если почты нет — напишите администратору.
            </p>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-accent px-5 py-3 text-[1.0625rem] font-semibold text-accent-contrast transition-colors hover:bg-accent-hover disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ form.processing ? "Вход…" : "Войти" }}
            </button>
        </form>
    </AuthShell>
</template>
