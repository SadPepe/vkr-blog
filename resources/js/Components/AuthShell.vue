<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    headTitle: {
        type: String,
        required: true,
    },
    // Крупное утверждение в левой колонке — голос бренда, а не подпись к форме
    headline: {
        type: String,
        required: true,
    },
    lead: {
        type: String,
        default: "",
    },
    items: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Layout>
        <Head :title="headTitle" />

        <div class="page-shell py-10 lg:py-16">
            <div class="auth-layout">
                <!-- Левая колонка: бренд и обещание, на узких экранах скрыта — знак уже в шапке -->
                <aside class="auth-aside hidden lg:block">
                    <Link
                        :href="route('home')"
                        class="inline-flex shrink-0 items-center gap-3 focus-visible:outline-none"
                        aria-label="Челны подземка — на главную"
                    >
                        <svg
                            class="h-8 w-8 shrink-0 text-accent"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path d="M4 21V10.5a8 8 0 0 1 16 0V21" />
                            <path d="M8 21v-4.5h4V12h4" />
                        </svg>
                        <span class="font-sans text-[1.375rem] font-bold leading-none tracking-[-0.02em] text-content"
                            >Челны <span class="text-accent">подземка</span></span
                        >
                    </Link>

                    <p class="type-article mt-10 text-content">{{ headline }}</p>

                    <p v-if="lead" class="measure mt-4 text-[1.0625rem] leading-relaxed text-muted">
                        {{ lead }}
                    </p>

                    <ul v-if="items.length" class="mt-8 border-t border-line">
                        <li v-for="item in items" :key="item" class="border-b border-line py-3 text-[0.9375rem] leading-snug text-content">
                            {{ item }}
                        </li>
                    </ul>
                </aside>

                <!-- Панель формы: та же геометрия, что у постов в ленте -->
                <section class="min-w-0">
                    <div class="border border-line-strong bg-surface">
                        <header class="border-b border-line px-6 py-6 sm:px-8">
                            <h1 class="type-article text-content"><slot name="title" /></h1>
                            <p v-if="$slots.subtitle" class="mt-2 text-[1.0625rem] leading-snug text-content">
                                <slot name="subtitle" />
                            </p>
                        </header>

                        <div class="px-6 py-6 sm:px-8 sm:py-7">
                            <slot />
                        </div>

                        <footer
                            v-if="$slots.footer"
                            class="border-t border-line bg-surface-muted px-6 py-4 text-[0.9375rem] leading-snug text-content sm:px-8"
                        >
                            <slot name="footer" />
                        </footer>
                    </div>
                </section>
            </div>
        </div>
    </Layout>
</template>
