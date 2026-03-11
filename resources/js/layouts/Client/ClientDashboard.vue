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

    <main class="mx-auto w-full max-w-6xl px-4 pb-16 pt-8">
      <h3 class="text-3xl font-bold text-slate-900">{{ props.title }}</h3>
      <p class="mt-2 text-muted-foreground text-slate-700">{{ props.description }}</p>
    </main>
  </div>

</template>