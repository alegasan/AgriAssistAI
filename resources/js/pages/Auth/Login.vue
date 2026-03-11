<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardAction,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Head, Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import InputError from '@/components/ui/form/InputError.vue'

const form = useForm({
  login: '',
  password: '',
})

const showPassword = ref(false)

const submit = () => {
  form.post(route('login.store'), {
    onError: () => { form.password = '' },
  })
}

</script>

<template>
  <Head title="Login" />
  <div class="relative min-h-screen overflow-hidden bg-[#f8faf9] font-sans">
    <Link
      href="/"
      class="absolute left-6 top-6 z-10 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 hover:text-emerald-800"
    >
      <span aria-hidden="true">←</span>
      Back
    </Link>
    <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-emerald-200/40 blur-3xl" />
    <div class="pointer-events-none absolute -right-20 bottom-0 h-80 w-80 rounded-full bg-emerald-100/70 blur-3xl" />

    <div class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
      <div class="grid w-full items-center gap-10 lg:grid-cols-2">
        <div class="hidden space-y-5 lg:block">
          <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100/60 px-3 py-1 text-xs font-semibold text-emerald-700">
            Welcome Back to AgriAssist AI
          </span>
          <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 xl:text-5xl">
            Keep your crops protected with every login.
          </h1>
          <p class="max-w-lg text-base leading-relaxed text-slate-600">
            Access your scan history, monitor crop health trends, and get actionable disease insights powered by AI.
          </p>

          <div class="grid max-w-md gap-3 text-sm text-slate-600">
            <div class="flex items-center gap-2 rounded-xl border border-emerald-100 bg-white/80 px-3 py-2 shadow-sm">
              <span class="h-2 w-2 rounded-full bg-emerald-500" />
              Instant diagnosis results
            </div>
            <div class="flex items-center gap-2 rounded-xl border border-emerald-100 bg-white/80 px-3 py-2 shadow-sm">
              <span class="h-2 w-2 rounded-full bg-emerald-500" />
              Personalized treatment plans
            </div>
            <div class="flex items-center gap-2 rounded-xl border border-emerald-100 bg-white/80 px-3 py-2 shadow-sm">
              <span class="h-2 w-2 rounded-full bg-emerald-500" />
              Smart prevention alerts
            </div>
          </div>
        </div>

        <Card class="w-full border border-slate-100 bg-white/95 shadow-xl backdrop-blur-sm lg:ml-auto lg:max-w-md">
          <CardHeader class="space-y-3 pb-4">
            <CardTitle class="text-2xl font-bold tracking-tight text-slate-900">
              Login to your account
            </CardTitle>
            <CardDescription class="text-slate-600">
              Enter your credentials to continue monitoring your plants.
            </CardDescription>
            <CardAction>
              <Link href="/register" class="ml-4 text-sm font-medium text-emerald-700 underline-offset-4 hover:underline">
                Create an account
              </Link>
            </CardAction>
          </CardHeader>

          <CardContent>
            <form class="space-y-4" @submit.prevent="submit">
              <div class="grid w-full items-center gap-4">
                <div class="flex flex-col gap-1.5">
                  <Label for="login" class="text-slate-700">Email or Username</Label>
                  <Input
                    id="login"
                    v-model="form.login"
                    type="text"
                    placeholder="you@example.com or username"
                    required
                    :aria-invalid="!!form.errors.login"
                    class="border-slate-200 focus-visible:ring-emerald-600"
                  />
                  <InputError :message="form.errors.login" />
                </div>
                <div class="flex flex-col gap-1.5">
                  <div class="flex items-center">
                    <Label for="password" class="text-slate-700">Password</Label>
                    <a
                      href="#"
                      class="ml-auto inline-block text-sm font-medium text-emerald-700 underline-offset-4 hover:underline"
                    >
                      Forgot your password?
                    </a>
                  </div>
                  <div class="relative">
                    <Input
                      id="password"
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      :aria-invalid="!!form.errors.password"
                      class="border-slate-200 pr-16 focus-visible:ring-emerald-600"
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
              </div>
            </form>
          </CardContent>

          <CardFooter class="flex flex-col gap-2 pt-0">
            <Button
              type="submit"
              :disabled="form.processing"
              class="w-full rounded-xl bg-emerald-600 font-semibold text-white shadow-md hover:bg-emerald-700 hover:shadow-lg"
              @click="submit"
            >
              {{ form.processing ? 'Logging in...' : 'Login' }}
            </Button>
            <Button variant="outline" class="w-full rounded-xl border-slate-200 bg-white font-semibold text-slate-700 hover:bg-slate-50">
              Login with Google
            </Button>
          </CardFooter>
        </Card>
      </div>
    </div>
  </div>
</template>
