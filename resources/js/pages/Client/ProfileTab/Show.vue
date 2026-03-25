<script setup lang="ts">
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { Camera, User } from "lucide-vue-next";
import ClientDashboard from "@/layouts/Client/ClientDashboard.vue";

const props = defineProps<{ user?: Record<string, any> }>();
const page = usePage();

const user = computed(() => props.user ?? page.props.auth.user);
const username = computed(() => user.value?.username ?? "—");
const joinDate = computed(() => user.value?.created_at_formatted ?? "—");
const avatarUrl = computed(() => user.value?.avatar ?? "");
</script>

<template>
        <ClientDashboard :title="'Profile'" :description="'Manage your profile information and settings.'">
                <section class="mx-auto flex w-full max-w-3xl flex-col gap-6">
                        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xl">
                                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-center gap-4">
                                                <div
                                                        class="relative h-16 w-16 shrink-0 overflow-hidden rounded-full bg-emerald-600/90 text-white">
                                                        <img v-if="avatarUrl" :src="avatarUrl" alt="Profile photo"
                                                                class="h-full w-full object-cover" />
                                                        <div v-else class="flex h-full w-full items-center justify-center">
                                                                <User class="h-7 w-7" />
                                                        </div>
                                                        <div
                                                                class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-emerald-500 text-white shadow">
                                                                <Camera class="h-3.5 w-3.5" />
                                                        </div>
                                                </div>

                                                <div class="flex flex-col gap-1">
                                                        <p class="text-lg font-semibold text-slate-900">{{ user.name }}</p>
                                                        <p class="text-sm text-slate-600">{{ user.email }}</p>
                                                        <p class="text-xs text-slate-500">Username: {{ username }}</p>
                                                </div>
                                        </div>

                                        <div class="flex flex-col gap-2 sm:items-end">
                                                <button type="button" disabled
                                                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-500 shadow-sm">
                                                        Upload new photo
                                                </button>
                                                <p class="text-xs text-slate-500">Uploads coming soon.</p>
                                        </div>
                                </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                                <div class="flex flex-col gap-3 rounded-2xl border border-slate-100 bg-white p-5 shadow-xl">
                                        <p class="text-sm font-semibold text-slate-900">Account details</p>
                                        <div class="flex flex-col gap-3">
                                                <div class="flex items-center justify-between">
                                                        <span class="text-xs text-slate-500">Username</span>
                                                        <span class="text-sm font-medium text-slate-900">{{ username }}</span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                        <span class="text-xs text-slate-500">Joined</span>
                                                        <span class="text-sm font-medium text-slate-900">{{ joinDate }}</span>
                                                </div>
                                        </div>
                                </div>

                                <div class="flex flex-col gap-3 rounded-2xl border border-slate-100 bg-white p-5 shadow-xl">
                                        <p class="text-sm font-semibold text-slate-900">Profile photo</p>
                                        <div class="flex flex-col gap-3">
                                                <p class="text-sm text-slate-600">
                                                        Keep your profile photo fresh to help your teammates recognize you.
                                                </p>
                                                <p class="text-xs text-emerald-700">
                                                        Uploads are being prepared.
                                                </p>
                                        </div>
                                </div>
                        </div>
                </section>
        </ClientDashboard>
</template>