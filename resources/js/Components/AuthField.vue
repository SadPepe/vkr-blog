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
        <!-- Плавающая подпись: лежит в поле, на фокусе и при значении встаёт на верхнюю рамку.
             Пустой placeholder нужен только для :placeholder-shown — по нему CSS узнаёт,
             что поле пустое. Подпись перекрывает рамку своим фоном панели. -->
        <div class="relative">
            <input
                :id="id"
                v-model="model"
                :type="type"
                :autocomplete="autocomplete"
                :aria-invalid="error ? 'true' : undefined"
                v-bind="$attrs"
                placeholder=" "
                class="peer w-full border bg-surface px-3.5 py-3 text-[1.0625rem] text-content transition-colors focus:border-accent focus:outline-none"
                :class="[error ? 'border-accent' : 'border-line', $slots.aside ? 'pr-24' : 'pr-3.5']"
            />

            <label
                :for="id"
                class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-surface px-1.5 text-[1.0625rem] leading-none text-muted transition-all duration-150 peer-focus:top-0 peer-focus:text-[0.8125rem] peer-focus:text-accent peer-[:not(:placeholder-shown)]:top-0 peer-[:not(:placeholder-shown)]:text-[0.8125rem] peer-[:not(:placeholder-shown)]:text-content motion-reduce:transition-none"
            >
                {{ label }}
            </label>

            <div v-if="$slots.aside" class="absolute right-3 top-1/2 -translate-y-1/2">
                <slot name="aside" />
            </div>
        </div>

        <p v-if="hint" class="mt-1.5 text-[0.8125rem] leading-snug text-muted">{{ hint }}</p>
        <InputError class="mt-1.5" :message="error" />
    </div>
</template>
