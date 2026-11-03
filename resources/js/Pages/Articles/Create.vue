<template>
    <MainLayout>
        <Head title="Новая статья" />

        <div v-if="saveError" class="mx-auto mt-4 max-w-4xl px-4" role="alert">
            <div
                class="flex items-start justify-between gap-3 rounded-md border border-red-300 bg-red-50 px-4 py-3 dark:border-red-500/30 dark:bg-dark:hover:bg-red-500/10"
            >
                <p class="text-sm text-red-800 dark:text-red-200">{{ saveError }}</p>
                <button
                    type="button"
                    class="rounded p-0.5 text-red-700 transition-colors hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-300 red-500/20"
                    aria-label="Скрыть сообщение"
                    @click="saveError = ''"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <TiptapEditor
            ref="editor"
            draft-key="article-create"
            :initial-title="''"
            :initial-content="''"
            :initial-excerpt="''"
            @save="saveArticle"
        />

        <DraftsPanel current-key="article-create" @reset-current="resetCurrentDraft" />

        <div class="fixed bottom-6 right-6 z-40 flex flex-wrap items-center justify-end gap-2 sm:gap-3">
            <button
                v-if="editor?.isDirty"
                type="button"
                class="inline-flex items-center gap-2 rounded-md border border-line-strong bg-surface px-4 py-3 text-sm font-semibold text-content shadow-md transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                @click="showClearConfirm = true"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                </svg>
                <span class="hidden sm:inline">Очистить черновик</span>
                <span class="sm:hidden">Очистить</span>
            </button>

            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-md bg-accent px-5 py-3 text-sm font-semibold text-accent-contrast shadow-md transition-colors hover:bg-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="isSaving"
                @click="saveArticle"
            >
                <svg v-if="!isSaving" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span v-else class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                {{ isSaving ? "Сохранение..." : "Сохранить" }}
            </button>
        </div>

        <ConfirmDialog
            :show="showClearConfirm"
            title="Очистить черновик?"
            text="Несохранённые изменения будут удалены, а форма вернётся к пустой. Это действие нельзя отменить."
            confirm-text="Очистить"
            cancel-text="Отмена"
            @confirm="resetCurrentDraft"
            @cancel="showClearConfirm = false"
        />
    </MainLayout>
</template>

<script setup>
import { ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import DraftsPanel from "@/Components/DraftsPanel.vue";
import TiptapEditor from "@/Components/TipTapEditor.vue";
import MainLayout from "@/Layouts/Layout.vue";

const editor = ref(null);
const isSaving = ref(false);
const saveError = ref("");
const showClearConfirm = ref(false);

const resetCurrentDraft = () => {
    showClearConfirm.value = false;
    editor.value?.resetDraft();
};

const saveArticle = async () => {
    if (isSaving.value || !editor.value) return;

    const { formData, assetUrls, error } = await editor.value.prepareArticleData();

    if (error) {
        saveError.value = error;
        return;
    }

    if (!formData) return;

    isSaving.value = true;

    // Inertia отправляет запрос асинхронно — сбрасываем флаг в onFinish,
    // а не сразу после вызова (иначе возможна двойная отправка).
    router.post(route("articles.store"), formData, {
        preserveScroll: true,
        onSuccess: () => {
            // Очищаем blob-ссылки черновик после успешного сохранения
            editor.value.cleanupBlobUrls(assetUrls);
            editor.value.clearDraft();
        },
        onError: (errors) => {
            saveError.value = Object.values(errors).flat().join(" ") || "Не удалось сохранить статью. Проверьте поля и попробуйте ещё раз.";
        },
        onFinish: () => {
            isSaving.value = false;
        },
    });
};
</script>
