<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import {
    BookOpen,
    Camera,
    CheckCircle2,
    FileText,
    MessageCircle,
    ShieldCheck,
    Sun,
} from "lucide-vue-next";
import ClientNavbar from "@/components/Client/ClientNavbar.vue";
import QuickActionsCard from "@/components/Client/QuickActionsCard.vue";
import WeatherCard from "@/components/Client/WeatherCard.vue";
import WeatherForecast from "@/components/Client/WeatherForecast.vue";

type RecentDiagnosis = {
    id: number | string;
    title: string;
    status: string;
    submitted_at: string | null;
    confidence_score: number | null;
    confidence_label: string | null;
};

const page = usePage();
const user = page.props.auth?.user ?? null;
const recentDiagnoses = (page.props.recentDiagnoses ?? []) as RecentDiagnosis[];

const weatherLoading = ref(true);
const currentWeather = ref<any | null>(null);

async function fetchCurrentWeather() {
    try {
        const res = await fetch('/client/weather/current');
        const json = await res.json();
        if (json.success) currentWeather.value = json.data;
    } catch (e) {
        // ignore
    } finally {
        weatherLoading.value = false;
    }
}

onMounted(() => void fetchCurrentWeather());

const statusMeta: Record<string, { label: string; icon: typeof ShieldCheck; iconClass: string; badgeClass: string }> = {
    pending: {
        label: "Pending",
        icon: ShieldCheck,
        iconClass: "bg-amber-100 text-amber-600",
        badgeClass: "bg-amber-100 text-amber-700",
    },
    processing: {
        label: "Processing",
        icon: Sun,
        iconClass: "bg-slate-100 text-slate-600",
        badgeClass: "bg-slate-100 text-slate-700",
    },
    completed: {
        label: "Resolved",
        icon: CheckCircle2,
        iconClass: "bg-emerald-100 text-emerald-700",
        badgeClass: "bg-emerald-100 text-emerald-700",
    },
    failed: {
        label: "Failed",
        icon: ShieldCheck,
        iconClass: "bg-rose-100 text-rose-600",
        badgeClass: "bg-rose-100 text-rose-700",
    },
};

const confidenceMeta = (score: RecentDiagnosis["confidence_score"]) => {
    if (score === null || Number.isNaN(score)) {
        return { label: "Check", badgeClass: "bg-slate-100 text-slate-700" };
    }

    if (score >= 80) {
        return { label: "High", badgeClass: "bg-rose-100 text-rose-700" };
    }

    if (score >= 50) {
        return { label: "Medium", badgeClass: "bg-amber-100 text-amber-700" };
    }

    return { label: "Low", badgeClass: "bg-emerald-100 text-emerald-700" };
};
</script>

<template>
    <Head title="Dashboard" />
    <div class="min-h-screen bg-slate-50">
        <ClientNavbar />

        <main class="mx-auto w-full max-w-6xl px-4 pb-16 pt-8">
            <section
                class="relative overflow-hidden rounded-3xl border border-emerald-200/60 bg-emerald-700/10 px-6 py-8 shadow-sm"
            >
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(16,185,129,0.55),rgba(15,23,42,0.25))]"
                ></div>
                <div
                    class="absolute inset-0 bg-[linear-gradient(130deg,rgba(255,255,255,0.32),rgba(255,255,255,0))]"
                ></div>
                <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div class="space-y-2 text-white">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-100">Welcome back</p>
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">{{ user?.name ?? "there" }}</h1>
                        <div v-if="weatherLoading" class="space-y-2">
                            <div class="h-8 w-48 rounded-full bg-white/30 animate-pulse"></div>
                            <div class="h-4 w-32 rounded-full bg-white/20 animate-pulse"></div>
                        </div>
                        <div v-else-if="currentWeather" class="space-y-1">
                            <p class="text-3xl font-extrabold tracking-tight md:text-4xl">
                                {{ currentWeather.temperature }}&deg;C - {{ currentWeather.condition }}
                            </p>
                            <p class="text-sm text-emerald-50/90">
                                {{ currentWeather.description }} · {{ currentWeather.humidity }}% humidity
                            </p>
                        </div>
                        <div v-else class="space-y-1">
                            <p class="text-3xl font-extrabold tracking-tight md:text-4xl">Weather unavailable</p>
                            <p class="text-sm text-emerald-50/90">Check your API key or try again later.</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <WeatherCard />
                        <WeatherForecast />
                    </div>
                </div>
            </section>

            <section class="mt-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Quick Actions</h2>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <QuickActionsCard
                        title="New Diagnosis"
                        description="Submit a new plant diagnosis"
                        :icon="Camera"
                        href="/client/diagnose"
                        :class="' bg-emerald-300 text-emerald-700'"
                    />
                    <QuickActionsCard
                        title="My Reports"
                        description="View your past diagnoses and results"
                        :icon="FileText"
                        href="/client/reports"
                        :class="' bg-orange-300 text-slate-700'"
                    />
                    <QuickActionsCard
                        title="Support"
                        description="Contact our support team for help"
                        :icon="MessageCircle"
                        href="/client/support"
                        :class="' bg-purple-300 text-slate-700'"
                    />
                    <QuickActionsCard
                        title="Knowledge Hub"
                        description="Browse the disease knowledge base"
                        :icon="BookOpen"
                        href="/client/knowledgehub"
                        :class="' bg-green-300 text-slate-700'"
                    />
                </div>
            </section>

            <section class="mt-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Recent Activity</h2>
                    <Link href="/client/reports" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                        View all
                    </Link>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div
                        v-for="diagnosis in recentDiagnoses"
                        :key="diagnosis.id"
                        class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="grid h-12 w-12 place-items-center rounded-full"
                                :class="statusMeta[diagnosis.status]?.iconClass ?? 'bg-slate-100 text-slate-600'"
                            >
                                <component
                                    :is="statusMeta[diagnosis.status]?.icon ?? ShieldCheck"
                                    class="h-6 w-6"
                                    aria-hidden="true"
                                />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ diagnosis.title }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ diagnosis.submitted_at ?? "Recently" }}
                                    <span v-if="diagnosis.confidence_label">
                                        · {{ diagnosis.confidence_label }} confidence
                                    </span>
                                </p>
                            </div>
                        </div>
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="diagnosis.status === 'completed'
                                ? confidenceMeta(diagnosis.confidence_score).badgeClass
                                : (statusMeta[diagnosis.status]?.badgeClass ?? 'bg-slate-100 text-slate-700')"
                        >
                            {{ diagnosis.status === 'completed'
                                ? confidenceMeta(diagnosis.confidence_score).label
                                : (statusMeta[diagnosis.status]?.label ?? diagnosis.status) }}
                        </span>
                    </div>

                    <div
                        v-if="recentDiagnoses.length === 0"
                        class="flex items-center justify-between rounded-2xl border border-dashed border-slate-200 bg-white px-5 py-6 text-sm text-slate-500"
                    >
                        No recent diagnoses yet. Submit a new diagnosis to get started.
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
