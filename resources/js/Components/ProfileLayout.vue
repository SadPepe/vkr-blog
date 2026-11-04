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
            <!-- Обложка во всю ширину контейнера, без скруглений -->
            <div class="relative aspect-[16/7] max-h-[46vh] w-full overflow-hidden bg-surface-muted">
                <img v-if="user.cover_url" :src="user.cover_url" alt="" class="h-full w-full object-cover" loading="eager" />
            </div>

            <!-- Аватар-квадрат наезжает на обложку, рамка — тон поверхности -->
            <div class="relative -mt-16 w-max bg-surface p-1">
                <UserAvatar :user="user" :size="120" square />
            </div>

            <!-- Имя дисплейным кеглем и строка «что это» сразу под ним -->
            <h1
                class="mt-6 font-display text-[clamp(2.25rem,5vw,4rem)] font-extrabold leading-[0.95] tracking-[-0.03em] [text-wrap:balance] text-content"
            >
                {{ user.name }}
            </h1>

            <p
                v-if="user.about"
                :id="aboutId"
                class="measure mt-4 whitespace-pre-line text-[1.25rem] leading-relaxed text-content"
                :class="aboutExpanded ? '' : 'max-h-[7.5rem] overflow-hidden lg:max-h-none'"
            >
                {{ user.about }}
            </p>
            <button
                v-if="showAboutToggle"
                type="button"
                class="mt-2 inline-flex min-h-11 items-center text-sm text-accent transition-colors hover:text-accent-hover lg:hidden"
                :aria-expanded="aboutExpanded"
                :aria-controls="aboutId"
                @click="aboutExpanded = !aboutExpanded"
            >
                {{ aboutExpanded ? "Свернуть" : "Показать полностью" }}
            </button>

            <!-- Статистика: крупная цифра и подпись в одну строку, обычным регистром -->
            <dl class="mt-10 flex flex-wrap gap-x-12 gap-y-5">
                <div v-for="item in statsItems" :key="item.label" class="flex items-baseline gap-2.5">
                    <dd class="num font-display text-[2.25rem] font-bold leading-none text-content">
                        {{ item.value }}
                    </dd>
                    <dt class="text-[0.9375rem] text-muted">{{ item.label }}</dt>
                </div>
            </dl>

            <p v-if="memberSince" class="mt-6 text-[0.9375rem] text-muted">
                На блоге с <time :datetime="user.created_at">{{ memberSince }}</time>
            </p>

            <!-- Медиасетка 3×2: пустые ячейки — поля тона, а не дыра -->
            <section v-if="media.length" class="mt-14">
                <h2 class="type-h2 text-content">Медиа</h2>
                <div class="mt-5 grid grid-cols-2 gap-1 sm:grid-cols-3">
                    <template v-for="cell in 6" :key="cell">
                        <Link
                            v-if="media[cell - 1]"
                            :href="route('articles.show', { article: media[cell - 1].slug })"
                            class="group relative block aspect-square overflow-hidden bg-surface-muted focus-visible:outline-none"
                            :aria-label="`Открыть пост: ${media[cell - 1].title || 'без заголовка'}`"
                        >
                            <img
                                :src="media[cell - 1].card_image_path"
                                alt=""
                                class="h-full w-full object-cover"
                                loading="lazy"
                                decoding="async"
                            />
                        </Link>
                        <div v-else class="aspect-square bg-surface-muted" aria-hidden="true"></div>
                    </template>
                </div>
            </section>

            <div v-if="$slots.actions" class="mt-8 flex flex-wrap gap-3">
                <slot name="actions" />
            </div>

            <section class="mt-16 min-w-0">
                <slot />
            </section>
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
    if (backgroundLuminance.value === null) return 0.55;

    // Тёмное фото глушим сильнее: подписи и цифры должны читаться без замены фона
    return Math.min(0.82, Math.max(0.42, 0.82 - (backgroundLuminance.value / 255) * 0.4));
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

const plural = (n, forms) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return forms[0];
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return forms[1];
    return forms[2];
};

const statsItems = computed(() => [
    { label: plural(props.stats.articles_count ?? 0, ["статья", "статьи", "статей"]), value: props.stats.articles_count ?? 0 },
    { label: plural(props.stats.views_count ?? 0, ["просмотр", "просмотра", "просмотров"]), value: props.stats.views_count ?? 0 },
    { label: plural(props.stats.likes_count ?? 0, ["лайк", "лайка", "лайков"]), value: props.stats.likes_count ?? 0 },
    {
        label: plural(props.stats.comments_count ?? 0, ["комментарий", "комментария", "комментариев"]),
        value: props.stats.comments_count ?? 0,
    },
]);
</script>
