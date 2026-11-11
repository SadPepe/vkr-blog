<script setup>
import AuthShell from "@/Components/AuthShell.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(() => props.status === "verification-link-sent");
</script>

<template>
    <AuthShell head-title="Подтверждение почты">
        <template #title>Подтвердите почту</template>
        <template #subtitle>Перейдите по ссылке из письма. Если письмо не пришло — отправим новое.</template>

        <p v-if="verificationLinkSent" class="mb-5 border-l-2 border-accent bg-surface-muted px-4 py-3 text-[0.9375rem] text-content">
            Новая ссылка для подтверждения отправлена на почту, указанную при регистрации.
        </p>

        <form class="space-y-5" @submit.prevent="submit">
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-accent px-5 py-3 text-[1.0625rem] font-semibold text-accent-contrast transition-colors hover:bg-accent-hover disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ form.processing ? "Отправляем…" : "Отправить письмо ещё раз" }}
            </button>
        </form>

        <template #footer>
            Не тот аккаунт?
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="font-semibold text-accent underline underline-offset-[3px] transition-colors hover:text-accent-hover"
            >
                Выйти
            </Link>
        </template>
    </AuthShell>
</template>
