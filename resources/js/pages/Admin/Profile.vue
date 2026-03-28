<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import { AtSign, CalendarDays, Camera, Image as ImageIcon, KeyRound, Mail, ShieldCheck, User } from "lucide-vue-next";
import { CircleCheckBig } from "lucide-vue-next"; 
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { route } from "ziggy-js";
import AlertDialog from "@/components/AlertDialog.vue";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";
import AdminDashboard from "@/layouts/Admin/AdminDashboard.vue";
import Transition from "@/components/Transition.vue";

const props = defineProps<{ user?: Record<string, any> }>();
const page = usePage();
const fileInputRef = ref<HTMLInputElement | null>(null);
const localPreviewUrl = ref<string | null>(null);

const uploadForm = useForm<{ avatar: File | null }>({
    avatar: null,
});


const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const user = computed(() => props.user ?? page.props.auth.user);
const displayName = computed(() => user.value?.name ?? "—");
const displayEmail = computed(() => user.value?.email ?? "—");
const username = computed(() => user.value?.username ?? "—");
const joinDate = computed(() => user.value?.created_at_formatted ?? "—");
const avatarUrl = computed(() => user.value?.avatar_url ?? user.value?.avatar ?? "");
const displayedAvatarUrl = computed(() => localPreviewUrl.value ?? avatarUrl.value);
const uploadProgress = computed(() => Math.round(uploadForm.progress?.percentage ?? 0));
const requiresCurrentPassword = computed(() => !user.value?.provider);

const clearLocalPreview = (): void => {
    if (localPreviewUrl.value) {
        URL.revokeObjectURL(localPreviewUrl.value);
        localPreviewUrl.value = null;
    }
};

const showSuccessToast = ref(false);
let hideTimer: number | null = null;
const flash = computed(() => (page.props.flash ?? {}) as { success?: string });

const clearHideTimer = (): void => {
    if (hideTimer !== null) {
        window.clearTimeout(hideTimer);
        hideTimer = null;
    }
};

watch(
    () => flash.value.success,
    (message) => {
        clearHideTimer();
        if (!message) {
            showSuccessToast.value = false;
            return;
        }
        showSuccessToast.value = true;
        hideTimer = window.setTimeout(() => {
            showSuccessToast.value = false;
        }, 3000);
    },
    { immediate: true },
);
const onFileChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    uploadForm.clearErrors("avatar");

    if (!file) {
        uploadForm.avatar = null;
        clearLocalPreview();

        return;
    }

const MAX_FILE_SIZE_BYTES = 2 * 1024 * 1024; // 2MB
const ALLOWED_MIME_TYPES = ["image/png", "image/jpeg", "image/webp"];

const onFileChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    uploadForm.clearErrors("avatar");

    if (!file) {
        uploadForm.avatar = null;
        clearLocalPreview();

        return;
    }

    if (!ALLOWED_MIME_TYPES.includes(file.type)) {
        uploadForm.avatar = null;
        clearLocalPreview();
        uploadForm.setError("avatar", "Please choose a PNG, JPG, or WEBP image.");

        return;
    }

    if (file.size > MAX_FILE_SIZE_BYTES) {
        uploadForm.avatar = null;
        clearLocalPreview();
        uploadForm.setError("avatar", "File size must be under 2MB.");

        return;
    }

    uploadForm.avatar = file;
    clearLocalPreview();
    localPreviewUrl.value = URL.createObjectURL(file);
};};

const triggerFilePicker = (): void => {
    if (uploadForm.processing) {
        return;
    }

    fileInputRef.value?.click();
};

const uploadAvatar = (): void => {
    if (!user.value?.id || uploadForm.processing) {
        return;
    }

    if (!uploadForm.avatar) {
        uploadForm.setError("avatar", "Please select an image before uploading.");

        return;
    }

    uploadForm.post(route("admin.profile.avatar.upload"), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.reset("avatar");
            if (fileInputRef.value) {
                fileInputRef.value.value = "";
            }
            clearLocalPreview();
        },
    });
};

const updatePassword = (): void => {
    if (!user.value?.id || passwordForm.processing) {
        return;
    }

    passwordForm.put(route("admin.profile.password.update"), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset("current_password", "password", "password_confirmation");
        },
    });
};

onBeforeUnmount(() => {
    clearLocalPreview();
    clearHideTimer();
});
</script>

<template>
    <AdminDashboard :title="'Profile'" :description="'Manage your profile information and settings.'">
        <section class="mx-auto mt-4 w-full max-w-6xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="mx-auto flex w-full max-w-4xl flex-col gap-5">
                <div class="overflow-hidden rounded-3xl border border-emerald-100/70 bg-gradient-to-br from-white via-emerald-50/50 to-slate-50 p-6 shadow-xl">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-4 sm:gap-5">
                            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-full ring-4 ring-white/90 shadow-lg bg-emerald-600/90 text-white">
                                <img v-if="displayedAvatarUrl" :src="displayedAvatarUrl" alt="Profile photo" class="h-full w-full object-cover" />
                                <div v-else class="flex h-full w-full items-center justify-center">
                                    <User class="h-8 w-8" />
                                </div>
                                <div
                                    v-if="!displayedAvatarUrl"
                                    class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-emerald-500 text-white shadow"
                                >
                                    <Camera class="h-3.5 w-3.5" />
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <span class="inline-flex w-fit items-center gap-1 rounded-full border border-emerald-200 bg-white/80 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <ShieldCheck class="h-3.5 w-3.5" />
                                    Profile
                                </span>
                                <div class="space-y-1">
                                    <p class="text-2xl font-semibold tracking-tight text-slate-900">{{ displayName }}</p>
                                    <p class="text-sm text-slate-600">{{ displayEmail }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex w-full max-w-sm flex-col gap-2.5 rounded-2xl border border-slate-200/70 bg-white/80 p-3.5 shadow-sm">
                            <input
                                ref="fileInputRef"
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                class="hidden"
                                @change="onFileChange"
                            />
                            <div class="grid gap-2 sm:grid-cols-2">
                                <button
                                    type="button"
                                    @click="triggerFilePicker"
                                    :disabled="uploadForm.processing"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    <ImageIcon class="h-4 w-4" />
                                    Choose photo
                                </button>
                                <AlertDialog
                                    title="Confirm photo change"
                                    description="Your profile photo will be updated. Do you want to continue?"
                                    trigger-label="Save photo"
                                    confirm-label="Save photo"
                                    cancel-label="Cancel"
                                    trigger-class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                                    confirm-class="rounded-xl bg-emerald-600 text-white hover:bg-emerald-700"
                                    :trigger-disabled="uploadForm.processing || !uploadForm.avatar"
                                    :confirm-disabled="uploadForm.processing"
                                    @confirm="uploadAvatar"
                                />
                            </div>
                            <p v-if="uploadForm.errors.avatar" class="text-xs text-rose-600">{{ uploadForm.errors.avatar }}</p>
                            <p v-else class="text-xs text-slate-500">PNG, JPG, or WEBP up to 2MB</p>
                            <p v-if="uploadForm.processing && uploadProgress > 0" class="text-xs text-emerald-700">
                                Uploading {{ uploadProgress }}%
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-lg">
                        <p class="text-sm font-semibold text-slate-900">Account details</p>
                        <div class="mt-4 flex flex-col divide-y divide-slate-100">
                            <div class="flex items-center justify-between gap-3 py-3">
                                <span class="inline-flex items-center gap-2 text-sm text-slate-500">
                                    <AtSign class="h-4 w-4 text-slate-400" />
                                    Username
                                </span>
                                <span class="text-sm font-semibold text-slate-900">{{ username }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-3">
                                <span class="inline-flex items-center gap-2 text-sm text-slate-500">
                                    <Mail class="h-4 w-4 text-slate-400" />
                                    Email
                                </span>
                                <span class="truncate text-right text-sm font-medium text-slate-900">{{ displayEmail }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-3">
                                <span class="inline-flex items-center gap-2 text-sm text-slate-500">
                                    <CalendarDays class="h-4 w-4 text-slate-400" />
                                    Joined
                                </span>
                                <span class="text-sm font-medium text-slate-900">{{ joinDate }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-lg">
                        <p class="inline-flex items-center gap-2 text-sm font-semibold text-slate-900">
                            <KeyRound class="h-4 w-4 text-slate-500" />
                            Change password
                        </p>
                        <form class="mt-4 space-y-3" @submit.prevent>
                            <div v-if="requiresCurrentPassword" class="space-y-1.5">
                                <label for="current_password" class="text-xs font-medium text-slate-600">Current password</label>
                                <div class="relative">
                                    <input
                                        id="current_password"
                                        v-model="passwordForm.current_password"
                                        :type="showCurrentPassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        :disabled="passwordForm.processing"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 pr-16 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:bg-slate-50"
                                    />
                                    <button
                                        type="button"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 text-xs font-semibold text-emerald-700 hover:text-emerald-800"
                                        @click="showCurrentPassword = !showCurrentPassword"
                                    >
                                        {{ showCurrentPassword ? "Hide" : "Show" }}
                                    </button>
                                </div>
                                <p v-if="passwordForm.errors.current_password" class="text-xs text-rose-600">
                                    {{ passwordForm.errors.current_password }}
                                </p>
                            </div>

                            <p v-else class="rounded-lg border border-emerald-100 bg-emerald-50/70 px-3 py-2 text-xs text-emerald-800">
                                Your account was created with Google. You can set a password here to also sign in with email.
                            </p>

                            <div class="space-y-1.5">
                                <label for="password" class="text-xs font-medium text-slate-600">New password</label>
                                <div class="relative">
                                    <input
                                        id="password"
                                        v-model="passwordForm.password"
                                        :type="showNewPassword ? 'text' : 'password'"
                                        autocomplete="new-password"
                                        :disabled="passwordForm.processing"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 pr-16 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:bg-slate-50"
                                    />
                                    <button
                                        type="button"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 text-xs font-semibold text-emerald-700 hover:text-emerald-800"
                                        @click="showNewPassword = !showNewPassword"
                                    >
                                        {{ showNewPassword ? "Hide" : "Show" }}
                                    </button>
                                </div>
                                <p v-if="passwordForm.errors.password" class="text-xs text-rose-600">
                                    {{ passwordForm.errors.password }}
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <label for="password_confirmation" class="text-xs font-medium text-slate-600">Confirm new password</label>
                                <div class="relative">
                                    <input
                                        id="password_confirmation"
                                        v-model="passwordForm.password_confirmation"
                                        :type="showConfirmPassword ? 'text' : 'password'"
                                        autocomplete="new-password"
                                        :disabled="passwordForm.processing"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 pr-16 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:bg-slate-50"
                                    />
                                    <button
                                        type="button"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 text-xs font-semibold text-emerald-700 hover:text-emerald-800"
                                        @click="showConfirmPassword = !showConfirmPassword"
                                    >
                                        {{ showConfirmPassword ? "Hide" : "Show" }}
                                    </button>
                                </div>
                                <p v-if="passwordForm.errors.password_confirmation" class="text-xs text-rose-600">
                                    {{ passwordForm.errors.password_confirmation }}
                                </p>
                            </div>

                            <AlertDialog
                                title="Confirm password update"
                                description="Your password will be changed and used on your next sign in. Do you want to continue?"
                                trigger-label="Update password"
                                confirm-label="Update password"
                                cancel-label="Cancel"
                                trigger-class="w-full rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                                confirm-class="rounded-xl bg-emerald-600 text-white hover:bg-emerald-700"
                                :trigger-disabled="passwordForm.processing"
                                :confirm-disabled="passwordForm.processing"
                                @confirm="updatePassword"
                            />
                        </form>
                    </div>
                </div>
            </div>
        </section>

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
    </AdminDashboard>
</template>
