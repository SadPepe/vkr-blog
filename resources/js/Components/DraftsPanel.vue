<template>
    <div>
        <!-- Вкладка у правого края (десктоп) -->
        <button
            type="button"
            class="fixed right-0 top-1/2 z-30 hidden -translate-y-1/2 flex-col items-center gap-2 rounded-l-md border border-r-0 border-line bg-surface px-2 py-3 text-muted shadow-md transition-colors hover:bg-surface-muted hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent lg:flex"
            :aria-expanded="isOpen"
            aria-controls="drafts-panel"
            @click="open"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
            </svg>
            <span class="text-xs font-medium [writing-mode:vertical-rl]">Черновики</span>
            <span
                v-if="drafts.length"
                class="min-w-[18px] rounded-full bg-accent px-1 text-center text-[10px] font-semibold leading-[18px] text-accent-contrast"
            >
                {{ drafts.length }}
            </span>
        </button>

        <!-- Кнопка для узких экранов -->
        <button
            type="button"
            class="fixed bottom-24 right-4 z-30 inline-flex items-center gap-2 rounded-full border border-line bg-surface px-4 py-3 text-sm font-semibold text-content shadow-lg transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 lg:hidden"
            :aria-expanded="isOpen"
            aria-controls="drafts-panel"
            @click="open"
        >
            <svg class="h-5 w-5 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
            </svg>
            Черновики
            <span
                v-if="drafts.length"
                class="min-w-[20px] rounded-full bg-accent px-1.5 text-center text-xs font-semibold leading-5 text-accent-contrast"
            >
                {{ drafts.length }}
            </span>
        </button>

        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isOpen"
                    id="drafts-panel"
                    class="fixed inset-0 z-50"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="drafts-panel-title"
                >
                    <div class="absolute inset-0 bg-black/50" aria-hidden="true" @click="close" />

                    <aside class="absolute inset-y-0 right-0 flex w-full max-w-sm flex-col border-l border-line bg-surface shadow-xl">
                        <header class="flex items-start justify-between gap-3 border-b border-line px-4 py-4 sm:px-5">
                            <div>
                                <h2 id="drafts-panel-title" class="text-base font-semibold text-content">Черновики</h2>
                                <p class="mt-1 text-xs text-muted">Сохраняются в этом браузере автоматически</p>
                            </div>
                            <button
                                type="button"
                                class="rounded-md p-1.5 text-muted transition-colors hover:bg-surface-muted hover:text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                                aria-label="Закрыть панель черновиков"
                                @click="close"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </header>

                        <div class="flex-1 overflow-y-auto px-4 py-4 sm:px-5">
                            <p
                                v-if="!drafts.length"
                                class="rounded-md border border-dashed border-line px-4 py-8 text-center text-sm leading-relaxed text-muted"
                            >
                                Черновиков пока нет. Начните писать статью — она сохранится автоматически.
                            </p>

                            <ul v-else class="space-y-3">
                                <li
                                    v-for="draft in drafts"
                                    :key="draft.key"
                                    class="rounded-md border border-line bg-surface-muted px-3 py-3"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-content">{{ draftTitle(draft) }}</p>
                                            <p class="mt-0.5 truncate text-xs text-muted">
                                                {{ draftTypeLabel(draft) }} · {{ formatDraftTime(draft.savedAt) }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            class="rounded-md p-1.5 text-muted transition-colors hover:bg-red-50 hover:text-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:hover:bg-red-500/10 dark:hover:text-red-300"
                                            aria-label="Удалить черновик"
                                            @click="askRemove(draft.key)"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <p v-if="draft.excerpt" class="mt-2 line-clamp-2 text-xs leading-relaxed text-muted">
                                        {{ draft.excerpt }}
                                    </p>

                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <Link
                                            :href="draft.isNew ? route('create') : route('articles.edit', draft.slug)"
                                            class="inline-flex items-center gap-1 rounded-md border border-line-strong bg-surface px-3 py-1.5 text-xs font-semibold text-content transition-colors hover:bg-page focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                                            @click="close"
                                        >
                                            Открыть
                                            <svg
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </Link>
                                        <span
                                            v-if="draft.key === currentKey"
                                            class="rounded-full bg-accent-soft px-2 py-0.5 text-[11px] font-medium text-accent"
                                        >
                                            текущий
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <footer v-if="drafts.length" class="border-t border-line px-4 py-4 sm:px-5">
                            <button
                                type="button"
                                class="w-full rounded-md border border-red-300 px-3 py-2 text-sm font-semibold text-red-700 transition-colors hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10"
                                @click="askRemoveAll"
                            >
                                Удалить все черновики
                            </button>
                        </footer>
                    </aside>
                </div>
            </Transition>
        </Teleport>

        <ConfirmDialog
            :show="pendingDelete !== null"
            :title="confirmTitle"
            :text="confirmText"
            confirm-text="Удалить"
            cancel-text="Отмена"
            @confirm="confirmRemove"
            @cancel="pendingDelete = null"
        />
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import { DRAFTS_UPDATED_EVENT, draftTypeLabel, formatDraftTime, listDrafts, removeDraft } from "@/Utilities/drafts";

const props = defineProps({
    currentKey: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["reset-current"]);

const ALL_DRAFTS = "__all__";

const isOpen = ref(false);
const drafts = ref([]);
const pendingDelete = ref(null);

const refresh = () => {
    drafts.value = listDrafts();
};

const open = () => {
    refresh();
    isOpen.value = true;
};

const close = () => {
    isOpen.value = false;
    pendingDelete.value = null;
};

const draftTitle = (draft) => draft.title || (draft.isNew ? "Без названия" : draft.slug || "Без названия");

const currentAffected = computed(() => pendingDelete.value === ALL_DRAFTS || pendingDelete.value === props.currentKey);

const confirmTitle = computed(() => (pendingDelete.value === ALL_DRAFTS ? "Удалить все черновики?" : "Удалить черновик?"));

const confirmText = computed(() =>
    currentAffected.value
        ? "Сохранённый черновик будет удалён, а форма очищена. Это действие нельзя отменить."
        : "Сохранённый черновик будет удалён. Это действие нельзя отменить.",
);

const askRemove = (key) => {
    pendingDelete.value = key;
};

const askRemoveAll = () => {
    pendingDelete.value = ALL_DRAFTS;
};

const confirmRemove = () => {
    const target = pendingDelete.value;
    const wasCurrent = target === props.currentKey;

    if (target === ALL_DRAFTS) {
        const hadCurrent = drafts.value.some((draft) => draft.key === props.currentKey);
        drafts.value.forEach((draft) => removeDraft(draft.key));
        if (hadCurrent) emit("reset-current");
    } else if (target) {
        removeDraft(target);
        if (wasCurrent) emit("reset-current");
    }

    pendingDelete.value = null;
    refresh();
};

const handleKeydown = (event) => {
    if (event.key === "Escape" && isOpen.value && pendingDelete.value === null) {
        close();
    }
};

onMounted(() => {
    refresh();
    window.addEventListener("focus", refresh);
    window.addEventListener("storage", refresh);
    window.addEventListener(DRAFTS_UPDATED_EVENT, refresh);
    window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener("focus", refresh);
    window.removeEventListener("storage", refresh);
    window.removeEventListener(DRAFTS_UPDATED_EVENT, refresh);
    window.removeEventListener("keydown", handleKeydown);
});
</script>
