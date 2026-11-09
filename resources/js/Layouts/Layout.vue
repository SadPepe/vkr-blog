<template>
    <div class="flex min-h-screen flex-col">
        <AnnouncementTicker />
        <Navbar />

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
import AnnouncementTicker from "@/Components/AnnouncementTicker.vue";
import Navbar from "@/Components/Navbar.vue";
import FlashMessage from "@/Components/FlashMessage.vue";
import PageTransition from "@/Shared/PageTransition.vue";
import Footer from "@/Components/Footer.vue";

const page = usePage();
</script>
