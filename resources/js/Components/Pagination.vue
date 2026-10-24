<template>
  <div v-if="links && links.length > 3" class="mt-9 flex flex-wrap items-center justify-center">
    <template v-for="(link, index) in links" :key="index">
      <Link
        :href="link.url || '#'"
        :aria-label="getAriaLabel(link, index, links)"
        :aria-current="link.active ? 'page' : undefined"
        :class="[
          'mx-1 my-0.5 rounded px-4 py-2 transition',
          link.active ? 'flex w-10 h-10 justify-center items-center rounded-md bg-emerald-600 text-white dark:bg-emerald-500 dark:text-white pointer-events-none' : 'flex h-10 justify-center items-center rounded-md bg-[#FAF8F4] dark:bg-[#303030] text-gray-900 dark:text-stone-300',
          !link.url ? 'cursor-not-allowed opacity-50' : ''
        ]"
        v-html="link.label"
        preserve-scroll
      />
    </template>
  </div>
</template>


<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    links: {
        type: Array,
        required: true,
        validator: (value) => {
            return Array.isArray(value) && 
                   value.every(link => 'url' in link && 'label' in link)
        }
    }
})

const getAriaLabel = (link, index, links) => {
    if (index === 0) return "Предыдущая страница";
    if (index === links.length - 1) return "Следующая страница";
    const page = parseInt(link.label, 10);
    return Number.isNaN(page) ? `Страница ${index}` : `Страница ${page}`;
};
</script>