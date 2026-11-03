<script setup>
import Layout from "@/Layouts/Layout.vue";
import InputError from "@/Components/InputError.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const showPassword = ref(false);

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Layout>
        <Head title="Регистрация" />

        <div class="mx-auto flex w-full max-w-md items-center justify-center py-8 md:py-12">
            <div
                class="w-full max-w-md rounded-md border border-line bg-surface/90 p-8 shadow-xl shadow-black/5 backdrop-blur sm:p-10"
            >
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
                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-content dark:text-white">Создать аккаунт</h1>
                    <p class="mt-1.5 text-sm text-muted">
                        Присоединяйтесь к нашему блогу — это займёт меньше минуты
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Имя -->
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-content"> Имя </label>
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
                                id="name"
                                type="text"
                                class="w-full rounded-md border border-line bg-surface-muted py-2.5 pl-11 pr-3.5 text-sm text-content outline-none transition-all duration-200 focus:border-accent focus:ring-2 focus:ring-accent/20"
                                placeholder="Как вас зовут?"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.name" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-content"> Email </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                                    />
                                </svg>
                            </span>
                            <input
                                id="email"
                                type="email"
                                class="w-full rounded-md border border-line bg-surface-muted py-2.5 pl-11 pr-3.5 text-sm text-content outline-none transition-all duration-200 focus:border-accent focus:ring-2 focus:ring-accent/20"
                                placeholder="you@example.com"
                                v-model="form.email"
                                required
                                autocomplete="username"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.email" />
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
                                placeholder="Минимум 8 символов"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
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

                    <!-- Подтверждение пароля -->
                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-content">
                            Подтверждение пароля
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"
                                    />
                                </svg>
                            </span>
                            <input
                                id="password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full rounded-md border border-line bg-surface-muted py-2.5 pl-11 pr-3.5 text-sm text-content outline-none transition-all duration-200 focus:border-accent focus:ring-2 focus:ring-accent/20"
                                placeholder="Повторите пароль"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-md bg-accent hover:bg-accent-hover px-5 py-3 text-sm font-semibold text-accent-contrast shadow-md transition-all duration-200 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ form.processing ? "Регистрация..." : "Создать аккаунт" }}
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-muted">
                    Уже есть аккаунт?
                    <Link
                        :href="route('login')"
                        class="font-semibold text-accent transition-colors hover:text-accent dark:hover:text-accent"
                    >
                        Войти
                    </Link>
                </p>
            </div>
        </div>
    </Layout>
</template>
