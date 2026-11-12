<script setup>
import { Link } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";

// Версия в ключе: если текст уведомления поменяется, его можно показать заново
const STORAGE_KEY = "cookie-notice-v1";

const visible = ref(false);

onMounted(() => {
    try {
        visible.value = !localStorage.getItem(STORAGE_KEY);
    } catch {
        // Приватный режим или запрет хранилища: показываем, но запомнить не сможем
        visible.value = true;
    }
});

const accept = () => {
    visible.value = false;

    try {
        localStorage.setItem(STORAGE_KEY, "1");
    } catch {
        // Не запомнилось — уведомление вернётся при следующем визите, это не ломает сайт
    }
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="visible"
            class="fixed inset-x-0 bottom-0 z-40 border-t border-line-strong bg-surface"
            role="region"
            aria-label="Использование файлов cookie"
        >
            <div class="page-shell flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between sm:gap-8">
                <p class="max-w-[62ch] text-[0.9375rem] leading-relaxed text-content">
                    Сайт использует только технические cookie: они держат сессию и защищают формы. Аналитики и рекламных трекеров нет.
                    Видео с внешних площадок встраивается при открытии публикации — свои cookie может поставить провайдер плеера.
                    Подробнее —
                    <Link
                        :href="route('legal.privacy')"
                        class="text-accent underline underline-offset-[3px] transition-colors hover:text-accent-hover"
                    >
                        в политике
                    </Link>
                    .
                </p>

                <button
                    type="button"
                    class="shrink-0 bg-accent px-5 py-2.5 text-[0.9375rem] font-semibold text-accent-contrast transition-colors hover:bg-accent-hover"
                    @click="accept"
                >
                    Понятно
                </button>
            </div>
        </div>
    </Transition>
</template>
