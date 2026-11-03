<template>
    <Layout>
        <Head title="Настройки профиля" />

        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-bold tracking-tight text-content">Настройки профиля</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Данные профиля -->
                <section class="rounded-md border border-line bg-surface p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-content">Данные профиля</h2>
                    <p class="mt-1 text-sm text-muted">Имя, почта и рассказ о себе — их видят читатели блога.</p>

                    <div class="mt-5 space-y-4">
                        <div>
                            <InputLabel for="name" value="Имя" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1.5 block w-full"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Почта" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1.5 block w-full"
                                required
                                autocomplete="username"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="about" value="Обо мне" />
                            <TextArea id="about" v-model="form.about" rows="4" class="mt-1.5 block w-full" autocomplete="about" />
                            <InputError class="mt-2" :message="form.errors.about" />
                        </div>
                    </div>
                </section>

                <!-- Оформление профиля -->
                <section class="rounded-md border border-line bg-surface p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-content">Оформление профиля</h2>
                    <p class="mt-1 text-sm text-muted">
                        Обложка украсит шапку профиля, фон — всю страницу. Подойдут JPG, PNG или WebP до 5 МБ.
                    </p>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <!-- Обложка -->
                        <div>
                            <p class="text-sm font-medium text-content">Фото в шапке профиля</p>

                            <div class="mt-2 overflow-hidden rounded-md border border-line bg-surface-muted">
                                <div class="relative aspect-[16/9] w-full">
                                    <img v-if="coverShown" :src="coverShown" alt="Обложка профиля" class="h-full w-full object-cover" />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center bg-gradient-to-r from-accent to-accent-2 px-3 text-center text-xs font-medium text-accent-contrast"
                                    >
                                        Без обложки
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <label
                                    for="cover_image"
                                    class="inline-flex cursor-pointer items-center justify-center rounded-md border border-line-strong px-3 py-1.5 text-sm font-medium text-content transition-colors hover:bg-surface-muted focus-within:ring-2 focus-within:ring-accent focus-within:ring-offset-2 focus-within:ring-offset-surface"
                                >
                                    {{ coverShown ? "Заменить" : "Загрузить" }}
                                </label>
                                <input
                                    id="cover_image"
                                    ref="coverInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="sr-only"
                                    @change="onCoverChange"
                                />
                                <button
                                    v-if="coverShown"
                                    type="button"
                                    class="rounded-md px-2.5 py-1.5 text-sm font-medium text-red-600 transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-400"
                                    @click="removeCover"
                                >
                                    Удалить
                                </button>
                            </div>

                            <InputError class="mt-2" :message="form.errors.cover_image" />
                        </div>

                        <!-- Фон -->
                        <div>
                            <p class="text-sm font-medium text-content">Фон страницы профиля</p>

                            <div class="mt-2 overflow-hidden rounded-md border border-line bg-surface-muted">
                                <div class="relative aspect-[16/9] w-full">
                                    <img
                                        v-if="backgroundShown"
                                        :src="backgroundShown"
                                        alt="Фон профиля"
                                        class="h-full w-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center bg-page px-3 text-center text-xs font-medium text-muted"
                                    >
                                        Фон темы по умолчанию
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <label
                                    for="background_image"
                                    class="inline-flex cursor-pointer items-center justify-center rounded-md border border-line-strong px-3 py-1.5 text-sm font-medium text-content transition-colors hover:bg-surface-muted focus-within:ring-2 focus-within:ring-accent focus-within:ring-offset-2 focus-within:ring-offset-surface"
                                >
                                    {{ backgroundShown ? "Заменить" : "Загрузить" }}
                                </label>
                                <input
                                    id="background_image"
                                    ref="backgroundInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="sr-only"
                                    @change="onBackgroundChange"
                                />
                                <button
                                    v-if="backgroundShown"
                                    type="button"
                                    class="rounded-md px-2.5 py-1.5 text-sm font-medium text-red-600 transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-400"
                                    @click="removeBackground"
                                >
                                    Удалить
                                </button>
                            </div>

                            <InputError class="mt-2" :message="form.errors.background_image" />
                        </div>
                    </div>
                </section>

                <div class="flex items-center gap-4">
                    <PrimaryButton :disabled="form.processing">Сохранить</PrimaryButton>

                    <span v-if="form.processing && form.progress" class="text-sm text-muted">
                        Загрузка {{ form.progress.percentage }}%
                    </span>

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p v-if="form.recentlySuccessful" class="text-sm text-accent">Сохранено</p>
                    </Transition>
                </div>
            </form>

            <!-- Смена пароля -->
            <section class="mt-6 rounded-md border border-line bg-surface p-6 shadow-sm">
                <UpdatePasswordForm />
            </section>

            <!-- Удаление аккаунта -->
            <section class="mt-6 rounded-md border border-line bg-surface p-6 shadow-sm">
                <DeleteUserForm />
            </section>
        </div>
    </Layout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextArea from "@/Components/TextArea.vue";
import TextInput from "@/Components/TextInput.vue";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: {
        type: Object,
        required: true,
    },
});

const coverInput = ref(null);
const backgroundInput = ref(null);
const coverObjectUrl = ref(null);
const backgroundObjectUrl = ref(null);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    about: props.user.about || "",
    cover_image: null,
    background_image: null,
    remove_cover_image: false,
    remove_background_image: false,
});

const coverShown = computed(() => {
    if (form.cover_image) return coverObjectUrl.value;
    return form.remove_cover_image ? null : props.user.cover_url;
});

const backgroundShown = computed(() => {
    if (form.background_image) return backgroundObjectUrl.value;
    return form.remove_background_image ? null : props.user.background_url;
});

const previewUrl = (file, current) => {
    if (current) URL.revokeObjectURL(current);
    return file ? URL.createObjectURL(file) : null;
};

const onCoverChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    form.cover_image = file;
    form.remove_cover_image = false;
    coverObjectUrl.value = previewUrl(file, coverObjectUrl.value);
};

const onBackgroundChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    form.background_image = file;
    form.remove_background_image = false;
    backgroundObjectUrl.value = previewUrl(file, backgroundObjectUrl.value);
};

const removeCover = () => {
    form.cover_image = null;
    form.remove_cover_image = true;
    coverObjectUrl.value = previewUrl(null, coverObjectUrl.value);
    if (coverInput.value) coverInput.value.value = "";
};

const removeBackground = () => {
    form.background_image = null;
    form.remove_background_image = true;
    backgroundObjectUrl.value = previewUrl(null, backgroundObjectUrl.value);
    if (backgroundInput.value) backgroundInput.value.value = "";
};

const submit = () => {
    form.transform((data) => {
        // Multipart-тело PHP разбирает только у POST, поэтому метод подменяем вручную.
        const payload = { ...data, _method: "PUT" };

        // Не отправляем пустые файлы и «false»-флаги: правило boolean их не примет.
        ["cover_image", "background_image"].forEach((field) => {
            if (!payload[field]) delete payload[field];
        });
        ["remove_cover_image", "remove_background_image"].forEach((field) => {
            if (!payload[field]) delete payload[field];
        });

        return payload;
    }).post(route("profile.update"), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            coverObjectUrl.value = previewUrl(null, coverObjectUrl.value);
            backgroundObjectUrl.value = previewUrl(null, backgroundObjectUrl.value);
            if (coverInput.value) coverInput.value.value = "";
            if (backgroundInput.value) backgroundInput.value.value = "";
            form.reset();
        },
    });
};
</script>
