<template>
    <div class="mt-24 mx-auto max-w-[1500px]">
        <header class="">
            <Navbar @search="performSearch" />
        </header>

        <PageTransition>
            <main>
                <slot />
            </main>
        </PageTransition>
       <Footer></Footer>
    </div>
</template>

<script setup>
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
