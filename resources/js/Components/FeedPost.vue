<!-- resources/js/Components/FeedPost.vue -->
<template>
    <!-- Пост в открытой ленте: без подложки, рамки и скругления.
         Посты разделяет линия и воздух в родительском .feed-list. -->
    <article ref="cardRef" class="min-w-0">
        <!-- Шапка: автор, категория и дата; просмотры остаются справа -->
        <header class="flex items-start gap-3 leading-snug text-muted">
            <UserAvatar v-if="showAuthor" :user="article.user" :size="44" class="shrink-0" />
            <div class="min-w-0 flex-1">
                <Link
                    v-if="showAuthor"
                    :href="route('authors.show', article.user.id)"
                    class="block truncate text-[1rem] font-semibold text-content transition-colors hover:text-accent"
                >
                    {{ article.user.name }}
                </Link>
                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[0.8125rem]">
                    <!-- Закрепление живёт в профиле автора, на его странице и в общей ленте
                         у собственного поста — там метка и подсказывает, что пин сработал -->
                    <span
                        v-if="article.is_pinned && (!showAuthor || isOwner)"
                        class="inline-flex items-center gap-1 font-medium text-accent"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M17.25 3.75H6.75A1.5 1.5 0 005.25 5.25v15L12 15.75l6.75 4.5v-15a1.5 1.5 0 00-1.5-1.5z"
                            />
                        </svg>
                        Закреплено
                    </span>

                    <span
                        v-if="article.status && article.status !== 'published'"
                        class="inline-flex items-center gap-1 rounded-sm border border-line bg-surface-muted px-1.5 py-0.5 font-medium text-muted"
                    >
                        {{ statusLabel }}
                    </span>

                    <Link
                        v-if="article.category_slug"
                        :href="route('articlesByCategory', article.category_slug)"
                        class="transition-colors hover:text-accent"
                    >
                        {{ categoryName }}
                    </Link>
                    <span v-else>Без категории</span>

                    <span
                        class="ml-auto inline-flex items-center gap-1"
                        :aria-label="`${article.views_count} ${pluralViews(article.views_count)}`"
                    >
                        <svg
                            class="h-4 w-4 shrink-0"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            />
                        </svg>
                        <span class="num font-medium">{{ article.views_count }}</span>
                    </span>

                    <!-- Действия автора — «⋯» рядом с просмотрами; в чужом посте слота нет -->
                    <div v-if="$slots.actions && isOwner" class="-my-1 flex items-center"><slot name="actions" /></div>
                </div>
            </div>
        </header>

        <h3
            v-if="heading"
            class="mt-4 font-display text-[1.5rem] font-bold leading-[1.2] tracking-[-0.02em] text-content sm:text-[1.75rem]"
        >
            <Link :href="route('articles.show', article.slug)" class="transition-colors hover:text-accent">
                {{ heading }}
            </Link>
        </h3>

        <!-- Телефон: тот же HTML, что на десктопе, — контент идёт в авторском порядке,
             фото стоят на своих местах и растягиваются на всю ширину экрана. Свёрнутый
             пост обрезан по высоте экрана (расчёт в скрипте), «Читать дальше» встроена
             в последний видимый абзац — сразу за последним словом -->
        <div
            ref="bodyRef"
            class="post-content mt-3 text-content"
            :class="{
                'feed-body': isMobile,
                'feed-body--clamped': !isMobile && article.has_more && !expanded,
            }"
            v-html="isMobile && mobileCollapsed ? teaserHtml : postHtml"
            @click="handleContentClick"
        />

        <!-- Текстовые действия чтения остаются ссылками, без лишних пилюль -->
        <div v-if="(article.has_more || expanded) && !mobileCollapsed" class="mt-3 flex flex-wrap items-center gap-x-5">
            <button
                type="button"
                class="inline-flex min-h-10 items-center text-sm font-medium text-accent transition-colors hover:text-accent-hover"
                :aria-expanded="expanded"
                @click="toggleExpand"
            >
                {{ expanded ? "Свернуть" : loading ? "Загружаю…" : "Читать дальше" }}
            </button>
        </div>

        <!-- Взаимодействия: лайк, комментарий и «поделиться» — голыми иконками без общей
             обёртки, рядом «К статье», справа по краю — время публикации -->
        <footer class="mt-4 flex flex-wrap items-center gap-x-1 gap-y-2">
            <div class="flex items-center" role="group" aria-label="Действия с постом">
                <LikeButton
                    :article="article"
                    :initial-liked="Boolean(article.is_liked)"
                    :initial-likes-count="article.likes_count ?? 0"
                    class="post-action"
                />

                <Link
                    :href="articleUrl + '#comments'"
                    class="post-action"
                    :aria-label="article.comments_count ? `Комментарии: ${article.comments_count}` : 'Комментировать'"
                >
                    <svg class="h-[1.375rem] w-[1.375rem] sm:h-[1.125rem] sm:w-[1.125rem] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                        />
                    </svg>
                    <span v-if="article.comments_count" class="num">{{ article.comments_count }}</span>
                </Link>

                <button
                    type="button"
                    class="post-action"
                    :aria-label="copied ? 'Ссылка скопирована' : 'Скопировать ссылку на пост'"
                    @click="share"
                >
                    <svg
                        v-if="copied"
                        class="h-[1.375rem] w-[1.375rem] sm:h-[1.125rem] sm:w-[1.125rem] shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <svg v-else class="h-[1.375rem] w-[1.375rem] sm:h-[1.125rem] sm:w-[1.125rem] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 11-5.656-5.656l1.5-1.5m9.656-1.5l1.5-1.5a4 4 0 10-5.656-5.656l-3 3a4 4 0 000 5.656"
                        />
                    </svg>
                    <span class="sr-only" aria-live="polite">{{ copied ? "Скопировано" : "" }}</span>
                </button>

                <!-- «К статье» — та же иконка-действие, без пилюли: в ряду не выделяется -->
                <Link :href="articleUrl" class="post-action" aria-label="Открыть статью целиком">
                    <svg class="h-[1.375rem] w-[1.375rem] sm:h-[1.125rem] sm:w-[1.125rem] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                </Link>
            </div>

            <!-- Время публикации: сколько прошло с момента выхода.
                 На телефоне идёт сразу за действиями в той же строке: у правого
                 края снизу висит кнопка «Наверх», и время оказывалось под ней -->
            <time
                :datetime="article.created_at"
                :title="formatDateTime(article.created_at)"
                class="num ml-2 text-[0.8125rem] tracking-[0.02em] text-muted sm:ml-auto"
            >
                {{ relativeTime(article.created_at) }}
            </time>
        </footer>

        <!-- Лайтбокс: фото из поста на полный экран, карусель листается по кадрам -->
        <ImageLightbox
            :show="lightboxOpen"
            :src="lightboxSrc"
            :alt="lightboxAlt"
            :images="lightboxImages"
            :index="lightboxIndex"
            @close="closeLightbox"
        />
    </article>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import LikeButton from "@/Components/LikeButton.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import ImageLightbox from "@/Components/ImageLightbox.vue";
import { formatDateTime, relativeTime } from "@/Support/relativeTime";

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    // Строка автора нужна в общей ленте и не нужна на странице самого автора
    showAuthor: {
        type: Boolean,
        default: true,
    },
});

const page = usePage();

// Свой ли пост: меню «⋯» и метка «Закреплено» в общей ленте — только владельцу
const isOwner = computed(() => page.props.auth?.user?.id === props.article.user?.id);

const expanded = ref(false);
const copied = ref(false);
const loading = ref(false);
const fullHtml = ref("");

// Фото в посте открываются в лайтбоксе по клику — как на странице статьи
const lightboxOpen = ref(false);
const lightboxSrc = ref("");
const lightboxAlt = ref("");
const lightboxImages = ref([]);
const lightboxIndex = ref(0);

const closeLightbox = () => {
    lightboxOpen.value = false;
};

// Делегирование: работает и для превью, и для картинок, догруженных кнопкой «Читать дальше»
const handleContentClick = (event) => {
    const target = event.target;

    // «Читать дальше» в анонсе приходит из v-html — ловим клик по классу
    if (target instanceof Element && target.closest(".feed-teaser__more")) {
        event.preventDefault();
        toggleExpand();
        return;
    }

    if (!(target instanceof HTMLImageElement)) return;

    event.preventDefault();
    event.stopPropagation();

    // Фото из карусели открываем вместе с соседними кадрами — их можно листать
    const carousel = target.closest(".image-carousel");
    const frames = carousel ? [...carousel.querySelectorAll(":scope > img")] : [];
    const index = frames.indexOf(target);

    lightboxImages.value = frames.map((img) => ({ src: img.currentSrc || img.src, alt: img.alt || "" }));
    lightboxIndex.value = index >= 0 ? index : 0;

    lightboxSrc.value = target.currentSrc || target.src;
    lightboxAlt.value = target.alt || "";
    lightboxOpen.value = true;
};

const articleUrl = computed(() => route("articles.show", props.article.slug));

// «Поделиться» = скопировать ссылку на пост, чтобы её можно было отправить куда угодно
const share = async () => {
    const url = window.location.origin + articleUrl.value;

    try {
        await navigator.clipboard.writeText(url);
    } catch {
        window.prompt("Скопируйте ссылку", url);
        return;
    }

    copied.value = true;
    window.setTimeout(() => (copied.value = false), 2000);
};

const categoryName = computed(() => props.categories.find((c) => c.slug === props.article.category_slug)?.name ?? "Без категории");

// В своём профиле видно и черновики, и скрытое — подписываем их, чтобы
// не путать с опубликованным
const statusLabel = computed(() => (props.article.status === "draft" ? "Черновик" : "Скрыто"));

const heading = computed(() => (props.article.title ?? "").trim());

/* --- Мобильная карточка: один пост — один экран -------------------------------
   Контент не переставляется: тот же HTML превью, что на десктопе, фото стоят
   на своих местах в авторском порядке и растягиваются на всю ширину экрана.
   Свёрнутый пост обрезан по высоте экрана, «Читать дальше» встроена в последний
   видимый абзац — сразу за последним словом. */
const MOBILE_QUERY = "(max-width: 639px)";
// Зазор между постом и нижней панелью/кромкой экрана
const BOTTOM_GUTTER = 12;
// Высота нижней навигации: 60px ряд + 1px граница (см. .bottom-nav в app.css).
// Константой, а не замером: Layout вставляет панель после страницы, и в момент
// первого расчёта её ещё нет в DOM.
const BOTTOM_NAV_HEIGHT = 61;
const MORE_LABEL = "Читать дальше";

const isMobile = ref(typeof window !== "undefined" && window.matchMedia(MOBILE_QUERY).matches);
const mobileCollapsed = computed(() => isMobile.value && !expanded.value);

const cardRef = ref(null);
const teaserHtml = ref("");

const escapeHtml = (text) => text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

const moreMarkup = () =>
    `<span class="feed-teaser__cut">… </span><button type="button" class="feed-teaser__more">${MORE_LABEL}</button>`;

// Блок не влезает целиком: режем текст по словам и дописываем «Читать дальше»
// внутрь того же абзаца, чтобы она стояла в конце последней видимой строки
const cutTextBlock = (mirror, node, text, space) => {
    const probe = document.createElement(node.tagName.toLowerCase());
    probe.className = node.className;
    mirror.appendChild(probe);

    const fits = (length) => {
        probe.innerHTML = escapeHtml(text.slice(0, length).trimEnd()) + " " + moreMarkup();
        return probe.getBoundingClientRect().height <= space;
    };

    let best = 0;
    let low = 0;
    let high = text.length;

    while (low <= high) {
        const mid = Math.floor((low + high) / 2);
        if (fits(mid)) {
            best = mid;
            low = mid + 1;
        } else {
            high = mid - 1;
        }
    }

    if (best < text.length) {
        const spaceAt = text.lastIndexOf(" ", best);
        if (spaceAt > 0) best = spaceAt;
    }

    probe.innerHTML = escapeHtml(text.slice(0, best).trimEnd()) + " " + moreMarkup();
    const html = probe.outerHTML;
    probe.remove();
    return html;
};

let teaserRun = 0;

const buildTeaser = async () => {
    if (!isMobile.value || expanded.value) return;

    const card = cardRef.value;
    const body = bodyRef.value;
    if (!card || !body) return;

    const run = ++teaserRun;

    // Зеркало той же ширины и с теми же классами: блоки меряются в тех же условиях
    const mirror = document.createElement("div");
    mirror.className = "post-content feed-body text-content";
    mirror.style.cssText = `position:absolute;left:-9999px;top:0;width:${body.clientWidth}px;margin:0;`;
    mirror.innerHTML = postHtml.value;
    document.body.appendChild(mirror);

    // Картинка без размеров весит ноль — без загрузки обрезка съедет
    mirror.querySelectorAll("img").forEach((img) => (img.loading = "eager"));
    await Promise.all(
        [...mirror.querySelectorAll("img")].map(
            (img) =>
                img.complete ||
                new Promise((resolve) => {
                    img.addEventListener("load", resolve, { once: true });
                    img.addEventListener("error", resolve, { once: true });
                }),
        ),
    );

    if (run !== teaserRun || !mirror.isConnected) {
        mirror.remove();
        return;
    }

    // Нижняя навигация перекрывает низ экрана: пост не должен уезжать под неё
    const budget = window.innerHeight - BOTTOM_NAV_HEIGHT - BOTTOM_GUTTER;
    // Всё, кроме тела поста: шапка, заголовок, ряд действий и их отступы
    const rest = card.getBoundingClientRect().height - body.getBoundingClientRect().height;
    const available = budget - rest;

    const mirrorTop = mirror.getBoundingClientRect().top;
    const blocks = [...mirror.children];

    const kept = [];
    let cut = null;

    for (const node of blocks) {
        const rect = node.getBoundingClientRect();
        const top = rect.top - mirrorTop;

        if (top + rect.height <= available) {
            kept.push(node.outerHTML);
            continue;
        }

        // В блоке нет медиа — дорезаем текст по словам, кнопка встанет в строку.
        // Проверяем и сам блок: редактор вставляет картинку отдельным <img>,
        // без обёртки, и querySelector её не находил
        const media =
            node.matches("img, video, iframe, .image-carousel, .video-embed") ||
            node.querySelector("img, video, iframe, .image-carousel, .video-embed");
        const text = (node.textContent || "").trim();
        const rest = available - top;

        if (!media && text && rest > 24) {
            cut = cutTextBlock(mirror, node, text, rest);
        } else if (media) {
            // Высокое фото не обрезаем: показываем кадр целиком во всю ширину, даже
            // если из-за этого карточка станет выше экрана. Потолок — высота экрана,
            // и только на совсем вытянутых кадрах включается contain (поля по бокам),
            // чтобы одно фото не занимало два экрана
            // Отдельная картинка растягивается на всю ширину экрана, поэтому
            // натуральную высоту считаем от ширины вьюпорта, а не колонки
            const blockWidth = node.matches("img")
                ? window.innerWidth
                : node.getBoundingClientRect().width || body.clientWidth;
            const first = node.matches("img") ? node : node.querySelector("img");
            const naturalHeight =
                first && first.naturalWidth ? (first.naturalHeight / first.naturalWidth) * blockWidth : rest;
            // Потолок — два экрана: одно фото не должно растягивать пост на три
            const cap = Math.round(Math.min(Math.max(naturalHeight, 80), window.innerHeight * 2));

            const holder = document.createElement("div");
            holder.innerHTML = node.outerHTML;
            holder.querySelectorAll("img").forEach((img) => {
                img.style.maxHeight = `${cap}px`;
                img.style.objectFit = "contain";
            });
            kept.push(holder.innerHTML);
        }

        break;
    }

    const truncated = Boolean(cut) || kept.length < blocks.length;
    let html = kept.join("");

    if (cut) {
        html += cut;
    } else if (truncated || props.article.has_more) {
        // Упёрлись в фото: кнопка идёт строкой сразу под кадром
        html += `<p class="feed-teaser__row"><button type="button" class="feed-teaser__more">${MORE_LABEL}</button></p>`;
    }

    teaserHtml.value = html;
    mirror.remove();
};

const mobileMedia = typeof window !== "undefined" ? window.matchMedia(MOBILE_QUERY) : null;

const onMobileChange = (event) => {
    isMobile.value = event.matches;
    buildTeaser();
};

let resizeTimer = 0;
let lastWidth = 0;

const onResize = () => {
    // На телефоне адресная строка при прокрутке меняет только высоту экрана.
    // Пересчитывать из-за этого нельзя: карточка меняет число строк и «прыгает».
    // Поворот и смена окна меняют ширину — вот на неё и реагируем.
    if (window.innerWidth === lastWidth) return;

    lastWidth = window.innerWidth;
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(buildTeaser, 150);
};

onMounted(() => {
    lastWidth = window.innerWidth;
    mobileMedia?.addEventListener("change", onMobileChange);
    window.addEventListener("resize", onResize);
    buildTeaser();

    // Golos Text грузится после первого рендера: без пересчёта строки лягут иначе
    document.fonts?.ready.then(() => buildTeaser());
});

onBeforeUnmount(() => {
    mobileMedia?.removeEventListener("change", onMobileChange);
    window.removeEventListener("resize", onResize);
    window.clearTimeout(resizeTimer);
});

watch([mobileCollapsed, () => props.article.preview], () => buildTeaser());

// Авторские подзаголовки не должны конкурировать с заголовками страницы:
// оставляем их визуально, но как абзацы, а не как h2/h3
const demoteHeadings = (html) =>
    (html || "").replace(/<(\/?)h([1-6])\b[^>]*>/gi, (_, close) => (close ? "</p>" : '<p class="post-subhead">'));

const postHtml = computed(() => demoteHeadings(expanded.value ? fullHtml.value : props.article.preview));

// Код и карусели в ленте: модули грузим лениво, только когда они нужны
const bodyRef = ref(null);

const enhanceContent = async () => {
    const root = bodyRef.value;
    if (!root) return;

    if (root.querySelector("pre code")) {
        const { enhanceCodeBlocks } = await import("@/Support/codeHighlight");
        enhanceCodeBlocks(root);
    }

    if (root.querySelector(".image-carousel")) {
        const { enhanceImageCarousels } = await import("@/Support/imageCarousel");
        enhanceImageCarousels(root);
    }

    if (root.querySelector(".video-embed")) {
        const { enhanceVideoEmbeds } = await import("@/Support/videoEmbed");
        enhanceVideoEmbeds(root);
    }
};

onMounted(enhanceContent);

// При раскрытии поста HTML меняется целиком — оформляем блоки заново
watch(postHtml, async () => {
    await nextTick();
    enhanceContent();
});

const toggleExpand = async () => {
    if (expanded.value) {
        expanded.value = false;
        return;
    }

    if (!fullHtml.value && props.article.has_more) {
        loading.value = true;

        try {
            const response = await fetch(route("articles.excerpt", props.article.slug), {
                headers: { Accept: "application/json" },
            });
            if (!response.ok) throw new Error("HTTP " + response.status);
            const data = await response.json();
            fullHtml.value = data.html || props.article.preview;
        } catch {
            router.visit(route("articles.show", props.article.slug));
            return;
        } finally {
            loading.value = false;
        }
    }

    expanded.value = true;
};

const pluralize = (n, [one, few, many]) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return one;
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return few;
    return many;
};

const pluralViews = (n) => pluralize(n, ["просмотр", "просмотра", "просмотров"]);
</script>
