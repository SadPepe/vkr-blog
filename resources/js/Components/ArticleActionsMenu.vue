<!-- Меню действий над своим постом: «⋯» рядом с просмотрами.
     Закрепление и скрытие — PATCH-запросы, редактирование — переход, удаление отдаём родителю. -->
<template>
    <div ref="rootRef" class="relative shrink-0">
        <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full text-muted transition-colors hover:bg-surface-muted hover:text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
            aria-haspopup="menu"
            :aria-expanded="open ? 'true' : 'false'"
            aria-label="Действия с постом"
            title="Действия с постом"
            @click="open = !open"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <circle cx="12" cy="5" r="1.6" />
                <circle cx="12" cy="12" r="1.6" />
                <circle cx="12" cy="19" r="1.6" />
            </svg>
        </button>

        <!-- Оверлей закрывает меню по клику мимо и не даёт странице уехать -->
        <div v-if="open" class="fixed inset-0 z-40" @click="open = false"></div>

        <div
            v-if="open"
            class="absolute right-0 top-full z-50 mt-1 w-52 border border-line bg-surface py-1 shadow-lg"
            role="menu"
            aria-label="Действия с постом"
        >
            <Link
                :href="route('articles.edit', { article: article.slug })"
                class="block w-full px-4 py-2 text-left text-sm text-content transition-colors hover:bg-surface-muted focus-visible:bg-surface-muted focus-visible:outline-none"
                role="menuitem"
                @click="open = false"
            >
                Редактировать
            </Link>

            <button
                v-if="article.status === 'published'"
                type="button"
                class="block w-full px-4 py-2 text-left text-sm text-content transition-colors hover:bg-surface-muted focus-visible:bg-surface-muted focus-visible:outline-none"
                role="menuitem"
                @click="togglePin"
            >
                {{ article.is_pinned ? "Открепить" : "Закрепить" }}
            </button>

            <button
                type="button"
                class="block w-full px-4 py-2 text-left text-sm text-content transition-colors hover:bg-surface-muted focus-visible:bg-surface-muted focus-visible:outline-none"
                role="menuitem"
                @click="toggleVisibility"
            >
                {{ article.status === "published" ? "Скрыть" : "Опубликовать" }}
            </button>

            <button
                v-if="showDelete"
                type="button"
                class="block w-full px-4 py-2 text-left text-sm text-content transition-colors hover:bg-surface-muted focus-visible:bg-surface-muted focus-visible:outline-none"
                role="menuitem"
                @click="requestDelete"
            >
                Удалить
            </button>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    // Удаление подтверждает родитель: на странице поста его нет,
    // а в профиле уже есть ConfirmDialog
    showDelete: {
        type: Boolean,
        default: false,
    },
});

// Родитель узнаёт результат действия: в ленте с локальным срезом (Home)
// метку и исчезновение поста нужно обновить без перезагрузки страницы
const emit = defineEmits(["delete", "pin", "visibility"]);

const open = ref(false);
const rootRef = ref(null);

const closeOnEscape = (event) => {
    if (event.key === "Escape") open.value = false;
};

const closeOnOutside = (event) => {
    if (open.value && rootRef.value && !rootRef.value.contains(event.target)) {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener("keydown", closeOnEscape);
    document.addEventListener("pointerdown", closeOnOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("keydown", closeOnEscape);
    document.removeEventListener("pointerdown", closeOnOutside);
});

const togglePin = () => {
    open.value = false;
    // Значение вычисляем до запроса: после него props.article придёт уже новым
    const pinned = !props.article.is_pinned;

    router.patch(route("articles.pin", { article: props.article.slug }), {}, {
        preserveScroll: true,
        onSuccess: () => emit("pin", props.article, pinned),
    });
};

const toggleVisibility = () => {
    open.value = false;
    // Текущий статус тоже запоминаем заранее
    const hidden = props.article.status === "published";

    router.patch(route("articles.visibility", { article: props.article.slug }), {}, {
        preserveScroll: true,
        onSuccess: () => emit("visibility", props.article, hidden),
    });
};

const requestDelete = () => {
    open.value = false;
    emit("delete", props.article);
};
</script>
