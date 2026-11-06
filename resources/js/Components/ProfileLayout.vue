<template>
    <div class="relative pb-24 pt-4">
        <!-- Фон профиля: картинка пользователя, приглушённая подложкой -->
        <div v-if="user.background_url" class="pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
            <img
                :src="user.background_url"
                alt=""
                class="h-full w-full object-cover"
                :style="{ filter: `blur(${backgroundBlur}px) saturate(75%)` }"
                loading="lazy"
                decoding="async"
                @load="measureBackground"
            />
            <div class="absolute inset-0" :style="{ backgroundColor: `rgb(var(--c-page) / ${filmAlpha})` }"></div>
        </div>

        <div class="page-shell">
            <div class="mx-auto w-full max-w-[74rem] bg-page">
                <!-- Обложка во всю ширину панели, без скруглений -->
                <div class="relative aspect-[16/6] max-h-[26vh] w-full overflow-hidden bg-surface-muted">
                    <img v-if="user.cover_url" :src="user.cover_url" alt="" class="h-full w-full object-cover" loading="eager" />
                </div>

                <div class="px-5 pb-14 sm:px-8 lg:px-10">
                    <!-- Шапка как в соцсетях: аватар наезжает на обложку, рядом имя и подпись -->
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:gap-8">
                        <div class="relative -mt-16 -ml-1 w-max bg-page p-1">
                            <UserAvatar :user="user" :size="120" square />
                        </div>
                        <div class="flex min-w-0 flex-1 flex-col gap-4 sm:pt-3 sm:pb-2 xl:flex-row xl:items-baseline xl:justify-between xl:gap-10">
                            <div class="min-w-0">
                            <h1
                                class="font-display text-[clamp(2.25rem,4vw,4rem)] font-extrabold leading-[0.95] tracking-[-0.03em] [text-wrap:balance] text-content"
                            >
                                {{ user.name }}
                            </h1>
                            <p class="mt-2 text-[1.125rem] text-muted">{{ subtitle }}</p>
                            </div>

                            <!-- Ссылки автора: на одной линии с именем, на узких экранах — под ним -->
                            <ul
                                v-if="links.length"
                                class="flex flex-wrap gap-x-5 gap-y-2 text-[1.0625rem] xl:max-w-[30rem] xl:justify-end"
                                aria-label="Ссылки автора"
                            >
                            <li v-for="link in links" :key="link.url">
                                <a
                                    :href="link.url"
                                    target="_blank"
                                    rel="noopener noreferrer nofollow"
                                    class="group inline-flex flex-wrap items-baseline gap-x-1.5 transition-colors"
                                >
                                    <span class="font-medium text-content transition-colors group-hover:text-accent">{{
                                        link.label
                                    }}</span>
                                    <span class="text-[0.9375rem] text-muted">{{ linkHost(link.url) }}</span>
                                </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div v-if="$slots.actions" class="mt-6 flex flex-wrap gap-3">
                        <slot name="actions" />
                    </div>

                    <!-- Сетка как на главной: лента слева, рейл «Об авторе» и «Чаще читают» справа -->
                    <div class="home-layout mt-12">
                        <section class="min-w-0">
                            <slot />
                        </section>

                        <aside v-if="hasAside" class="min-w-0 xl:sticky xl:top-8 xl:self-start">
                            <section v-if="user.about">
                                <h2 class="type-h2 text-content">Об авторе</h2>
                                <p
                                    :id="aboutId"
                                    class="mt-4 whitespace-pre-line text-[1.0625rem] leading-relaxed text-content"
                                    :class="aboutExpanded ? '' : 'max-h-[9rem] overflow-hidden xl:max-h-none'"
                                >
                                    {{ user.about }}
                                </p>
                                <button
                                    v-if="showAboutToggle"
                                    type="button"
                                    class="mt-2 inline-flex min-h-11 items-center text-sm text-accent transition-colors hover:text-accent-hover xl:hidden"
                                    :aria-expanded="aboutExpanded"
                                    :aria-controls="aboutId"
                                    @click="aboutExpanded = !aboutExpanded"
                                >
                                    {{ aboutExpanded ? "Свернуть" : "Показать полностью" }}
                                </button>

                                <dl class="mt-7 flex flex-wrap gap-x-10 gap-y-4">
                                    <div v-for="item in statsItems" :key="item.label" class="flex items-baseline gap-2.5">
                                        <dd class="num font-display text-[1.75rem] font-bold leading-none text-content">
                                            {{ item.value }}
                                        </dd>
                                        <dt class="text-[0.9375rem] text-muted">{{ item.label }}</dt>
                                    </div>
                                </dl>
                            </section>

                            <!-- Чаще читают: цифра просмотров крупно, как «Афиша» на главной -->
                            <section v-if="topArticles.length" class="mt-10">
                                <h2 class="type-h2 text-content">Чаще читают</h2>
                                <ul class="mt-5 flex flex-col gap-5">
                                    <li v-for="article in topArticles" :key="article.id">
                                        <Link
                                            :href="route('articles.show', { article: article.slug })"
                                            class="group flex flex-wrap items-baseline gap-x-2.5 gap-y-1"
                                        >
                                            <span
                                                class="num font-display text-[1.75rem] font-bold leading-none tracking-[-0.02em] text-content transition-colors group-hover:text-accent"
                                            >
                                                {{ formatCount(article.views_count) }}
                                            </span>
                                            <span class="text-[1.0625rem] text-muted">
                                                {{ plural(article.views_count ?? 0, ["просмотр", "просмотра", "просмотров"]) }}
                                            </span>
                                            <span
                                                class="basis-full text-[1.0625rem] font-medium leading-snug text-content transition-colors group-hover:text-accent"
                                            >
                                                {{ article.label }}
                                            </span>
                                        </Link>
                                    </li>
                                </ul>
                            </section>
                        </aside>
                    </div>
                </div>
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
    // Лучшие статьи автора по просмотрам — вместо прежней медиасетки
    topArticles: {
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
    if (backgroundLuminance.value === null) return 0.55;

    // Контент лежит на непрозрачной панели, поэтому фото вокруг затемняем умеренно
    return Math.min(0.6, Math.max(0.3, 0.6 - (backgroundLuminance.value / 255) * 0.3));
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

const formatCount = (value) => new Intl.NumberFormat("ru-RU").format(value ?? 0);

// Подпись одной строкой: она же отвечает на вопрос «что это за страница»
const subtitle = computed(() => (memberSince.value ? `Автор блога · на блоге с ${memberSince.value}` : "Автор блога"));

const aboutId = computed(() => `about-${props.user.id}`);

const showAboutToggle = computed(() => (props.user.about || "").trim().length > 160);

const memberSince = computed(() => {
    if (!props.user.created_at) return "";

    const date = new Date(props.user.created_at);

    return `${MONTHS_GENITIVE[date.getMonth()]} ${date.getFullYear()}`;
});

const plural = (n, forms) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return forms[0];
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return forms[1];
    return forms[2];
};

// В рейле только две цифры: сколько статей и сколько их читали
const statsItems = computed(() => [
    { label: plural(props.stats.articles_count ?? 0, ["статья", "статьи", "статей"]), value: props.stats.articles_count ?? 0 },
    { label: plural(props.stats.views_count ?? 0, ["просмотр", "просмотра", "просмотров"]), value: props.stats.views_count ?? 0 },
]);

// Ссылки на ресурсы автора (из настроек профиля)
const links = computed(() => (props.user.links ?? []).filter((link) => link?.url));

const linkHost = (url) => {
    try {
        return new URL(url).hostname.replace(/^www\./, "");
    } catch {
        return "";
    }
};

// Рейл нужен, если есть что про автора сказать или показать
const hasAside = computed(() => Boolean(props.user.about) || props.topArticles.length > 0 || links.value.length > 0);
</script>
