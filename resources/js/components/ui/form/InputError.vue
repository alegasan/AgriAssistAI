<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
    message?: string
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <p
        v-if="message"
        role="alert"
        :class="cn('text-sm font-medium text-red-500', props.class)"
    >
        {{ message }}
    </p>
</template>
