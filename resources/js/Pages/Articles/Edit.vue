<template>
    <MainLayout>
        <Head title="Редактирование статьи" />

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
            :draft-key="draftKey"
            :initial-title="article.title"
            :initial-content="article.content"
            :initial-excerpt="article.excerpt"
            :initial-card-image="article.card_image_url"
            :initial-card-category="article.category_slug"
            @save="updateArticle"
        />

        <div class="fixed bottom-6 right-6 z-40">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-md bg-accent px-5 py-3 text-sm font-semibold text-accent-contrast shadow-md transition-colors hover:bg-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="isSaving"
                @click="updateArticle"
            >
                <svg v-if="!isSaving" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span v-else class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                {{ isSaving ? "Сохранение..." : "Сохранить" }}
            </button>
        </div>

        <DraftsPanel :current-key="draftKey" @reset-current="resetCurrentDraft" />
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

    const { formData, assetUrls, error } = await editor.value.prepareArticleData();

    if (error) {
        saveError.value = error;
        return;
    }

    if (!formData) return;

    isSaving.value = true;

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
                    Object.values(errors).flat().join(" ") || "Не удалось сохранить изменения. Проверьте поля и попробуйте ещё раз.";
            },
            onFinish: () => {
                isSaving.value = false;
            },
        },
    );
};
</script>
