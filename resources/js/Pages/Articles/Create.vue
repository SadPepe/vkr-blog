<template>
    <MainLayout>
        <div class="fixed bottom-6 right-6 z-40">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-green-400 to-emerald-600 rounded-xl blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                
                <button
                    type="button"
                    @click="saveArticle"
                    :disabled="isSaving"
                    :class="[
                        'relative px-6 py-3 text-white font-semibold rounded-xl shadow-2xl',
                        'transition-all duration-300 transform',
                        'flex items-center space-x-2',
                        isSaving 
                            ? 'bg-gray-500 cursor-not-allowed' 
                            : 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700',
                        !isSaving && 'hover:scale-110 hover:shadow-3xl'
                    ]"
                >
                    <svg v-if="!isSaving" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div v-else class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span>{{ isSaving ? 'Сохранение...' : 'Сохранить' }}</span>
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
    </MainLayout>
</template>

<script setup>
import { ref } from "vue";
import TiptapEditor from "@/Components/TipTapEditor.vue";
import MainLayout from "@/Layouts/Layout.vue";
import { router } from "@inertiajs/vue3";

const editor = ref(null);
const isSaving = ref(false);

const saveArticle = async () => {
    if (isSaving.value || !editor.value) return;

    const { formData, assetUrls, error } = await editor.value.prepareArticleData();

    if (error) {
        window.alert(error);
        return;
    }

    if (!formData) return;

    isSaving.value = true;

    // Inertia отправляет запрос асинхронно — сбрасываем флаг в onFinish,
    // а не сразу после вызова (иначе возможна двойная отправка).
    router.post(route("articles.store"), formData, {
        preserveScroll: true,
        onSuccess: () => {
            // Очищаем blob-ссылки и черновик после успешного сохранения
            editor.value.cleanupBlobUrls(assetUrls);
            editor.value.clearDraft();
            lastSaved.value = new Date().toLocaleTimeString("ru-RU");
        },
        onError: (errors) => {
            console.error("Ошибка сохранения:", errors);
            window.alert("Ошибка при сохранении статьи");
        },
        onFinish: () => {
            isSaving.value = false;
        },
    });
};

const lastSaved = ref(null);
</script>

<style scoped>
.shadow-3xl {
    box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.25);
}

.group:hover button:not(:disabled) {
    animation: pulse-glow 2s ease-in-out infinite;
}

.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>