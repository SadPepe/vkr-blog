<script setup>
import { computed } from "vue";
import GuestLayout from "@/Layouts/Layout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

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
    <GuestLayout>
        <Head title="Подтверждение почты" />
        <div class="mx-auto w-full max-w-md py-8 md:py-12">
            <div class="mb-4 text-sm text-muted">
                Спасибо за регистрацию! Подтвердите почту, перейдя по ссылке из письма. Если письмо не пришло — отправим новое.
            </div>

            <div class="mb-4 text-sm font-medium text-accent" v-if="verificationLinkSent">
                Новая ссылка для подтверждения отправлена указанную при регистрации почту.
            </div>

            <form @submit.prevent="submit">
                <div class="mt-4 flex items-center justify-between">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Отправить письмо ещё раз
                    </PrimaryButton>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-muted underline hover:text-content focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2"
                        >Выйти</Link
                    >
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
