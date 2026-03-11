<script setup lang="ts">

const props = defineProps<{
    title: string
    description: string
    icon?: any
    class?: string
}>()
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div :class="props.class + ' grid h-12 w-12 place-items-center rounded-full'">
             <component :is="props.icon" class="h-6 w-6" aria-hidden="true" />
        </div>
        <p class="mt-4 text-sm font-semibold text-slate-900">{{ props.title }}</p>
        <p class="text-xs text-slate-500">{{ props.description }}</p>
    </div>
</template>