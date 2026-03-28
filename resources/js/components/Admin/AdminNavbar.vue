<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3"
import { Leaf } from "lucide-vue-next"
import { computed, ref, watch } from "vue";
import { route } from "ziggy-js";
import AlertDialog from "@/components/AlertDialog.vue"
const navItems = [
    { label: 'Dashboard', routeName: 'admin.dashboard', href: route('admin.dashboard') },
    { label: 'Users', routeName: 'admin.users.index', href: route('admin.users.index') },
    { label: 'Diseases', routeName: 'admin.diseases.index', href: route('admin.diseases.index') },
    { label: 'Diagnoses', routeName: 'admin.diagnoses.index', href: route('admin.diagnoses.index') },
]
const isActive = (routeName?: string) => (routeName ? route().current(routeName) : false)
const logoutForm = useForm()
const handleLogout = () => {
    logoutForm.post(route('logout'))
}

const page = usePage()
const userAvatarUrl = computed(() => {
    const avatar = page.props.auth.user.avatar

    if (typeof avatar !== 'string' || avatar.trim() === '') {
        return ''
    }

    return route('admin.profile.avatar.show')
})
const userInitial = computed(() => {
    const name = String(page.props.auth.user.name ?? '').trim()

    return name ? name.charAt(0).toUpperCase() : 'A'
})

const avatarLoadError = ref(false)

watch(userAvatarUrl, () => {
    avatarLoadError.value = false
})
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-6 px-2 py-4">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-full bg-emerald-600 text-white">
                    <Leaf class="h-5 w-5" aria-hidden="true" />
                </div>
                <p class="text-base font-semibold text-slate-900">AgriAssist AI</p>
            </div>

            <nav 
            class="flex items-center gap-2 rounded-full bg-emerald-50 px-2 py-1 text-sm font-medium text-slate-600">
                
             <component v-for="item in navItems" :is="item.href ? Link : 'button'" :key="item.label"
                    :href="item.href" type="button" class="rounded-full px-4 py-2 transition"
                    :class="isActive(item.routeName) ? 'bg-white text-emerald-700 shadow-sm' : 'hover:text-emerald-700'">
                    {{ item.label }}
                </component>
            </nav>

            <div class="flex items-center gap-3">
           

                <Link
                    :href="route('admin.profile.show')"
                    class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-1.5 shadow-sm"
                >
                    <div class="grid h-8 w-8 place-items-center overflow-hidden rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700">
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

                <AlertDialog
                    title="Confirm logout"
                    description="You are about to end your session. Do you want to continue?"
                    trigger-label="Logout"
                    confirm-label="Logout"
                    cancel-label="Cancel"
                    trigger-class="rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100"
                    :trigger-disabled="logoutForm.processing"
                    :confirm-disabled="logoutForm.processing"
                    @confirm="handleLogout"
                />

            </div>
        </div>
    </header>
</template>
