<!-- «Поделиться» — одна кнопка-стрелка: копирует ссылку на статью.
     Подтверждение — галочка и акцентный цвет, для скринридера — aria-live. -->
<template>
    <button
        type="button"
        class="post-action"
        :class="copied ? 'text-accent' : ''"
        :aria-label="copied ? 'Ссылка скопирована' : 'Скопировать ссылку на статью'"
        :title="copied ? 'Ссылка скопирована' : 'Скопировать ссылку'"
        @click="copy"
    >
        <svg v-if="!copied" class="h-[1.125rem] w-[1.125rem] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H9M17 7v8" />
        </svg>
        <svg v-else class="h-[1.125rem] w-[1.125rem] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>

        <span class="sr-only" aria-live="polite">{{ copied ? "Ссылка скопирована" : "" }}</span>
    </button>
</template>

<script setup>
import { onBeforeUnmount, ref } from "vue";

const props = defineProps({
    url: {
        type: String,
        required: true,
    },
});

const copied = ref(false);
let copiedTimer = null;

const copy = async () => {
    try {
        await navigator.clipboard.writeText(props.url);
    } catch {
        // Резервный путь для браузеров без Clipboard API (http, старые движки)
        const input = document.createElement("textarea");
        input.value = props.url;
        input.setAttribute("readonly", "");
        input.style.position = "absolute";
        input.style.left = "-9999px";
        document.body.appendChild(input);
        input.select();
        document.execCommand("copy");
        document.body.removeChild(input);
    }

    copied.value = true;
    clearTimeout(copiedTimer);
    copiedTimer = setTimeout(() => {
        copied.value = false;
    }, 2500);
};

onBeforeUnmount(() => clearTimeout(copiedTimer));
</script>
