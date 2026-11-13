<template>
    <!-- Кнопка «Наверх»: появляется после прокрутки, чтобы длинную ленту
         можно было вернуть к началу без рывка к скроллбару. -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <button
            v-if="visible"
            type="button"
            class="fixed bottom-6 right-4 z-30 inline-flex h-11 w-11 items-center justify-center bg-surface-muted text-content shadow-sm transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent sm:right-6"
            aria-label="Наверх"
            title="Наверх"
            @click="backToTop"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M5 12l7-7 7 7" />
            </svg>
        </button>
    </Transition>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";

const props = defineProps({
    // Порог в пикселях: первую страницу ленты кнопка не перекрывает
    threshold: {
        type: Number,
        default: 700,
    },
});

const visible = ref(false);

const update = () => {
    visible.value = window.scrollY > props.threshold;
};

const prefersReducedMotion = () => window.matchMedia?.("(prefers-reduced-motion: reduce)").matches ?? false;

const backToTop = () => {
    window.scrollTo({ top: 0, behavior: prefersReducedMotion() ? "auto" : "smooth" });
};

onMounted(() => {
    update();
    window.addEventListener("scroll", update, { passive: true });
});

onBeforeUnmount(() => window.removeEventListener("scroll", update));
</script>
