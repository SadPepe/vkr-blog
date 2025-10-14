<template>
    <div class="mt-[150px] mx-auto max-w-screen-xl">
        <header class="">
            <Navbar @search="performSearch" />
        </header>

        <PageTransition>
            <main class="min-h-[85vh]">
                <slot />
            </main>
        </PageTransition>
       <Footer></Footer>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import Navbar from "@/Components/Navbar.vue";
import PageTransition from "@/Shared/PageTransition.vue";
import Footer from "@/Components/Footer.vue";
import { router } from "@inertiajs/vue3";

const performSearch = (query) => {
  router.get(route('home'), { search: query }, {
    preserveState: true,
    replace: true
  })
}
</script>
