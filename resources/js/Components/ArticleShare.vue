<!-- Ссылки на пост: на телефоне — системный лист и копирование,
     с sm — три пилюли-иконки. -->
<template>
    <div class="flex items-center gap-1.5">
        <!-- Копировать ссылку: подтверждение — галочка и акцентная пилюля -->
        <button
            type="button"
            class="ui-pill min-h-11 min-w-11 px-0 sm:min-h-10 sm:min-w-10"
            :class="copied ? 'ui-pill--active' : ''"
            :aria-label="copied ? 'Ссылка скопирована' : 'Скопировать ссылку'"
            :title="copied ? 'Ссылка скопирована' : 'Скопировать ссылку'"
            @click="copy"
        >
            <svg v-if="!copied" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 11-5.656-5.656l1.5-1.5m9.656-1.5l1.5-1.5a4 4 0 10-5.656-5.656l-3 3a4 4 0 000 5.656"
                />
            </svg>
            <svg v-else class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </button>

        <!-- Системный лист: одна кнопка вместо двух целей.
             Нет листа — кнопка копирует ссылку и меняет подпись -->
        <button
            type="button"
            class="ui-pill ui-pill--solid min-h-11 gap-1.5 sm:hidden"
            :aria-label="copied ? 'Ссылка скопирована' : 'Поделиться'"
            @click="share"
        >
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M4 15v3.5A1.5 1.5 0 005.5 20h13a1.5 1.5 0 001.5-1.5V15" />
            </svg>
            {{ copied ? "Ссылка скопирована" : "Поделиться" }}
        </button>

        <!-- Соцсети — с sm; на телефоне их закрывает одна кнопка выше -->
        <a
            :href="telegramUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="ui-pill hidden min-h-11 min-w-11 px-0 sm:inline-flex sm:min-h-10 sm:min-w-10"
            aria-label="Поделиться в Telegram"
            title="Telegram"
        >
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 3L3.5 10.4l5.6 2.1L11 19.5l3.1-3.9 4.3 2.9L21 3z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.1 12.5L21 3" />
            </svg>
        </a>
        <a
            :href="vkUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="ui-pill hidden min-h-11 min-w-11 px-0 sm:inline-flex sm:min-h-10 sm:min-w-10"
            aria-label="Поделиться во ВКонтакте"
            title="ВКонтакте"
        >
            <span class="text-[0.8125rem] font-semibold leading-none tracking-[0.08em]" aria-hidden="true">VK</span>
        </a>

        <span class="sr-only" aria-live="polite">{{ copied ? "Ссылка скопирована" : "" }}</span>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref } from "vue";

const props = defineProps({
    url: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: "",
    },
});

// Системный лист есть не везде: на десктопе и в старых движках его нет
const canNativeShare = typeof navigator !== "undefined" && typeof navigator.share === "function";

const telegramUrl = computed(
    () => `https://t.me/share/url?url=${encodeURIComponent(props.url)}&text=${encodeURIComponent(props.title)}`,
);

const vkUrl = computed(
    () => `https://vk.com/share.php?url=${encodeURIComponent(props.url)}&title=${encodeURIComponent(props.title)}`,
);

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

const share = () => {
    if (canNativeShare) {
        // Отмену листа (AbortError) не считаем ошибкой
        navigator.share({ title: props.title, url: props.url }).catch(() => {});
        return;
    }

    // Без системного листа «Поделиться» отдаёт ссылку в буфер
    copy();
};

onBeforeUnmount(() => clearTimeout(copiedTimer));
</script>
