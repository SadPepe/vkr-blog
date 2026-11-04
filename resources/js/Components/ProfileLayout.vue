<template>
    <div class="relative mx-auto w-full max-w-[1100px] pb-8">
        <!-- Фон профиля: фото живёт только в полях вокруг колонки, приглушено
             и растворяется в цвет страницы сверху и снизу — швов нет -->
        <div
            v-if="user.background_url"
            class="pointer-events-none absolute inset-y-0 left-1/2 -z-10 hidden w-screen -translate-x-1/2 overflow-hidden lg:block"
            aria-hidden="true"
        >
            <img
                :src="user.background_url"
                alt=""
                loading="lazy"
                decoding="async"
                class="h-full w-full scale-110 object-cover opacity-90"
                :style="{ filter: `blur(${backgroundBlur}px) saturate(75%)` }"
                @load="measureBackground"
            />
            <!-- Плёнка подстраивается под яркость картинки: тёмные фото глушим сильнее,
                 светлые — почти не трогаем. Контраст текста от этого не зависит:
                 текст всегда лежит на непрозрачных панелях -->
            <div class="absolute inset-0" :style="{ backgroundColor: `rgb(var(--c-page) / ${filmAlpha})` }"></div>
            <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-page to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 h-56 bg-gradient-to-t from-page to-transparent"></div>
        </div>
        <!-- Подложка во всю ширину блока профиля; карточки внутри неё с небольшим отступом -->
        <div v-if="user.background_url" class="pointer-events-none absolute inset-0 -z-10 bg-page" aria-hidden="true"></div>
        <!-- Шапка: обложка во всю ширину подложки, вплотную к её верхнему краю -->
        <div class="overflow-hidden rounded-md border border-line bg-surface shadow-sm">
            <div class="relative h-40 w-full bg-gradient-to-r from-accent via-accent to-accent-2 sm:h-56">
                <img v-if="user.cover_url" :src="user.cover_url" alt="" class="h-full w-full object-cover" />
            </div>
        </div>

        <div class="mt-3 px-4 sm:px-5 lg:px-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[240px_minmax(0,1fr)] xl:gap-8">
                <!-- Левая колонка: аватар и карточка с информацией -->
                <div class="min-w-0 lg:col-start-1">
                    <!-- Аватар наезжает на шапку снизу -->
                    <div class="relative z-10 -mb-8 -mt-16 ml-5 w-max sm:-mt-20 sm:ml-6">
                        <div class="w-max rounded-full ring-4 ring-surface" aria-hidden="true">
                            <UserAvatar :user="user" :size="112" />
                        </div>
                    </div>

                    <!-- Карточка информации: на десктопе прилипает к верху при прокрутке ленты -->
                    <div
                        class="rounded-md border border-line bg-surface p-5 pt-12 shadow-sm sm:p-6 sm:pt-14 lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto lg:overscroll-contain"
                    >
                        <h1 class="break-words text-2xl font-bold tracking-tight text-content">{{ user.name }}</h1>
                        <p v-if="memberSince" class="mt-1 text-sm text-muted">
                            На блоге с <time :datetime="user.created_at">{{ memberSince }}</time>
                        </p>

                        <p
                            v-if="user.about"
                            :id="aboutId"
                            class="mt-3 whitespace-pre-line text-[15px] leading-relaxed text-content"
                            :class="aboutExpanded ? '' : 'max-h-[6.75rem] overflow-hidden lg:max-h-none'"
                        >
                            {{ user.about }}
                        </p>
                        <button
                            v-if="showAboutToggle"
                            type="button"
                            class="mt-1.5 inline-flex items-center gap-1 text-sm font-semibold text-accent transition-colors hover:text-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent lg:hidden"
                            :aria-expanded="aboutExpanded"
                            :aria-controls="aboutId"
                            @click="aboutExpanded = !aboutExpanded"
                        >
                            {{ aboutExpanded ? "Свернуть" : "Показать полностью" }}
                        </button>
                        <p v-else class="mt-3 text-sm text-muted">Здесь пока нет описания профиля.</p>

                        <!-- Статистика профиля -->
                        <dl class="mt-5 grid grid-cols-2 gap-px overflow-hidden rounded-md border border-line bg-line">
                            <div v-for="item in statsItems" :key="item.label" class="min-w-0 bg-surface px-2 py-2.5">
                                <dt class="text-[10px] font-semibold uppercase tracking-normal text-muted">
                                    {{ item.label }}
                                </dt>
                                <dd class="mt-0.5 text-lg font-bold tabular-nums text-content">{{ item.value }}</dd>
                            </div>
                        </dl>

                        <div v-if="$slots.actions" class="mt-5 flex flex-col gap-2">
                            <slot name="actions" />
                        </div>

                        <!-- Медиа: последние обложки статей -->
                        <div v-if="media.length" class="mt-7">
                            <h2 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Медиа</h2>
                            <div class="mt-2 grid grid-cols-3 gap-1">
                                <Link
                                    v-for="item in media"
                                    :key="item.id"
                                    :href="route('articles.show', { article: item.slug })"
                                    class="group relative block aspect-square overflow-hidden rounded-md bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                                    :aria-label="`Открыть пост: ${item.title || 'без заголовка'}`"
                                >
                                    <img
                                        :src="item.card_image_path"
                                        alt=""
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Центральная колонка: лента -->
                <section class="min-w-0 lg:col-start-2">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import UserAvatar from "@/Components/UserAvatar.vue";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    media: {
        type: Array,
        default: () => [],
    },
});

const MONTHS_GENITIVE = [
    "января",
    "февраля",
    "марта",
    "апреля",
    "мая",
    "июня",
    "июля",
    "августа",
    "сентября",
    "октября",
    "ноября",
    "декабря",
];

const aboutExpanded = ref(false);

// Средняя яркость фона (0–255), измеряется по загруженной картинке один раз
const backgroundLuminance = ref(null);

const filmAlpha = computed(() => {
    if (backgroundLuminance.value === null) return 0.4;

    return Math.min(0.6, Math.max(0.22, 0.6 - (backgroundLuminance.value / 255) * 0.4));
});

// Размытие выбирает владелец профиля (0–24 px), по умолчанию 12 px
const backgroundBlur = computed(() => {
    const value = Number(props.user.background_blur);

    return Number.isFinite(value) ? Math.min(24, Math.max(0, value)) : 12;
});

function measureBackground(event) {
    const image = event.target;

    if (!image?.naturalWidth) return;

    try {
        const canvas = document.createElement("canvas");
        canvas.width = 16;
        canvas.height = 16;

        const context = canvas.getContext("2d", { willReadFrequently: true });
        context.drawImage(image, 0, 0, 16, 16);

        const { data } = context.getImageData(0, 0, 16, 16);
        let sum = 0;

        for (let i = 0; i < data.length; i += 4) {
            sum += 0.2126 * data[i] + 0.7152 * data[i + 1] + 0.0722 * data[i + 2];
        }

        backgroundLuminance.value = sum / (data.length / 4);
    } catch {
        // Если браузер не дал прочитать пиксели, остаёмся на безопасном дефолте
        backgroundLuminance.value = null;
    }
}

const aboutId = computed(() => `about-${props.user.id}`);

const showAboutToggle = computed(() => (props.user.about || "").trim().length > 160);

const memberSince = computed(() => {
    if (!props.user.created_at) return "";

    const date = new Date(props.user.created_at);

    return `${MONTHS_GENITIVE[date.getMonth()]} ${date.getFullYear()}`;
});

const statsItems = computed(() => [
    { label: "Статьи", value: props.stats.articles_count ?? 0 },
    { label: "Просмотры", value: props.stats.views_count ?? 0 },
    { label: "Лайки", value: props.stats.likes_count ?? 0 },
    { label: "Комментарии", value: props.stats.comments_count ?? 0 },
]);
</script>
