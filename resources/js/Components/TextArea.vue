<template>
  <textarea
    :id="id"
    :class="[
      'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm',
      'block w-full',
    ]"
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

defineEmits(['update:modelValue']);
</script>