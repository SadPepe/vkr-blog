<template>
<Layout><div class="mx-auto max-w-3xl">
  <form @submit.prevent="submit">
    <div>
      <InputLabel for="name" value="Name" />
      <TextInput
        id="name"
        v-model="form.name"
        type="text"
        class="mt-1 block w-full"
        required
        autofocus
        autocomplete="name"
      />
      <InputError class="mt-2" :message="form.errors.name" />
    </div>

    <div class="mt-4">
      <InputLabel for="email" value="Email" />
      <TextInput
        id="email"
        v-model="form.email"
        type="email"
        class="mt-1 block w-full"
        required
        autocomplete="username"
      />
      <InputError class="mt-2" :message="form.errors.email" />
    </div>
     <div class="mt-4">
      <InputLabel for="about" value="Обо мне" />
      <TextArea
        id="about"
        v-model="form.about"
        class="mt-1 block w-full dark:bg-[#303030] dark:border-stone-600 dark:text-stone-300"
        rows="4"
        autocomplete="about"
      />
      <InputError class="mt-2" :message="form.errors.about" />
    </div>

    <div class="flex items-center gap-4 mt-4">
      <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
      <Transition
        enter-active-class="transition ease-in-out"
        enter-from-class="opacity-0"
        leave-active-class="transition ease-in-out"
        leave-to-class="opacity-0"
      >
        <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
      </Transition>
    </div>
  </form>
  </div>
  </Layout>
</template>

<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';

const props = defineProps({
  mustVerifyEmail: Boolean,
  status: String,
  user:{type: Object},
});

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  about: props.user.about || '',
});

const submit = () => {
  form.put(route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};
</script>