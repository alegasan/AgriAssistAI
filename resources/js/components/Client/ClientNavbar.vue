<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3"
import { Link } from "@inertiajs/vue3"
import { Bell, CircleCheckBig, Leaf } from "lucide-vue-next"
import { computed, onBeforeUnmount, ref, watch } from "vue"
import { route } from "ziggy-js"
import AlertDialog from "@/components/AlertDialog.vue"
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert"


const navItems = [
    { label: "Home", routeName: "client.dashboard", href: route("client.dashboard") },
    { label: "Diagnose", routeName: "client.diagnose", href: route("client.diagnose") },
    { label: "Reports", routeName: "client.reports", href: route("client.reports") },
    { label: "Knowledge Hub", routeName: "client.knowledgehub", href: route("client.knowledgehub") },
]

const isActive = (routeName?: string) => (routeName ? route().current(routeName) : false)



const logoutForm = useForm()
const handleLogout = () => {
    logoutForm.post(route("logout"))
}
const page = usePage()

const flash = computed(() => (page.props.flash ?? {}) as { success?: string })
const showSuccessToast = ref(false)

let hideTimer: number | null = null

const clearHideTimer = (): void => {
    if (hideTimer !== null) {
        window.clearTimeout(hideTimer)
        hideTimer = null
    }
}

watch(
    () => flash.value.success,
    (message) => {
        clearHideTimer()

        if (!message) {
            showSuccessToast.value = false

            return
        }

        showSuccessToast.value = true

        hideTimer = window.setTimeout(() => {
            showSuccessToast.value = false
        }, 3000)
    },
    { immediate: true },
)

onBeforeUnmount(() => {
    clearHideTimer()
})
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-6 px-3 py-4">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-full bg-emerald-600 text-white">
                    <Leaf class="h-5 w-5" aria-hidden="true" />
                </div>
                <p class="text- base font-semibold text-slate-900">AgriAssist AI</p>
            </div>

            <nav
                class="hidden items-center gap-2 rounded-full bg-emerald-50 px-2 py-1 text-sm font-medium text-slate-600 md:flex">
                <component v-for="item in navItems" :is="item.href ? Link : 'button'" :key="item.label"
                    :href="item.href" type="button" class="rounded-full px-4 py-2 transition"
                    :class="isActive(item.routeName) ? 'bg-white text-emerald-700' : 'text-slate-600'">
                    {{ item.label }}
                </component>
            </nav>

            <div class="flex items-center gap-3">
                <button type="button"
                    class="relative grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600"
                    aria-label="Notifications">
                    <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-rose-500"></span>
                    <Bell class="h-5 w-5" aria-hidden="true" />
                </button>

                <Link :href="route('client.profile', page.props.auth.user.id)"
                    class="hidden items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-1.5 sm:flex cursor-pointer">
                    <div
                        class="grid h-8 w-8 place-items-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700">
                        {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <p class="text-sm font-semibold text-slate-900">{{ page.props.auth.user.name }}</p>
                </Link>


                <AlertDialog title="Confirm logout"
                    description="You are about to end your session. Do you want to continue?" trigger-label="Logout"
                    confirm-label="Logout" cancel-label="Cancel"
                    trigger-class="rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700"
                    :trigger-disabled="logoutForm.processing" :confirm-disabled="logoutForm.processing"
                    @confirm="handleLogout" />
            </div>
        </div>

        <Transition
            enter-active-class="transition-all duration-500 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition-all duration-500 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div v-if="showSuccessToast && flash.success" class="fixed right-3 top-3 z-[70] w-[min(92vw,26rem)] sm:right-6">
                <Alert class="border-emerald-200 bg-emerald-50 text-emerald-900 shadow-lg">
                    <CircleCheckBig class="text-emerald-700" />
                    <AlertTitle class="text-emerald-900">Success</AlertTitle>
                    <AlertDescription class="text-emerald-800">
                        {{ flash.success }}
                    </AlertDescription>
                </Alert>
            </div>
        </Transition>
    </header>
</template>
