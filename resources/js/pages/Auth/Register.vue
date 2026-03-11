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
import { InputError } from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Head, Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const form = useForm({
    name: '',
    email: '',
    username: '',
    password: '',
    password_confirmation: '',
})

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)


const submit = () => {
    form.post(route('register.store'), {
        onSuccess: () => form.reset(),
        onError: () => { form.password = ''; form.password_confirmation = '' },
    })
}

</script>

<template>
    <Head title="Create Account" />

    <div class="relative min-h-screen overflow-hidden bg-[#f8faf9] font-sans">
        <Link
            href="/"
            class="absolute left-6 top-6 z-10 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 hover:text-emerald-800"
        >
            <span aria-hidden="true">←</span>
            Back to welcome
        </Link>
        <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-emerald-200/40 blur-3xl" />
        <div class="pointer-events-none absolute -right-20 bottom-0 h-80 w-80 rounded-full bg-emerald-100/70 blur-3xl" />

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid w-full items-center gap-10 lg:grid-cols-2">
                <div class="hidden space-y-5 lg:block">
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100/60 px-3 py-1 text-xs font-semibold text-emerald-700">
                        Join AgriAssist AI Today
                    </span>
                    <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 xl:text-5xl">
                        Build a healthier farm with smarter decisions.
                    </h1>
                    <p class="max-w-lg text-base leading-relaxed text-slate-600">
                        Create your account to unlock AI-powered plant diagnostics, treatment guidance, and continuous crop monitoring.
                    </p>

                    <div class="grid max-w-md gap-3 text-sm text-slate-600">
                        <div class="flex items-center gap-2 rounded-xl border border-emerald-100 bg-white/80 px-3 py-2 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500" />
                            Unlimited health scan history
                        </div>
                        <div class="flex items-center gap-2 rounded-xl border border-emerald-100 bg-white/80 px-3 py-2 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500" />
                            Actionable treatment recommendations
                        </div>
                        <div class="flex items-center gap-2 rounded-xl border border-emerald-100 bg-white/80 px-3 py-2 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500" />
                            Early prevention and care alerts
                        </div>
                    </div>
                </div>

                <Card class="w-full border border-slate-100 bg-white/95 shadow-xl backdrop-blur-sm lg:ml-auto lg:max-w-md">
                    <CardHeader class="space-y-3 pb-4">
                        <CardTitle class="text-2xl font-bold tracking-tight text-slate-900">
                            Create your account
                        </CardTitle>
                        <CardDescription class="text-slate-600">
                            Start in seconds and keep every plant one step ahead.
                        </CardDescription>
                        <CardAction>
                            <Button as-child variant="link" class="px-0 font-semibold text-emerald-700 hover:text-emerald-800">
                                <Link href="/login">Sign In</Link>
                            </Button>
                        </CardAction>
                    </CardHeader>

                    <CardContent>
                        <form class="space-y-4" @submit.prevent="submit"> 
                            <div class="flex flex-col gap-1.5">
                                <Label for="name" class="text-slate-700">Full Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Jane Doe"
                                    required
                                    :aria-invalid="!!form.errors.name"
                                    class="border-slate-200 focus-visible:ring-emerald-600"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label for="username" class="text-slate-700">Username</Label>
                                <Input
                                    id="username"
                                    v-model="form.username"
                                    type="text"
                                    placeholder="jane_doe"
                                    required
                                    :aria-invalid="!!form.errors.username"
                                    class="border-slate-200 focus-visible:ring-emerald-600"
                                />
                                <InputError :message="form.errors.username" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label for="email" class="text-slate-700">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="you@example.com"
                                    required
                                    :aria-invalid="!!form.errors.email"
                                    class="border-slate-200 focus-visible:ring-emerald-600"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <Label for="password" class="text-slate-700">Password</Label>
                                <div class="relative">
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        placeholder="Create a strong password"
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

                            <div class="flex flex-col gap-1.5">
                                <Label for="password_confirmation" class="text-slate-700">Confirm Password</Label>
                                <div class="relative">
                                    <Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        :type="showPasswordConfirmation ? 'text' : 'password'"
                                        placeholder="Re-enter your password"
                                        required
                                        :aria-invalid="!!form.errors.password_confirmation"
                                        class="border-slate-200 pr-16 focus-visible:ring-emerald-600"
                                    />
                                    <button
                                        type="button"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 text-xs font-semibold text-emerald-700 hover:text-emerald-800"
                                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    >
                                        {{ showPasswordConfirmation ? 'Hide' : 'Show' }}
                                    </button>
                                </div>
                                <InputError :message="form.errors.password_confirmation" />
                            </div>

                        <Button class="w-full rounded-xl bg-emerald-600 font-semibold text-white shadow-md hover:bg-emerald-700 hover:shadow-lg"
                            type="submit"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Creating Account...' : 'Create Account' }}
                        </Button>
                        </form>
                    </CardContent>

                    <CardFooter class="flex flex-col gap-2 pt-0">
                        <Button variant="outline" class="w-full rounded-xl border-slate-200 bg-white font-semibold text-slate-700 hover:bg-slate-50">
                            Sign up with Google
                        </Button>
                        <p class="pt-1 text-center text-xs text-slate-500">
                            By signing up, you agree to our Terms and Privacy Policy.
                        </p>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </div>
</template>