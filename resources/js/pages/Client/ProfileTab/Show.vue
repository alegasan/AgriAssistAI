<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { Camera, User } from "lucide-vue-next";
import ClientDashboard from "@/layouts/Client/ClientDashboard.vue";

const page = usePage()
const user = page.props.auth.user


</script>

<template>
        <ClientDashboard :title="'Profile'" :description="'Manage your profile information and settings.'">
                <section class="mx-auto w-full max-w-2xl">
                        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xl">
                                <div class="flex items-center gap-4">
                                        <div
                                                class="relative h-14 w-14 shrink-0 rounded-full bg-emerald-600/90 text-white">
                                                <div class="flex h-full w-full items-center justify-center">
                                                        <User class="h-7 w-7" />
                                                </div>
                                                <div
                                                        class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-emerald-500 text-white shadow">
                                                        <Camera class="h-3.5 w-3.5" />
                                                </div>
                                        </div>

                                        <div>
                                                <p class="text-lg font-semibold text-slate-900">{{ user.name }}</p>
                                                <p class="text-sm text-slate-600">{{ user.email }}</p>
                                        </div>
                                </div>
                        </div>
                </section>
        </ClientDashboard>
</template>