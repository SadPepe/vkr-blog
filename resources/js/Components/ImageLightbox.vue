<template>
    <Teleport to="body">
        <Transition name="lightbox">
            <div
                v-if="show"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/85 p-6 backdrop-blur-md sm:p-10"
                role="dialog"
                aria-modal="true"
                :aria-label="alt ? `Просмотр изображения: ${alt}` : 'Просмотр изображения'"
                @click="requestClose"
            >
                <button
                    type="button"
                    class="absolute right-4 top-4 z-10 rounded-full bg-white/10 p-2.5 text-white transition-colors hover:bg-white/25 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 sm:right-6 sm:top-6"
                    aria-label="Закрыть"
                    title="Закрыть (Esc)"
                    @click.stop="requestClose"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <figure class="flex max-h-full max-w-[min(1100px,92vw)] flex-col items-center gap-3" @click.stop>
                    <img
                        :src="src"
                        :alt="alt || 'Изображение из статьи'"
                        class="max-h-[78vh] w-auto max-w-full rounded-md object-contain shadow-2xl"
                    />
                    <figcaption class="text-xs text-white/60">
                        Esc или клик вне изображения — закрыть
                    </figcaption>
                </figure>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { onBeforeUnmount, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    src: {
        type: String,
        default: "",
    },
    alt: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["close"]);

const requestClose = () => emit("close");

const onKeydown = (event) => {
    if (event.key === "Escape") {
        requestClose();
    }
};

watch(
    () => props.show,
    (open) => {
        document.body.style.overflow = open ? "hidden" : "";

        if (open) {
            window.addEventListener("keydown", onKeydown);
        } else {
            window.removeEventListener("keydown", onKeydown);
        }
    },
);

onBeforeUnmount(() => {
    window.removeEventListener("keydown", onKeydown);
    document.body.style.overflow = "";
});
</script>

<style scoped>
.lightbox-enter-active,
.lightbox-leave-active {
    transition: opacity 0.2s ease;
}

.lightbox-enter-from,
.lightbox-leave-to {
    opacity: 0;
}
</style>
