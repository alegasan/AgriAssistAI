<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import { AtSign, CalendarDays, Camera, Image as ImageIcon, Mail, ShieldCheck, User } from "lucide-vue-next";
import { computed, onBeforeUnmount, ref } from "vue";
import { route } from "ziggy-js";
import ClientDashboard from "@/layouts/Client/ClientDashboard.vue";

const props = defineProps<{ user?: Record<string, any> }>();
const page = usePage();
const fileInputRef = ref<HTMLInputElement | null>(null);
const localPreviewUrl = ref<string | null>(null);

const uploadForm = useForm<{ avatar: File | null }>({
        avatar: null,
});

const user = computed(() => props.user ?? page.props.auth.user);
const displayName = computed(() => user.value?.name ?? "—");
const displayEmail = computed(() => user.value?.email ?? "—");
const username = computed(() => user.value?.username ?? "—");
const joinDate = computed(() => user.value?.created_at_formatted ?? "—");
const avatarUrl = computed(() => user.value?.avatar_url ?? user.value?.avatar ?? "");
const displayedAvatarUrl = computed(() => localPreviewUrl.value ?? avatarUrl.value);
const uploadProgress = computed(() => Math.round(uploadForm.progress?.percentage ?? 0));

const clearLocalPreview = (): void => {
        if (localPreviewUrl.value) {
                URL.revokeObjectURL(localPreviewUrl.value);
                localPreviewUrl.value = null;
        }
};

const onFileChange = (event: Event): void => {
        const target = event.target as HTMLInputElement;
        const file = target.files?.[0] ?? null;

        uploadForm.clearErrors("avatar");

        if (!file) {
                uploadForm.avatar = null;
                clearLocalPreview();

                return;
        }

        if (!file.type.startsWith("image/")) {
                uploadForm.avatar = null;
                clearLocalPreview();
                uploadForm.setError("avatar", "Please choose a valid image file.");

                return;
        }

        uploadForm.avatar = file;
        clearLocalPreview();
        localPreviewUrl.value = URL.createObjectURL(file);
};

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

        uploadForm.post(route("client.profile.avatar.upload", user.value.id), {
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

onBeforeUnmount(() => {
        clearLocalPreview();
});
</script>

<template>
        <ClientDashboard :title="'Profile'" :description="'Manage your profile information and settings.'">
                <section class="mx-auto flex w-full max-w-4xl flex-col gap-5">
                        <div
                                class="overflow-hidden rounded-3xl border border-emerald-100/70 bg-gradient-to-br from-white via-emerald-50/50 to-slate-50 p-6 shadow-xl">
                                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="flex items-center gap-4 sm:gap-5">
                                                <div
                                                        class="relative h-20 w-20 shrink-0 overflow-hidden rounded-full ring-4 ring-white/90 shadow-lg bg-emerald-600/90 text-white">
                                                        <img v-if="displayedAvatarUrl" :src="displayedAvatarUrl" alt="Profile photo"
                                                                class="h-full w-full object-cover" />
                                                        <div v-else class="flex h-full w-full items-center justify-center">
                                                                <User class="h-8 w-8" />
                                                        </div>
                                                        <div v-if="!displayedAvatarUrl"
                                                                class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-emerald-500 text-white shadow">
                                                                <Camera class="h-3.5 w-3.5" />
                                                        </div>
                                                </div>

                                                <div class="flex flex-col gap-2">
                                                        <span
                                                                class="inline-flex w-fit items-center gap-1 rounded-full border border-emerald-200 bg-white/80 px-2.5 py-1 text-xs font-semibold text-emerald-700">
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
                                                <input ref="fileInputRef" type="file" accept="image/png,image/jpeg,image/webp"
                                                        class="hidden" @change="onFileChange" />
                                                <div class="grid gap-2 sm:grid-cols-2">
                                                        <button type="button" @click="triggerFilePicker" :disabled="uploadForm.processing"
                                                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60">
                                                                <ImageIcon class="h-4 w-4" />
                                                                Choose photo
                                                        </button>
                                                        <button type="button" @click="uploadAvatar"
                                                                :disabled="uploadForm.processing || !uploadForm.avatar"
                                                                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                                                                {{ uploadForm.processing ? "Uploading..." : "Save photo" }}
                                                        </button>
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
                                        <p class="text-sm font-semibold text-slate-900">Photo tips</p>
                                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                                                <p>
                                                        Use a clear, front-facing photo for better recognition across the platform.
                                                </p>
                                                <p>
                                                        Square images usually look best in the avatar frame.
                                                </p>
                                                <p class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-700">
                                                        <ImageIcon class="h-4 w-4" />
                                                        Changes appear right after a successful upload.
                                                </p>
                                        </div>
                                </div>
                        </div>
                </section>
        </ClientDashboard>
</template>