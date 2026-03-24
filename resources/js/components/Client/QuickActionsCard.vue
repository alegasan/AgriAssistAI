<script setup lang="ts">

const props = defineProps<{
    title: string
    description: string
    icon?: any
    class?: string
    href?: string
    layout?: "stacked" | "inline"
}>()

import { Link } from "@inertiajs/vue3";
</script>
    
<template>
    <Link v-if="props.href" :href="props.href">
        <div
            v-if="props.layout === 'inline'"
            class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition hover:bg-slate-50"
        >
            <div :class="(props.class ?? '') + ' grid h-12 w-12 place-items-center rounded-full'">
                <component :is="props.icon" class="h-6 w-6" aria-hidden="true" />
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ props.title }}</p>
                <p class="text-xs text-slate-500">{{ props.description }}</p>
            </div>
        </div>
        <div
            v-else
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:bg-slate-50"
        >
            <div :class="(props.class ?? '') + ' grid h-12 w-12 place-items-center rounded-full'">
                <component :is="props.icon" class="h-6 w-6" aria-hidden="true" />
            </div>
            <p class="mt-4 text-sm font-semibold text-slate-900">{{ props.title }}</p>
            <p class="text-xs text-slate-500">{{ props.description }}</p>
        </div>
    </Link>
    <div
        v-else-if="props.layout === 'inline'"
        class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm"
    >
        <div :class="(props.class ?? '') + ' grid h-12 w-12 place-items-center rounded-full'">
            <component :is="props.icon" class="h-6 w-6" aria-hidden="true" />
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-900">{{ props.title }}</p>
            <p class="text-xs text-slate-500">{{ props.description }}</p>
        </div>
    </div>
    <div v-else class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div :class="(props.class ?? '') + ' grid h-12 w-12 place-items-center rounded-full'">
            <component :is="props.icon" class="h-6 w-6" aria-hidden="true" />
        </div>
        <p class="mt-4 text-sm font-semibold text-slate-900">{{ props.title }}</p>
        <p class="text-xs text-slate-500">{{ props.description }}</p>
    </div>
</template>