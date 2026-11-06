<script setup>
import Layout from "@/Layouts/Layout.vue";
import InputError from "@/Components/InputError.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
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
    <Layout>
        <Head title="Вход" />

        <div class="mx-auto flex w-full max-w-md items-center justify-center py-8 md:py-12">
            <div class="w-full max-w-md rounded-md border border-line bg-surface/90 p-8 shadow-xl shadow-black/5 backdrop-blur sm:p-10">
                <!-- Логотип -->
                <div class="mb-8 text-center">
                    <span
                        class="inline-flex h-12 w-12 items-center justify-center rounded-md bg-gradient-to-br from-accent to-accent-2 text-accent-contrast shadow-lg"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"
                            />
                        </svg>
                    </span>
                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-content dark:text-white">Вход в аккаунт</h1>
                    <p class="mt-1.5 text-sm text-muted">Рады видеть вас снова!</p>
                </div>

                <div v-if="status" class="mb-4 rounded-md bg-accent-soft px-4 py-3 text-sm font-medium text-accent">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Логин -->
                    <div>
                        <label for="username" class="mb-1.5 block text-sm font-medium text-content"> Логин </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                                    />
                                </svg>
                            </span>
                            <input
                                id="username"
                                type="text"
                                class="w-full rounded-md border border-line bg-surface-muted py-2.5 pl-11 pr-3.5 text-sm text-content outline-none transition-all duration-200 focus:border-accent focus:ring-2 focus:ring-accent/20"
                                placeholder="chelny_author"
                                v-model="form.username"
                                required
                                autofocus
                                autocomplete="username"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.username" />
                    </div>

                    <!-- Пароль -->
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-content"> Пароль </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"
                                    />
                                </svg>
                            </span>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full rounded-md border border-line bg-surface-muted py-2.5 pl-11 pr-11 text-sm text-content outline-none transition-all duration-200 focus:border-accent focus:ring-2 focus:ring-accent/20"
                                placeholder="••••••••"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md p-1 text-muted transition-colors hover:text-accent dark:hover:text-accent"
                                :aria-label="showPassword ? 'Скрыть пароль' : 'Показать пароль'"
                                :title="showPassword ? 'Скрыть пароль' : 'Показать пароль'"
                            >
                                <svg
                                    v-if="showPassword"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"
                                    />
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
                                    />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <!-- Чекбокс + забыли пароль -->
                    <div class="flex items-center justify-between">
                        <label class="flex cursor-pointer items-center">
                            <input
                                type="checkbox"
                                name="remember"
                                v-model="form.remember"
                                class="h-4 w-4 rounded border-line-strong text-accent accent-emerald-600 focus:ring-accent"
                            />
                            <span class="ml-2 select-none text-sm text-muted"> Запомнить меня </span>
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm font-medium text-accent transition-colors hover:text-accent dark:hover:text-accent"
                        >
                            Забыли пароль?
                        </Link>
                    </div>

                    <p class="text-xs leading-relaxed text-muted">
                        Восстановление по почте работает, только если вы указали email в профиле. Если почты нет — напишите администратору.
                    </p>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-md bg-accent hover:bg-accent-hover px-5 py-3 text-sm font-semibold text-accent-contrast shadow-md transition-all duration-200 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ form.processing ? "Вход..." : "Войти" }}
                    </button>
                </form>

                <!-- Разделитель -->
                <div class="my-6 flex items-center gap-3">
                    <div class="h-px flex-1 bg-surface-muted dark:bg-surface/10"></div>
                    <span class="text-xs uppercase tracking-wider text-muted">или</span>
                    <div class="h-px flex-1 bg-surface-muted dark:bg-surface/10"></div>
                </div>

                <p class="text-center text-sm text-muted">
                    Ещё нет аккаунта?
                    <Link
                        :href="route('register')"
                        class="font-semibold text-accent transition-colors hover:text-accent dark:hover:text-accent"
                    >
                        Зарегистрироваться
                    </Link>
                </p>
            </div>
        </div>
    </Layout>
</template>
