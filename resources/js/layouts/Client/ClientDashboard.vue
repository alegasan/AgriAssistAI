<script setup lang="ts">

import ClientNavbar from "@/components/Client/ClientNavbar.vue"
import { Head } from "@inertiajs/vue3";


const props = defineProps<{
    title: string
    description: string
}>()

</script>



<template>
  
  <div class="min-h-screen bg-slate-50">
    <Head :title="props.title" />
    <ClientNavbar />

    <main class="mx-auto w-full max-w-6xl px-4 pb-12 pt-8 sm:px-6 lg:px-8">
      <div class="rounded-2xl border border-emerald-100/70 bg-white/80 px-5 py-4 shadow-sm backdrop-blur-sm sm:px-6 sm:py-5">
        <h3 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ props.title }}</h3>
        <p class="mt-1 text-sm text-slate-600">{{ props.description }}</p>
      </div>
    </main>
    <slot />
  </div>

</template>