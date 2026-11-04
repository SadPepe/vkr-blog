<template>
    <div class="mt-24 flex min-h-[calc(100vh-6rem)] flex-col">
        <header>
            <Navbar />
        </header>

        <FlashMessage />

        <PageTransition>
            <main :key="page.url" class="flex-1">
                <slot />
            </main>
        </PageTransition>
        <Footer />
    </div>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import Navbar from "@/Components/Navbar.vue";
import FlashMessage from "@/Components/FlashMessage.vue";
import PageTransition from "@/Shared/PageTransition.vue";
import Footer from "@/Components/Footer.vue";

const page = usePage();
</script>
