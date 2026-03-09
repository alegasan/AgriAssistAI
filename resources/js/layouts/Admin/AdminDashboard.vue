<script lang="ts" setup>
import { Head } from '@inertiajs/vue3'
import AdminNavbar from '@/components/Admin/AdminNavbar.vue'

const pageTitle = 'Admin Dashboard'
const props = defineProps<{
    title: string
    description: string
}>()
</script>

<template>

    <div class="min-h-screen bg-slate-50">

        <Head :title="pageTitle" />
        <AdminNavbar />

        <main class="mx-auto w-full max-w-6xl px-4 pb-16 pt-10">
            <section class="space-y-2">
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ props.title }}</h1>
                <p class="text-slate-600">{{ props.description }}</p>
            </section>
            <slot />
        </main>
    </div>

</template>