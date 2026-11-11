<script setup>
import InputError from "@/Components/InputError.vue";

defineOptions({ inheritAttrs: false });

const model = defineModel({
    type: String,
    required: true,
});

defineProps({
    id: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        default: "text",
    },
    hint: {
        type: String,
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
    autocomplete: {
        type: String,
        default: undefined,
    },
});
</script>

<template>
    <div>
        <div class="flex items-baseline justify-between gap-3">
            <label :for="id" class="text-[0.9375rem] font-medium leading-snug text-content">{{ label }}</label>
            <slot name="aside" />
        </div>

        <input
            :id="id"
            v-model="model"
            :type="type"
            :autocomplete="autocomplete"
            v-bind="$attrs"
            class="mt-1.5 w-full border border-line bg-page px-3.5 py-2.5 text-[1.0625rem] text-content transition-colors placeholder:text-muted focus:border-accent focus:outline-none"
        />

        <p v-if="hint" class="mt-1.5 text-[0.8125rem] leading-snug text-muted">{{ hint }}</p>
        <InputError class="mt-1.5" :message="error" />
    </div>
</template>
