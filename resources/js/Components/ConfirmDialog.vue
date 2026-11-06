<template>
    <Modal :show="show" max-width="md" @close="cancel">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-content">
                {{ title }}
            </h2>
            <p class="mt-2 text-sm leading-relaxed text-muted">
                {{ text }}
            </p>

            <div class="mt-6 flex justify-end gap-2.5">
                <button
                    type="button"
                    class="rounded border border-line-strong bg-surface px-4 py-2 text-sm font-semibold text-content transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                    @click="cancel"
                >
                    {{ cancelText }}
                </button>
                <button
                    type="button"
                    class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
                    @click="$emit('confirm')"
                >
                    {{ confirmText }}
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from "@/Components/Modal.vue";

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: "Подтвердите действие",
    },
    text: {
        type: String,
        default: "",
    },
    confirmText: {
        type: String,
        default: "Удалить",
    },
    cancelText: {
        type: String,
        default: "Отмена",
    },
});

const emit = defineEmits(["confirm", "cancel"]);

const cancel = () => emit("cancel");
</script>
