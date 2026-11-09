<template>
    <div
        v-if="visible"
        class="announcement-ticker relative flex items-center border-b border-line bg-accent-soft text-accent"
        role="region"
        aria-label="Объявления сайта"
    >
        <div class="min-w-0 flex-1 overflow-hidden py-2">
            <div class="announcement-ticker__track flex w-max" :style="{ '--ticker-duration': duration + 's' }">
                <div v-for="copy in 2" :key="copy" class="flex shrink-0" :aria-hidden="copy === 2 ? 'true' : undefined">
                    <span v-for="item in items" :key="item.id" class="flex items-center gap-3 pr-8 text-xs whitespace-nowrap sm:text-sm">
                        <span class="h-1 w-1 shrink-0 rounded-full bg-accent/50" aria-hidden="true"></span>
                        <component
                            :is="item.url ? 'a' : 'span'"
                            :href="item.url || undefined"
                            class="font-medium underline-offset-4"
                            :class="item.url ? 'underline decoration-accent/40 hover:decoration-accent' : ''"
                        >
                            {{ item.body }}
                        </component>
                    </span>
                </div>
            </div>
        </div>

        <button
            type="button"
            class="my-1 mr-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-accent/70 transition-colors hover:bg-accent/10 hover:text-accent focus-visible:ring-2 focus-visible:ring-accent focus-visible:outline-none"
            aria-label="Скрыть объявления"
            @click="dismiss"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

const STORAGE_KEY = "announcements-dismissed";

const page = usePage();
const items = computed(() => page.props.announcements ?? []);

// До монтирования строку не показываем: иначе мелькает закрытая пользователем
const dismissed = ref(true);

const signature = computed(() => items.value.map((item) => item.id).join("-"));

onMounted(() => {
    dismissed.value = localStorage.getItem(STORAGE_KEY) === signature.value;
});

const visible = computed(() => items.value.length > 0 && !dismissed.value);

// Скорость от длины текста: короткое объявление не пролетает за секунду,
// длинное — не тянется минуту.
const duration = computed(() => {
    const length = items.value.reduce((sum, item) => sum + (item.body?.length || 0), 0);
    return Math.min(70, Math.max(20, Math.round(length * 0.22 + 14)));
});

const dismiss = () => {
    localStorage.setItem(STORAGE_KEY, signature.value);
    dismissed.value = true;
};
</script>

<style>
.announcement-ticker__track {
    animation: announcement-ticker-scroll var(--ticker-duration, 30s) linear infinite;
    will-change: transform;
}

.announcement-ticker:hover .announcement-ticker__track {
    animation-play-state: paused;
}

/* Уважаем настройку «меньше движения»: строка стоит, а не бежит. */
@media (prefers-reduced-motion: reduce) {
    .announcement-ticker__track {
        animation: none;
        white-space: normal;
    }
}

@keyframes announcement-ticker-scroll {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-50%);
    }
}
</style>
