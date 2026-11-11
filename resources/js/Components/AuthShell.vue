<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    headTitle: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Layout>
        <Head :title="headTitle" />

        <div class="page-shell py-10 lg:py-16">
            <!-- Панель формы: та же геометрия, что у постов в ленте -->
            <div class="auth-layout">
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
            </div>
        </div>
    </Layout>
</template>
