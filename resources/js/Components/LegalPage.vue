<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    title: {
        type: String,
        required: true,
    },
    updated: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div class="page-shell py-12 md:py-16">
        <div class="mx-auto max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">Документы</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-content sm:text-4xl">{{ title }}</h1>
            <p class="mt-3 text-sm text-muted">Редакция от {{ updated }}</p>

            <div class="legal mt-10">
                <slot />
            </div>

            <div class="mt-14 border-t border-line pt-6 text-sm">
                <p class="text-muted">Другие документы:</p>
                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2">
                    <Link :href="route('legal.privacy')" class="text-accent transition-colors hover:text-accent-hover">
                        Политика обработки ПД
                    </Link>
                    <Link :href="route('legal.terms')" class="text-accent transition-colors hover:text-accent-hover">
                        Пользовательское соглашение
                    </Link>
                    <Link :href="route('legal.consent')" class="text-accent transition-colors hover:text-accent-hover">
                        Согласие на обработку
                    </Link>
                    <Link :href="route('legal.distribution')" class="text-accent transition-colors hover:text-accent-hover">
                        Согласие на распространение
                    </Link>
                    <Link :href="route('legal.copyright')" class="text-accent transition-colors hover:text-accent-hover">
                        Правообладателям
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
