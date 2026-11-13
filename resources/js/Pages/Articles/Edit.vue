<template>
    <MainLayout>
        <Head title="Редактирование статьи" />

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
            :draft-key="draftKey"
            :initial-title="article.title"
            :initial-content="article.content"
            :initial-card-category="article.category_slug"
            @save="updateArticle"
        />

        <DraftsPanel :current-key="draftKey" @reset-current="resetCurrentDraft" />

        <div class="sticky bottom-0 z-30 mt-8 bg-page px-5 pb-[calc(env(safe-area-inset-bottom)+0.75rem)] pt-2 sm:px-0 sm:pb-4">
            <div class="mx-auto flex max-w-[44rem] items-center justify-end gap-3 sm:px-8">
                <p class="hidden text-[13px] text-muted md:block">Изменения сохраняются в статье после нажатия</p>
                <button
                    type="button"
                    class="inline-flex h-11 min-w-[9rem] items-center justify-center gap-2 rounded-lg bg-accent px-5 text-sm font-medium text-accent-contrast transition-colors hover:bg-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="isSaving"
                    :aria-busy="isSaving"
                    @click="updateArticle"
                >
                    <svg v-if="!isSaving" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span v-else class="h-4 w-4 animate-spin rounded-full border-2 border-accent-contrast border-t-transparent" />
                    {{ isSaving ? "Сохранение…" : "Сохранить" }}
                </button>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import DraftsPanel from "@/Components/DraftsPanel.vue";
import TiptapEditor from "@/Components/TipTapEditor.vue";
import MainLayout from "@/Layouts/Layout.vue";

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
});

const editor = ref(null);
const isSaving = ref(false);
const saveError = ref("");

const draftKey = computed(() => `article-${props.article?.slug || "edit"}`);

const resetCurrentDraft = () => {
    editor.value?.resetDraft();
};

const updateArticle = async () => {
    if (isSaving.value || !editor.value) return;

    // См. Create.vue: блокировка нужна до асинхронной подготовки изображений.
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

        router.post(
            route("articles.update", props.article.slug),
            {
                _method: "PUT",
                ...Object.fromEntries(formData),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Очищаем blob-ссылки черновик после успешного сохранения
                    editor.value.cleanupBlobUrls(assetUrls);
                    editor.value.clearDraft();
                },
                onError: (errors) => {
                    saveError.value =
                        Object.values(errors).flat().join(" ") ||
                        "Не удалось сохранить изменения. Проверьте поля и попробуйте ещё раз.";
                },
                onFinish: () => {
                    isSaving.value = false;
                },
            },
        );
    } catch (error) {
        console.error("Не удалось подготовить статью к сохранению:", error);
        saveError.value = "Не удалось сохранить изменения. Попробуйте ещё раз.";
        isSaving.value = false;
    }
};
</script>
