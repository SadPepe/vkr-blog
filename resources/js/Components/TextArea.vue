<template>
    <textarea
        :id="id"
        :class="['rounded-md border border-line-strong bg-surface-muted text-content placeholder:text-muted shadow-sm focus:border-accent focus:ring-accent', 'block w-full']"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        v-bind="$attrs"
    ></textarea>
</template>

<script setup>
defineProps({
    id: String,
    modelValue: String,
});

defineEmits(["update:modelValue"]);
</script>
