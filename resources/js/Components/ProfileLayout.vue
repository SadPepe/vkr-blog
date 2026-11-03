<template>
    <!-- Фон страницы: на всю ширину и высоту экрана, без затемнения -->
    <div v-if="user.background_url" class="pointer-events-none fixed inset-0 -z-10" aria-hidden="true">
        <img :src="user.background_url" alt="" class="h-full w-full object-cover" />
    </div>

    <div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Шапка: обложка во всю ширину контента -->
        <div class="overflow-hidden rounded-md border border-line bg-surface shadow-sm">
            <div class="relative h-40 w-full bg-gradient-to-r from-accent via-accent to-accent-2 sm:h-56">
                <img v-if="user.cover_url" :src="user.cover_url" alt="" class="h-full w-full object-cover" />
            </div>
        </div>

        <div class="mt-3 grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            <!-- Левая колонка: аватар и карточка с информацией -->
            <aside class="min-w-0">
                <!-- Аватар наезжает на шапку снизу -->
                <div class="relative z-10 -mb-8 -mt-16 ml-5 w-max sm:-mt-20 sm:ml-6">
                    <div class="w-max rounded-full ring-4 ring-surface">
                        <UserAvatar :user="user" :size="112" />
                    </div>
                </div>

                <!-- Карточка информации: на десктопе прилипает к верху при прокрутке ленты -->
                <div class="rounded-md border border-line bg-surface p-5 pt-12 shadow-sm sm:p-6 sm:pt-14 lg:sticky lg:top-24">
                    <h1 class="break-words text-2xl font-bold tracking-tight text-content">{{ user.name }}</h1>
                    <p class="mt-1 text-sm text-muted">На блоге {{ memberSince }}</p>

                    <p v-if="user.about" class="mt-3 whitespace-pre-line text-[15px] leading-relaxed text-content">
                        {{ user.about }}
                    </p>
                    <p v-else class="mt-3 text-sm text-muted">Здесь пока нет описания профиля.</p>

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
                                    :alt="item.title || 'Изображение поста'"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    loading="lazy"
                                />
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Правая колонка: статистика и лента -->
            <section class="min-w-0">
                <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-md border border-line bg-line sm:grid-cols-4">
                    <div v-for="item in statsItems" :key="item.label" class="bg-surface px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">
                            {{ item.label }}
                        </dt>
                        <dd class="mt-0.5 text-xl font-bold tabular-nums text-content">{{ item.value }}</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <slot />
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
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

const memberSince = computed(() => {
    if (!props.user.created_at) return "";

    return new Date(props.user.created_at).toLocaleDateString("ru-RU", {
        month: "long",
        year: "numeric",
    });
});

const statsItems = computed(() => [
    { label: "Статьи", value: props.stats.articles_count ?? 0 },
    { label: "Просмотры", value: props.stats.views_count ?? 0 },
    { label: "Лайки", value: props.stats.likes_count ?? 0 },
    { label: "Комментарии", value: props.stats.comments_count ?? 0 },
]);
</script>
