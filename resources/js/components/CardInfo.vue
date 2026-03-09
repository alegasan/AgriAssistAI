<script setup lang="ts">
const props = defineProps<{
    title: string
    value: string
    change: string
    description: string
    icon?: any
}>()
</script>


<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="grid h-10 w-10 place-items-center rounded-full bg-rose-100 text-rose-600">
                <component :is="props.icon" class="h-5 w-5" aria-hidden="true" />
            </div>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ props.change }}</span>
        </div>
        <p class="mt-4 text-2xl font-semibold text-slate-900">{{ props.value }}</p>
        <p class="text-sm text-slate-500">{{ props.description }}</p>
    </div>
</template>