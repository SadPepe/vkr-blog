<template>
    <MainLayout>
      <div class="fixed bottom-6 right-6 z-40">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-green-400 to-emerald-600 rounded-xl blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                <button
                    @click="updateArticle"
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
            :initial-title="article.title"
            :initial-content="article.content"
            :initial-excerpt="article.excerpt"
            :initial-card-image="article.card_image_url"
            :initial-card-category="article.category_slug"
            @save="updateArticle"
        />
    </MainLayout>
</template>

<script setup>
import { ref } from "vue";
import TiptapEditor from "@/Components/TipTapEditor.vue";
import MainLayout from "@/Layouts/Layout.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    article: Object
});

const editor = ref(null);

const updateArticle = async () => {
    if (!editor.value) {
        console.error('Editor component is not ready');
        return;
    }

    try {
        const { formData, blobUrls } = await editor.value.prepareArticleData();
        
        await router.post(route('articles.update', props.article.slug), {
            _method: 'PUT',
            ...Object.fromEntries(formData)
        }, {
            onSuccess: () => {
                if (blobUrls) {
                    editor.value.cleanupBlobUrls(blobUrls);
                }
            },
            onError: (errors) => {
                console.error('Ошибка обновления:', errors);
            }
        });
    } catch (error) {
        console.error('Ошибка:', error);
    }
};
</script>
