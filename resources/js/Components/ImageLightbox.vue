<template>
    <Teleport to="body">
        <Transition name="lightbox">
            <div
                v-if="show"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/85 p-6 backdrop-blur-md sm:p-10"
                role="dialog"
                aria-modal="true"
                :aria-label="currentAlt ? `Просмотр изображения: ${currentAlt}` : 'Просмотр изображения'"
                @click="requestClose"
                @touchstart.passive="onTouchStart"
                @touchend.passive="onTouchEnd"
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

                <!-- Карусель листается по одному кадру: стрелки на краях и свайп -->
                <button
                    v-if="hasMany"
                    type="button"
                    class="absolute left-2 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/10 p-2.5 text-white transition-colors hover:bg-white/25 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 sm:left-5"
                    aria-label="Предыдущее фото"
                    title="Предыдущее (←)"
                    @click.stop="go(-1)"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <button
                    v-if="hasMany"
                    type="button"
                    class="absolute right-2 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/10 p-2.5 text-white transition-colors hover:bg-white/25 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 sm:right-5"
                    aria-label="Следующее фото"
                    title="Следующее (→)"
                    @click.stop="go(1)"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <figure class="flex max-h-full max-w-[min(1100px,92vw)] flex-col items-center gap-3" @click.stop>
                    <img
                        :key="currentSrc"
                        :src="currentSrc"
                        :alt="currentAlt || 'Изображение из статьи'"
                        class="max-h-[78vh] w-auto max-w-full rounded-md object-contain shadow-2xl"
                    />
                    <figcaption class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-white/60">
                        <span v-if="hasMany" class="num text-white/80" aria-live="polite">
                            {{ currentIndex + 1 }} / {{ total }}
                        </span>
                        <span>
                            {{ hasMany ? "Стрелки ← →, Esc или клик вне изображения — закрыть" : "Esc или клик вне изображения — закрыть" }}
                        </span>
                    </figcaption>
                </figure>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";

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
    // Галерея карусели: [{ src, alt }] или массив строк; пусто — показываем один src
    images: {
        type: Array,
        default: () => [],
    },
    index: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(["close"]);

const currentIndex = ref(0);

const gallery = computed(() =>
    props.images.length
        ? props.images.map((item) => (typeof item === "string" ? { src: item, alt: "" } : item))
        : props.src
          ? [{ src: props.src, alt: props.alt || "" }]
          : [],
);

const total = computed(() => gallery.value.length);
const hasMany = computed(() => total.value > 1);
const current = computed(() => gallery.value[currentIndex.value] || { src: props.src, alt: props.alt || "" });
const currentSrc = computed(() => current.value.src || "");
const currentAlt = computed(() => current.value.alt || "");

const clampIndex = (value) => (total.value ? Math.min(Math.max(value, 0), total.value - 1) : 0);

const go = (delta) => {
    if (!hasMany.value) return;
    currentIndex.value = (currentIndex.value + delta + total.value) % total.value;
};

const requestClose = () => emit("close");

const onKeydown = (event) => {
    if (event.key === "Escape") {
        requestClose();
        return;
    }

    if (!hasMany.value) return;

    if (event.key === "ArrowLeft") {
        event.preventDefault();
        go(-1);
    } else if (event.key === "ArrowRight") {
        event.preventDefault();
        go(1);
    }
};

// Свайп по фото на телефоне: листаем по одному кадру
const touchStart = ref(null);

const onTouchStart = (event) => {
    const touch = event.changedTouches?.[0];
    touchStart.value = touch ? { x: touch.clientX, y: touch.clientY } : null;
};

const onTouchEnd = (event) => {
    const start = touchStart.value;
    touchStart.value = null;

    const touch = event.changedTouches?.[0];
    if (!start || !touch || !hasMany.value) return;

    const dx = touch.clientX - start.x;
    const dy = touch.clientY - start.y;
    if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy)) return;

    go(dx > 0 ? -1 : 1);
};

watch(
    () => props.show,
    (open) => {
        document.body.style.overflow = open ? "hidden" : "";

        if (open) {
            currentIndex.value = clampIndex(props.index);
            window.addEventListener("keydown", onKeydown);
        } else {
            window.removeEventListener("keydown", onKeydown);
        }
    },
);

// Родитель может открыть галерею сразу с нужного кадра
watch(
    () => props.index,
    (value) => {
        if (props.show) currentIndex.value = clampIndex(value);
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
