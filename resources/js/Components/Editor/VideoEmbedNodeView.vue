<template>
    <NodeViewWrapper
        class="editor-video my-5 overflow-hidden rounded-xl border bg-surface transition-colors"
        :class="selected ? 'border-accent ring-2 ring-accent/20' : 'border-line'"
        contenteditable="false"
        data-testid="video-embed-editor"
    >
        <div class="relative aspect-video bg-surface-muted">
            <iframe
                v-if="embedUrl"
                :src="embedUrl"
                :title="`Видео ${providerLabel}`"
                class="pointer-events-none absolute inset-0 h-full w-full"
                loading="lazy"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
                referrerpolicy="no-referrer"
            ></iframe>
            <div v-else class="flex h-full items-center justify-center px-6 text-center text-sm text-muted">
                Не удалось распознать видео. Удалите блок и вставьте ссылку ещё раз.
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-line px-3 py-2 text-sm">
            <span class="font-medium text-content">{{ providerLabel }}</span>
            <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm font-medium text-muted transition-colors hover:bg-surface-muted hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                @click="deleteNode"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                </svg>
                Удалить видео
            </button>
        </div>
    </NodeViewWrapper>
</template>

<script setup>
import { computed } from "vue";
import { NodeViewWrapper } from "@tiptap/vue-3";
import { buildVideoEmbedUrl, videoProviderLabel } from "@/Support/videoEmbed";

const props = defineProps({
    node: { type: Object, required: true },
    selected: { type: Boolean, default: false },
    deleteNode: { type: Function, required: true },
});

const providerLabel = computed(() => videoProviderLabel(props.node.attrs.provider));
const embedUrl = computed(() =>
    buildVideoEmbedUrl({
        provider: props.node.attrs.provider,
        videoId: props.node.attrs.videoId,
        hash: props.node.attrs.hash,
    }),
);
</script>
