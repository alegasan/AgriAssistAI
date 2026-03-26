<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3"
import { Link } from "@inertiajs/vue3"
import { CircleCheckBig, Leaf, Menu } from "lucide-vue-next"
import { computed, onBeforeUnmount, ref, watch } from "vue"
import { route } from "ziggy-js"
import AlertDialog from "@/components/AlertDialog.vue"
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert"
import { Sheet, SheetContent, SheetHeader, SheetTitle } from "@/components/ui/sheet"


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
const userAvatarUrl = computed(() => {
    const avatar = page.props.auth.user.avatar

    if (typeof avatar !== 'string' || avatar.trim() === '') {
        return ''
    }

    return route('client.profile.avatar.show', page.props.auth.user.id)
})
const userInitial = computed(() => {
    const name = String(page.props.auth.user.name ?? '').trim()

    return name ? name.charAt(0).toUpperCase() : 'U'
})
const avatarLoadError = ref(false)

watch(userAvatarUrl, () => {
    avatarLoadError.value = false
})

const flash = computed(() => (page.props.flash ?? {}) as { success?: string })
const showSuccessToast = ref(false)

const isMobileMenuOpen = ref(false)

const setMobileMenuOpen = (value: boolean): void => {
    isMobileMenuOpen.value = value
}

const closeMobileMenu = (): void => {
    setMobileMenuOpen(false)
}

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
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
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

                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 md:hidden"
                    aria-label="Open menu"
                    @click="setMobileMenuOpen(true)"
                >
                    <Menu class="h-5 w-5" aria-hidden="true" />
                </button>

                <Sheet :open="isMobileMenuOpen" @update:open="setMobileMenuOpen">
                    <SheetContent side="left" class="border-r border-slate-200 bg-white p-0">
                        <SheetHeader class="border-b border-slate-200">
                            <SheetTitle class="text-lg font-extrabold tracking-tight text-slate-900">Menu</SheetTitle>
                        </SheetHeader>

                        <nav class="flex flex-col gap-1 p-4">
                            <Link
                                v-for="item in navItems"
                                :key="item.label"
                                :href="item.href"
                                class="flex w-full items-center rounded-xl px-4 py-3 text-sm font-semibold transition"
                                :class="isActive(item.routeName)
                                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                                    : 'text-slate-700 hover:bg-slate-50'"
                                @click="closeMobileMenu"
                            >
                                {{ item.label }}
                            </Link>

                            <Link
                                :href="route('client.profile', page.props.auth.user.id)"
                                class="flex w-full items-center rounded-xl px-4 py-3 text-sm font-semibold transition"
                                :class="isActive('client.profile')
                                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                                    : 'text-slate-700 hover:bg-slate-50'"
                                @click="closeMobileMenu"
                            >
                                Profile
                            </Link>

                            <button
                                type="button"
                                class="flex w-full items-center rounded-xl px-4 py-3 text-left text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                                @click="handleLogout(); closeMobileMenu()"
                            >
                                Logout
                            </button>
                        </nav>
                    </SheetContent>
                </Sheet>

                <Link :href="route('client.profile', page.props.auth.user.id)"
                    class="hidden items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-1.5 sm:flex cursor-pointer">
                    <div
                        class="grid h-8 w-8 place-items-center overflow-hidden rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700">
                        <img
                            v-if="userAvatarUrl && !avatarLoadError"
                            :src="userAvatarUrl"
                            alt="User avatar"
                            class="h-full w-full object-cover"
                            @error="avatarLoadError = true"
                            @load="avatarLoadError = false"
                        />
                        <span v-else>{{ userInitial }}</span>
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
