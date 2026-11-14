<template>
    <!-- Отступ снизу — место под нижнюю навигацию на телефоне -->
    <div class="page-with-bottom-nav flex min-h-screen flex-col">
        <AnnouncementTicker />
        <Navbar />

        <FlashMessage />

        <PageTransition>
            <main :key="page.url" class="flex-1">
                <slot />
            </main>
        </PageTransition>
        <Footer />

        <BottomNav />
        <CookieNotice />
        <ExternalLinkGuard />
    </div>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import AnnouncementTicker from "@/Components/AnnouncementTicker.vue";
import Navbar from "@/Components/Navbar.vue";
import FlashMessage from "@/Components/FlashMessage.vue";
import PageTransition from "@/Shared/PageTransition.vue";
import Footer from "@/Components/Footer.vue";
import BottomNav from "@/Components/BottomNav.vue";
import CookieNotice from "@/Components/CookieNotice.vue";
import ExternalLinkGuard from "@/Components/ExternalLinkGuard.vue";

const page = usePage();
</script>
