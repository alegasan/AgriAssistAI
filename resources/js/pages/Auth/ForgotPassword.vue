<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Mail } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button'

const form = useForm({
  email: '',
});

const page = usePage();
const statusMessage = computed(() => {
  const status = page.props.status || '';
  if (!status) {
    return '';
  }

  return status === 'passwords.sent'
    ? 'A password reset link has been sent to your email address.'
    : status;
});

function submit() {
  form.post(route('password.email'));
}
</script>


<template>
  <div class="relative min-h-screen flex items-center justify-center overflow-hidden bg-[#f8faf9] font-sans">
    <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-emerald-200/40 blur-3xl" />
    <div class="pointer-events-none absolute -right-20 bottom-0 h-80 w-80 rounded-full bg-emerald-100/70 blur-3xl" />
    <div class="relative w-full max-w-md px-4">
      <div class="bg-white/95 border border-slate-100 shadow-xl rounded-2xl px-8 py-10">
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 mb-6">Forgot Password</h1>
        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label for="email" class="block text-slate-700 mb-1 font-medium">Email</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-600">
                <Mail class="w-5 h-5" aria-hidden="true" />
              </span>
              <input
                v-model="form.email"
                id="email"
                type="email"
                required
                autocomplete="email"
                class="pl-10 pr-3 py-2 input input-bordered w-full border-slate-200 focus-visible:ring-emerald-600 focus:border-emerald-500 rounded-xl transition placeholder:text-slate-400"
                placeholder="you@example.com"
              />
            </div>
            <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
          </div>
          <Button 
          type="submit" 
          class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg py-2.5 w-full transition" 
          :disabled="form.processing">
            {{ form.processing ? 'Sending Reset Link...' : 'Send Reset Link' }}         
         </Button>
          <div v-if="statusMessage" class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100/60 px-3 py-1 text-xs font-semibold text-emerald-700 mt-2">
            <Mail class="w-4 h-4" aria-hidden="true" />
            {{ statusMessage }}
          </div>
        </form>
        <div class="mt-6 text-center">
          <Link :href="route('login')" class="text-emerald-700 hover:underline text-sm font-medium cursor-pointer">Back to login</Link>
        </div>
      </div>
    </div>
  </div>
</template>


