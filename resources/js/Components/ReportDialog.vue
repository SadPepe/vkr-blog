<template>
    <Modal :show="show" max-width="md" @close="close">
        <form class="p-6" @submit.prevent="submit">
            <h2 class="font-display text-lg font-semibold text-content">Пожаловаться</h2>
            <p class="mt-1 text-sm text-muted">Модератор проверит материал и примет решение.</p>

            <fieldset class="mt-4">
                <legend class="text-sm font-medium text-content">Причина</legend>
                <div class="mt-2 space-y-2">
                    <label
                        v-for="(label, key) in reasons"
                        :key="key"
                        class="flex cursor-pointer items-start gap-2 text-sm text-content"
                    >
                        <input
                            v-model="form.reason"
                            type="radio"
                            :value="key"
                            class="mt-0.5 border-line-strong text-accent focus:ring-accent"
                        />
                        <span>{{ label }}</span>
                    </label>
                </div>
            </fieldset>
            <InputError :message="form.errors.reason" class="mt-2" />

            <label for="report-comment" class="mt-4 block text-sm font-medium text-content">
                Пояснение <span class="font-normal text-muted">(необязательно)</span>
            </label>
            <Textarea
                id="report-comment"
                v-model="form.comment"
                rows="3"
                maxlength="500"
                class="mt-1"
                placeholder="Что именно не так"
            />
            <InputError :message="form.errors.comment" class="mt-2" />

            <div class="mt-5 flex justify-end gap-2">
                <SecondaryButton type="button" @click="close">Отмена</SecondaryButton>
                <PrimaryButton :disabled="form.processing">
                    {{ form.processing ? "Отправка…" : "Отправить" }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import { computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import InputError from "@/Components/InputError.vue";
import Textarea from "@/Components/TextArea.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    show: { type: Boolean, default: false },
    type: { type: String, required: true },
    id: { type: Number, required: true },
});

const emit = defineEmits(["close"]);
const page = usePage();
const reasons = computed(() => page.props.reportReasons ?? {});

const form = useForm({
    reportable_type: props.type,
    reportable_id: props.id,
    reason: "spam",
    comment: "",
});

const close = () => {
    form.reset();
    form.clearErrors();
    emit("close");
};

const submit = () => {
    form.post(route("reports.store"), {
        preserveScroll: true,
        onSuccess: () => close(),
    });
};
</script>
