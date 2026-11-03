<script setup>
import { onMounted, ref } from "vue";

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        v-model="model"
        ref="input"
        class="h-11 w-full rounded-md border border-line-strong bg-surface-muted px-3 text-sm text-content transition-colors placeholder:text-muted focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
    />
</template>
