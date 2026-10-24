<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const categories = computed(() => page.props.categories || []);

const activeCategory = ref(null);

const updateActiveCategory = () => {
    const path = window.location.pathname;

    if (path.includes('/categories/')) {
        const slug = path.split('/categories/')[1];
        activeCategory.value = slug;
    } else {
        activeCategory.value = 'all'; // Главная страница
    }
};

watch(() => page.url, updateActiveCategory);

const listEl = ref(null);
const isDragging = ref(false);

// Перетаскивание ряда мышью, когда категории не помещаются
let isDown = false;
let startX = 0;
let startScrollLeft = 0;
let dragged = 0;

const onMouseDown = (e) => {
    if (e.button !== 0) return;
    isDown = true;
    isDragging.value = true;
    startX = e.pageX;
    startScrollLeft = listEl.value.scrollLeft;
    dragged = 0;
};

const onMouseMove = (e) => {
    if (!isDown) return;
    const dx = e.pageX - startX;
    dragged = Math.max(dragged, Math.abs(dx));
    listEl.value.scrollLeft = startScrollLeft - dx;
};

const onMouseUp = () => {
    isDown = false;
    isDragging.value = false;
};

const onClickCapture = (e) => {
    // Не переходим по категории, если это была прокрутка
    if (dragged > 5) {
        e.preventDefault();
        e.stopPropagation();
    }
};

onMounted(() => {
    updateActiveCategory();

    const el = listEl.value;
    if (!el) return;

    el.addEventListener("mousedown", onMouseDown);
    el.addEventListener("mousemove", onMouseMove);
    el.addEventListener("mouseleave", onMouseUp);
    el.addEventListener("mouseup", onMouseUp);
    el.addEventListener("click", onClickCapture, true);
});

onBeforeUnmount(() => {
    const el = listEl.value;
    if (!el) return;

    el.removeEventListener("mousedown", onMouseDown);
    el.removeEventListener("mousemove", onMouseMove);
    el.removeEventListener("mouseleave", onMouseUp);
    el.removeEventListener("mouseup", onMouseUp);
    el.removeEventListener("click", onClickCapture, true);
});
</script>

<template>
    <div class="relative z-10 border-t border-gray-100/80 dark:border-white/5">
        <div
            ref="listEl"
            class="category-carousel scrollbar-hide flex cursor-grab select-none space-x-2 overflow-x-auto px-4 py-3"
            :class="isDragging ? 'cursor-grabbing' : ''"
        >
            <!-- Все категории -->
            <a
                href="/"
                :class="{
                    'border border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-400':
                        activeCategory === 'all',
                    'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-stone-300 dark:hover:bg-gray-800':
                        activeCategory !== 'all',
                }"
                class="inline-flex flex-shrink-0 items-center rounded px-4 py-2 text-sm font-medium whitespace-nowrap transition-all duration-200 hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-[#252525]"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Все категории
            </a>

            <!-- Категории -->
            <a
                v-for="category in categories"
                :key="category.id"
                :href="`/categories/${category.slug}`"
                :class="{
                    'border border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-400':
                        activeCategory === category.slug,
                    'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-stone-300 dark:hover:bg-gray-800':
                        activeCategory !== category.slug,
                }"
                class="inline-flex flex-shrink-0 items-center rounded px-4 py-2 text-sm font-medium whitespace-nowrap transition-all duration-200 hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-[#252525]"
            >
                {{ category.name }}
            </a>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.category-carousel :deep(a) {
    -webkit-user-drag: none;
}

/* Плавные переходы */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}
</style>