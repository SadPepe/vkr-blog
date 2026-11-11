<script setup>
import AuthField from "@/Components/AuthField.vue";
import AuthShell from "@/Components/AuthShell.vue";
import InputError from "@/Components/InputError.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const form = useForm({
    name: "",
    username: "",
    email: "",
    invite_code: "",
    password: "",
    password_confirmation: "",
    accept_terms: false,
    accept_privacy: false,
    accept_distribution: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};

const consentLink = "text-accent underline underline-offset-[3px] transition-colors hover:text-accent-hover";
</script>

<template>
    <AuthShell
        head-title="Регистрация"
        headline="Стать автором «подземки»."
        lead="Здесь тексты важнее охватов: пишите то, что знаете, а читатели ответят."
        :items="['Статьи с фото и черновиками', 'Страница автора со ссылками', 'Ответы на комментарии и лайки']"
    >
        <template #title>Создать аккаунт</template>
        <template #subtitle>Это займёт меньше минуты.</template>
        <template #footer>
            Уже есть аккаунт?
            <Link
                :href="route('login')"
                class="font-semibold text-accent underline underline-offset-[3px] transition-colors hover:text-accent-hover"
            >
                Войти
            </Link>
        </template>

        <form class="space-y-5" @submit.prevent="submit">
            <AuthField
                id="name"
                v-model="form.name"
                label="Имя"
                placeholder="Как вас зовут?"
                autocomplete="name"
                :error="form.errors.name"
                required
                autofocus
            />

            <AuthField
                id="username"
                v-model="form.username"
                label="Логин"
                placeholder="chelny_author"
                autocomplete="username"
                hint="3–32 символа: латиница, цифры и подчёркивание. Для входа, другим не показывается."
                :error="form.errors.username"
                required
                spellcheck="false"
            />

            <AuthField
                id="email"
                v-model="form.email"
                type="email"
                label="Email (необязательно)"
                placeholder="you@example.com"
                autocomplete="email"
                hint="Нужен для восстановления пароля и уведомлений. Можно не указывать."
                :error="form.errors.email"
            />

            <!-- Код приглашения: включается, когда бета закрыта -->
            <AuthField
                v-if="$page.props.registrationRequiresCode"
                id="invite_code"
                v-model="form.invite_code"
                label="Код приглашения"
                placeholder="Код от администратора"
                autocomplete="off"
                hint="Регистрация закрыта: код выдаёт администратор."
                :error="form.errors.invite_code"
                required
                spellcheck="false"
            />

            <AuthField
                id="password"
                v-model="form.password"
                label="Пароль"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Минимум 8 символов"
                autocomplete="new-password"
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
                placeholder="Повторите пароль"
                autocomplete="new-password"
                :error="form.errors.password_confirmation"
                required
            />

            <!-- Согласия: отдельные документы, чекбоксы не предзаполнены -->
            <fieldset class="border border-line bg-page p-4">
                <legend class="px-2 text-[0.875rem] font-semibold text-content">Согласия</legend>

                <div class="space-y-4">
                    <div>
                        <label class="flex items-start gap-3 text-[0.9375rem] leading-relaxed text-content">
                            <input
                                v-model="form.accept_terms"
                                type="checkbox"
                                class="mt-1 h-4 w-4 shrink-0 border-line-strong accent-accent"
                            />
                            <span>
                                Принимаю
                                <Link :href="route('legal.terms')" target="_blank" rel="noopener" :class="consentLink">
                                    Пользовательское соглашение
                                </Link>
                            </span>
                        </label>
                        <InputError class="mt-1.5" :message="form.errors.accept_terms" />
                    </div>

                    <div>
                        <label class="flex items-start gap-3 text-[0.9375rem] leading-relaxed text-content">
                            <input
                                v-model="form.accept_privacy"
                                type="checkbox"
                                class="mt-1 h-4 w-4 shrink-0 border-line-strong accent-accent"
                            />
                            <span>
                                Даю
                                <Link :href="route('legal.consent')" target="_blank" rel="noopener" :class="consentLink">
                                    согласие на обработку персональных данных
                                </Link>
                                и подтверждаю, что ознакомлен(а) с
                                <Link :href="route('legal.privacy')" target="_blank" rel="noopener" :class="consentLink"> Политикой </Link>
                            </span>
                        </label>
                        <InputError class="mt-1.5" :message="form.errors.accept_privacy" />
                    </div>

                    <div>
                        <label class="flex items-start gap-3 text-[0.9375rem] leading-relaxed text-content">
                            <input
                                v-model="form.accept_distribution"
                                type="checkbox"
                                class="mt-1 h-4 w-4 shrink-0 border-line-strong accent-accent"
                            />
                            <span>
                                Согласен(на) на публикацию профиля — имя, аватар, «о себе» и ссылки будут видны всем. Условия:
                                <Link :href="route('legal.distribution')" target="_blank" rel="noopener" :class="consentLink">
                                    согласие на распространение
                                </Link>
                            </span>
                        </label>
                        <InputError class="mt-1.5" :message="form.errors.accept_distribution" />
                    </div>
                </div>
            </fieldset>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-accent px-5 py-3 text-[1.0625rem] font-semibold text-accent-contrast transition-colors hover:bg-accent-hover disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ form.processing ? "Регистрация…" : "Создать аккаунт" }}
            </button>
        </form>
    </AuthShell>
</template>
