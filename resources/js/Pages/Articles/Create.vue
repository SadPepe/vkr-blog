<template>
    <MainLayout>
        <Head title="Новая статья" />

        <div v-if="saveError" class="mx-auto mt-4 max-w-[44rem] px-5 sm:px-8" role="alert">
            <div
                class="flex items-start justify-between gap-3 rounded-xl bg-accent-soft px-4 py-3"
            >
                <p class="text-sm text-content">{{ saveError }}</p>
                <button
                    type="button"
                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-muted transition-colors hover:bg-surface hover:text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
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
            @save="saveArticle"
        />

        <DraftsPanel current-key="article-create" @reset-current="resetCurrentDraft" />

        <div class="sticky bottom-0 z-30 mt-8 bg-page px-5 pb-[calc(env(safe-area-inset-bottom)+0.75rem)] pt-2 sm:px-0 sm:pb-4">
            <div class="mx-auto flex max-w-[44rem] items-center justify-between gap-3 sm:px-8">
                <button
                    v-if="editor?.isDirty"
                    type="button"
                    class="inline-flex min-h-11 items-center gap-2 rounded-lg px-3 text-sm font-medium text-muted transition-colors hover:bg-surface hover:text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
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

                <div class="ml-auto flex items-center gap-3">
                    <p class="hidden text-[13px] text-muted md:block">Черновик сохраняется автоматически</p>
                    <button
                        type="button"
                        class="inline-flex h-11 min-w-[9rem] items-center justify-center gap-2 rounded-lg bg-accent px-5 text-sm font-medium text-accent-contrast transition-colors hover:bg-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isSaving"
                        :aria-busy="isSaving"
                        @click="saveArticle"
                    >
                        <svg v-if="!isSaving" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span v-else class="h-4 w-4 animate-spin rounded-full border-2 border-accent-contrast border-t-transparent" />
                        {{ isSaving ? "Сохранение…" : "Сохранить" }}
                    </button>
                </div>
            </div>
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

    // Блокируем кнопку сразу: prepareArticleData может загружать изображения,
    // и до этого момента пользователь успевал нажать «Сохранить» ещё раз.
    isSaving.value = true;
    saveError.value = "";

    try {
        const { formData, assetUrls, error } = await editor.value.prepareArticleData();

        if (error) {
            saveError.value = error;
            isSaving.value = false;
            return;
        }

        if (!formData) {
            isSaving.value = false;
            return;
        }

        router.post(route("articles.store"), formData, {
            preserveScroll: true,
            onSuccess: () => {
                // Очищаем blob-ссылки черновик после успешного сохранения
                editor.value.cleanupBlobUrls(assetUrls);
                editor.value.clearDraft();
            },
            onError: (errors) => {
                saveError.value =
                    Object.values(errors).flat().join(" ") ||
                    "Не удалось сохранить статью. Проверьте поля и попробуйте ещё раз.";
            },
            onFinish: () => {
                isSaving.value = false;
            },
        });
    } catch (error) {
        console.error("Не удалось подготовить статью к сохранению:", error);
        saveError.value = "Не удалось сохранить статью. Попробуйте ещё раз.";
        isSaving.value = false;
    }
};
</script>
