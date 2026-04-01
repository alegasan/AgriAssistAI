<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import InputError from '@/components/ui/form/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
  token: string;
  email?: string | null;
}>();

const form = useForm({
  token: props.token ?? '',
  email: props.email ?? '',
  password: '',
  password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmation = ref(false);

const submit = () => {
  form.post(route('password.update'));
};
</script>

<template>
  <Head title="Reset Password" />
  <div class="relative min-h-screen flex items-center justify-center overflow-hidden bg-[#f8faf9] font-sans">
    <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-emerald-200/40 blur-3xl" />
    <div class="pointer-events-none absolute -right-20 bottom-0 h-80 w-80 rounded-full bg-emerald-100/70 blur-3xl" />
    <div class="relative w-full max-w-md px-4">
      <div class="bg-white/95 border border-slate-100 shadow-xl rounded-2xl px-8 py-10 space-y-6">
        <div class="space-y-2">
          <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Reset Password</h1>
          <p class="text-sm text-slate-600">
            Enter the email that received the reset link and set a new password to get back into your account.
          </p>
        </div>
        <form @submit.prevent="submit" class="space-y-5">
          <div class="flex flex-col gap-1.5">
            <Label for="email" class="text-slate-700">Email</Label>
            <Input
              id="email"
              type="email"
              v-model="form.email"
              required
              autocomplete="email"
              class="border-slate-200 focus-visible:ring-emerald-600"
            />
            <InputError :message="form.errors.email" />
          </div>

          <div class="flex flex-col gap-1.5">
            <Label for="password" class="text-slate-700">New Password</Label>
            <div class="relative">
              <Input
                id="password"
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                required
                autocomplete="new-password"
                class="border-slate-200 focus-visible:ring-emerald-600 pr-16"
              />
              <button
                type="button"
                class="absolute top-1/2 right-3 -translate-y-1/2 text-xs font-semibold text-emerald-700 hover:text-emerald-800"
                @click="showPassword = !showPassword"
              >
                {{ showPassword ? 'Hide' : 'Show' }}
              </button>
            </div>
            <InputError :message="form.errors.password" />
          </div>

          <div class="flex flex-col gap-1.5">
            <Label for="password_confirmation" class="text-slate-700">Confirm Password</Label>
            <div class="relative">
              <Input
                id="password_confirmation"
                :type="showConfirmation ? 'text' : 'password'"
                v-model="form.password_confirmation"
                required
                autocomplete="new-password"
                class="border-slate-200 focus-visible:ring-emerald-600 pr-16"
              />
              <button
                type="button"
                class="absolute top-1/2 right-3 -translate-y-1/2 text-xs font-semibold text-emerald-700 hover:text-emerald-800"
                @click="showConfirmation = !showConfirmation"
              >
                {{ showConfirmation ? 'Hide' : 'Show' }}
              </button>
            </div>
            <InputError :message="form.errors.password_confirmation" />
          </div>

          <Button
            type="submit"
            class="w-full bg-emerald-600 text-white font-semibold shadow-md rounded-xl hover:bg-emerald-700 hover:shadow-lg"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Resetting password...' : 'Reset Password' }}
          </Button>
        </form>
        <div class="text-center text-sm">
          <Link :href="route('login')" class="text-emerald-700 font-medium hover:underline">Back to login</Link>
        </div>
      </div>
    </div>
  </div>
</template>