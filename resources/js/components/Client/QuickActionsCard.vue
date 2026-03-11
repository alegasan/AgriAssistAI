<script setup lang="ts">

const props = defineProps<{
    title: string
    description: string
    icon?: any
    class?: string
    href?: string
}>()

import { Link } from "@inertiajs/vue3";
</script>
    
<template>
    <Link v-if="props.href" :href="props.href">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:bg-slate-50 transition cursor-pointer">
            <div :class="props.class + ' grid h-12 w-12 place-items-center rounded-full'">
                 <component :is="props.icon" class="h-6 w-6" aria-hidden="true" />
            </div>
        <p class="mt-4 text-sm font-semibold text-slate-900">{{ props.title }}</p>
        <p class="text-xs text-slate-500">{{ props.description }}</p>
        </div>
    </Link>
    <div v-else class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div :class="props.class + ' grid h-12 w-12 place-items-center rounded-full'">
             <component :is="props.icon" class="h-6 w-6" aria-hidden="true" />
        </div>
        <p class="mt-4 text-sm font-semibold text-slate-900">{{ props.title }}</p>
        <p class="text-xs text-slate-500">{{ props.description }}</p>
    </div>
</template>