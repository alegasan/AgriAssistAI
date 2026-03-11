<script setup lang="ts">
import {
    BookOpen,
    Camera,
    CheckCircle2,
    FileText,
    MessageCircle,
    ShieldCheck,
    Sun,
} from "lucide-vue-next"
import ClientNavbar from "@/components/Client/ClientNavbar.vue"
import { Head } from "@inertiajs/vue3";
import QuickActionsCard from "@/components/Client/QuickActionsCard.vue";
</script>

<template>

    <Head title="Dashboard" />
    <div class="min-h-screen bg-slate-50">
        <ClientNavbar />

        <main class="mx-auto w-full max-w-6xl px-4 pb-16 pt-8">
            <section
                class="relative overflow-hidden rounded-3xl border border-emerald-200/60 bg-emerald-700/10 px-6 py-8 shadow-sm">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(16,185,129,0.55),rgba(15,23,42,0.25))]">
                </div>
                <div class="absolute inset-0 bg-[linear-gradient(130deg,rgba(255,255,255,0.32),rgba(255,255,255,0))]">
                </div>
                <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div class="space-y-2 text-white">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-100">Welcome back</p>
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Ramesh Kumar</h1>
                        <p class="text-3xl font-extrabold tracking-tight md:text-4xl">28&deg;C - Sunny</p>
                        <p class="text-sm text-emerald-50/90">Great for field work!</p>
                    </div>

                    <div class="flex items-center gap-3 rounded-2xl bg-white/90 px-5 py-4 text-slate-900 shadow-sm">
                        <div class="grid h-12 w-12 place-items-center rounded-full bg-amber-100 text-amber-500">
                            <Sun class="h-6 w-6" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Today&apos;s condition</p>
                            <p class="text-xs text-slate-500">Stable winds, low humidity</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Quick Actions</h2>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <QuickActionsCard title="New Diagnosis" description="Submit a new plant diagnosis" :icon="Camera"
                        :class="' bg-emerald-100 text-emerald-700'" />
                    <QuickActionsCard title="My Diagnoses" description="View your past diagnoses and results"
                        :icon="FileText" :class="' bg-amber-100 text-slate-700'" />
                    <QuickActionsCard title="Disease Library" description="Browse the disease knowledge base"
                        :icon="BookOpen" :class="' bg-blue-100 text-slate-700'" />
                    <QuickActionsCard title="Support" description="Contact our support team for help"
                        :icon="MessageCircle" :class="' bg-orange-100 text-slate-700'" />
                </div>
            </section>

            <section class="mt-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Recent Activity</h2>
                    <button type="button" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                        View all
                    </button>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div
                        class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="grid h-12 w-12 place-items-center rounded-full bg-amber-100 text-amber-600">
                                <ShieldCheck class="h-6 w-6" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Rice - Leaf Blight</p>
                                <p class="text-xs text-slate-500">2 days ago · Medium severity</p>
                            </div>
                        </div>
                        <span
                            class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pending</span>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="grid h-12 w-12 place-items-center rounded-full bg-emerald-100 text-emerald-700">
                                <CheckCircle2 class="h-6 w-6" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Tomato - Pest Control</p>
                                <p class="text-xs text-slate-500">5 days ago · Low severity</p>
                            </div>
                        </div>
                        <span
                            class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Resolved</span>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>