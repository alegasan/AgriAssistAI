<script setup lang="ts">
import { Leaf } from "lucide-vue-next"
import { ref } from "vue"
import { 
    AlertDialog , 
    AlertDialogAction , 
    AlertDialogCancel, 
    AlertDialogContent, 
    AlertDialogTrigger , 
    AlertDialogTitle,
    AlertDialogDescription
} 
from "@/components/ui/alert-dialog"
import { Button } from "@/components/ui/button"

const open = ref(false)

const handleOpen = () => {
    open.value = true
}
const handleClose = () => {
    open.value = false
}

const props = defineProps<{
    title: string
    description: string
    triggerLabel?: string
    confirmLabel?: string
    cancelLabel?: string
    triggerClass?: string
    confirmClass?: string
    triggerDisabled?: boolean
    confirmDisabled?: boolean
}>()

const emit = defineEmits<{
    (event: "confirm"): void
}>()

const handleConfirm = () => {
    emit("confirm")
    handleClose()
}
</script>


<template>
    <AlertDialog :open="open" @update:open="open = $event">
        <AlertDialogTrigger as-child>
            <Button
                variant="outline"
                :class="props.triggerClass"
                :disabled="props.triggerDisabled"
                @click="handleOpen"
            >
                {{ props.triggerLabel ?? "Open Alert Dialog" }}
            </Button>
        </AlertDialogTrigger>
        <AlertDialogContent class="sm:max-w-lg rounded-2xl border border-emerald-100/70 bg-white p-6 shadow-xl">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 grid h-9 w-9 place-items-center rounded-full bg-emerald-100 text-emerald-700">
                    <Leaf class="h-4 w-4" aria-hidden="true" />
                </div>
                <div>
                    <AlertDialogTitle class="text-lg font-semibold tracking-tight text-emerald-900">
                        {{ props.title }}
                    </AlertDialogTitle>
                    <AlertDialogDescription class="mt-2 text-sm text-emerald-900/70">
                        {{ props.description }}
                    </AlertDialogDescription>
                </div>
            </div>
            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <AlertDialogCancel as-child>
                    <Button
                        variant="outline"
                        class="rounded-xl border-emerald-200 text-emerald-700 hover:bg-emerald-50"
                        @click="handleClose"
                    >
                        {{ props.cancelLabel ?? "Cancel" }}
                    </Button>
                </AlertDialogCancel>
                <AlertDialogAction
                    variant="destructive"
                    :class="props.confirmClass ?? 'rounded-xl bg-emerald-600 text-white hover:bg-emerald-700'"
                    :disabled="props.confirmDisabled"
                    @click="handleConfirm"
                >
                    {{ props.confirmLabel ?? "Delete" }}
                </AlertDialogAction>
            </div>
        </AlertDialogContent>
    </AlertDialog>
</template>