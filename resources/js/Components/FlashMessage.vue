<template>
    <div
<<<<<<< HEAD
        class="pointer-events-none fixed inset-x-4 top-24 z-50 flex flex-col items-center gap-2 sm:inset-x-auto sm:right-6 sm:items-end"
=======
        class="pointer-events-none fixed inset-x-4 top-5 z-50 flex flex-col items-center gap-2 sm:inset-x-auto sm:right-6 sm:items-end"
>>>>>>> my-saved-changes
        aria-live="polite"
        aria-atomic="true"
    >
        <TransitionGroup
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-for="message in messages"
                :key="message.id"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-md border px-4 py-3 text-sm shadow-lg"
                :class="message.type === 'error' ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-500/30 dark:bg-dark:hover:bg-red-500/10 dark:text-red-200' : 'border-accent/30 bg-accent-soft text-accent'"
                :role="message.type === 'error' ? 'alert' : 'status'"
            >
                <svg
                    class="mt-0.5 h-4 w-4 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        v-if="message.type === 'error'"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 0 001.71 3h16.94a2 0 001.71-3L13.71 3.86a2 0 00-3.42 0z"
                    />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="flex-1">{{ message.text }}</span>
                <button
                    type="button"
                    class="shrink-0 rounded p-0.5 opacity-60 transition-opacity hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-current"
                    aria-label="Закрыть уведомление"
                    @click="dismiss(message.id)"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
import { onBeforeUnmount, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const messages = ref([]);
const timers = new Map();
let seq = 0;

const push = (type, text) => {
    if (!text) return;

    const id = ++seq;
    messages.value.push({ id, type, text });
    timers.set(
        id,
        setTimeout(() => dismiss(id), 5000),
    );
};

const dismiss = (id) => {
    messages.value = messages.value.filter((message) => message.id !== id);
    clearTimeout(timers.get(id));
    timers.delete(id);
};

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        push("success", flash.success);
        push("error", flash.error);
    },
    { deep: true, immediate: true },
);

onBeforeUnmount(() => {
    timers.forEach((timer) => clearTimeout(timer));
    timers.clear();
});
</script>
