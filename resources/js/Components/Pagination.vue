<template>
  <div v-if="links && links.length > 3" class="mt-9 flex flex-row flex-nowrap justify-between md:justify-center items-center">
    <template v-for="(link, index) in links" :key="index">
      <Link
        :href="link.url || '#'"
        :class="[
          'px-4 py-2 mx-1 rounded transition',
          link.active ? 'md:flex w-10 h-10 mx-1 justify-center items-center rounded-full dark:bg-[#090909] dark:text-stone-200 pointer-events-none' : 'md:flex h-10 mx-1 justify-center items-center rounded-full bg-white dark:bg-[#303030] text-gray-900 dark:text-stone-300',
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
</script>