<template>
    <Modal :show="show" max-width="lg" centered blur @close="cancel">
        <div class="p-7 text-center sm:p-8">
            <div class="flex items-center justify-center gap-2.5" aria-label="Челны подземка">
                <svg
                    class="h-9 w-9 shrink-0 text-accent"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path d="M4 21V10.5a8 8 0 0 1 16 0V21" />
                    <path d="M8 21v-4.5h4V12h4" />
                </svg>
                <span class="font-sans text-[1.5rem] font-bold leading-none tracking-[-0.02em] text-content">
                    Челны <span class="text-accent">подземка</span>
                </span>
            </div>

            <h2 class="mt-6 text-xl font-semibold text-content">Вы покидаете сайт</h2>
            <p class="mx-auto mt-2 max-w-[34rem] text-[0.9375rem] leading-relaxed text-muted">
                Ссылка ведёт на внешний ресурс. Переходите по ней, только если доверяете источнику и проверили адрес.
            </p>

            <p class="mx-auto mt-5 max-w-[34rem] break-all rounded-md border border-line bg-surface-muted px-4 py-3 text-sm leading-relaxed text-content">
                {{ pendingUrl }}
            </p>

            <div class="mt-7 flex justify-center gap-3">
                <button
                    type="button"
                    class="rounded border border-line-strong bg-surface px-5 py-2.5 text-sm font-semibold text-content transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                    @click="cancel"
                >
                    Остаться
                </button>
                <button
                    type="button"
                    class="rounded bg-accent px-5 py-2.5 text-sm font-semibold text-accent-contrast shadow-sm transition-colors hover:bg-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                    @click="confirm"
                >
                    Перейти
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import Modal from "@/Components/Modal.vue";

const show = ref(false);
const pendingUrl = ref("");

const isHttpUrl = (url) => url.protocol === "http:" || url.protocol === "https:";

const handleDocumentClick = (event) => {
    if (event.defaultPrevented || event.button !== 0) {
        return;
    }

    const target = event.target instanceof Element ? event.target.closest("a[href]") : null;
    if (!target || !target.closest(".article-body, .post-content, .comment-content")) {
        return;
    }

    let url;
    try {
        url = new URL(target.href, window.location.href);
    } catch {
        return;
    }

    // Внутренние переходы, якоря, mailto: и tel: не требуют предупреждения.
    if (!isHttpUrl(url) || url.origin === window.location.origin) {
        return;
    }

    event.preventDefault();
    pendingUrl.value = url.href;
    show.value = true;
};

const cancel = () => {
    show.value = false;
    pendingUrl.value = "";
};

const confirm = () => {
    const url = pendingUrl.value;
    cancel();

    if (!url) {
        return;
    }

    // Открываем через временную ссылку: браузер сохраняет user gesture
    // и не блокирует новую вкладку как popup.
    const link = document.createElement("a");
    link.href = url;
    link.target = "_blank";
    link.rel = "noopener noreferrer";
    link.style.display = "none";
    document.body.appendChild(link);
    link.click();
    link.remove();
};

onMounted(() => document.addEventListener("click", handleDocumentClick));
onUnmounted(() => document.removeEventListener("click", handleDocumentClick));
</script>
