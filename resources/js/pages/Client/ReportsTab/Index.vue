<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import ClientDashboard from '@/layouts/Client/ClientDashboard.vue';
import AlertDialog from '@/components/AlertDialog.vue';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';

type RiskLevel = 'Low' | 'Medium' | 'High';
type RiskFilter = 'all' | 'low' | 'medium' | 'high';

type ReportItem = {
    id: number;
    title: string;
    crop: string;
    submitted_ago: string;
    created_at: string | null;
    risk: RiskLevel;
    notes: string;
    image_url: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedReports = {
    data: ReportItem[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
};

const props = defineProps<{
    diagnoses: PaginatedReports;
    filters?: {
        search?: string;
        risk?: string;
    };
}>();

const riskOptions: Array<{ label: string; value: RiskFilter }> = [
    { label: 'All', value: 'all' },
    { label: 'High', value: 'high' },
    { label: 'Medium', value: 'medium' },
    { label: 'Low', value: 'low' },
];

const normalizeRiskFilter = (value?: string): RiskFilter => {
    if (value === 'high' || value === 'medium' || value === 'low') {
        return value;
    }

    return 'all';
};

const searchQuery = ref(props.filters?.search ?? '');
const selectedRisk = ref<RiskFilter>(normalizeRiskFilter(props.filters?.risk));
const searchDebounceTimeout = ref<number | null>(null);
const isLoadingReports = ref(false);
const isSyncingFromServer = ref(false);
const isReportSheetOpen = ref(false);
const selectedReport = ref<ReportItem | null>(null);
const isDeletingReport = ref(false);

const reports = computed(() => props.diagnoses?.data ?? []);
const pagination = computed(() => props.diagnoses);
const highRiskCount = computed(() => reports.value.filter((report) => report.risk === 'High').length);
const recentWeekCount = computed(() => {
    const now = Date.now();
    const sevenDaysInMs = 7 * 24 * 60 * 60 * 1000;

    return reports.value.filter((report) => {
        if (!report.created_at) {
            return false;
        }

        const createdAtMs = new Date(report.created_at).getTime();

        if (Number.isNaN(createdAtMs)) {
            return false;
        }

        return now - createdAtMs <= sevenDaysInMs;
    }).length;
});

const requestReports = (page = 1): void => {
    const trimmedSearch = searchQuery.value.trim();

    router.get(
        route('client.reports'),
        {
            search: trimmedSearch !== '' ? trimmedSearch : undefined,
            risk: selectedRisk.value !== 'all' ? selectedRisk.value : undefined,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['diagnoses', 'filters'],
            onStart: () => {
                isLoadingReports.value = true;
            },
            onFinish: () => {
                isLoadingReports.value = false;
            },
        },
    );
};

const applyRiskFilter = (risk: RiskFilter): void => {
    if (selectedRisk.value === risk) {
        return;
    }

    selectedRisk.value = risk;
    requestReports(1);
};

const openReportSheet = (report: ReportItem): void => {
    selectedReport.value = report;
    isReportSheetOpen.value = true;
};

const handleSheetOpenChange = (open: boolean): void => {
    isReportSheetOpen.value = open;

    if (!open) {
        selectedReport.value = null;
    }
};

const deleteSelectedReport = (): void => {
    if (!selectedReport.value || isDeletingReport.value) {
        return;
    }

    const reportId = selectedReport.value.id;

    router.delete(route('client.reports.destroy', reportId), {
        preserveScroll: true,
        onStart: () => {
            isDeletingReport.value = true;
        },
        onFinish: () => {
            isDeletingReport.value = false;
        },
        onSuccess: () => {
            isReportSheetOpen.value = false;
            selectedReport.value = null;
        },
    });
};

watch(
    () => searchQuery.value,
    () => {
        if (isSyncingFromServer.value) {
            return;
        }

        if (searchDebounceTimeout.value !== null) {
            window.clearTimeout(searchDebounceTimeout.value);
        }

        searchDebounceTimeout.value = window.setTimeout(() => {
            requestReports(1);
        }, 350);
    },
);

watch(
    () => props.filters,
    (incomingFilters) => {
        isSyncingFromServer.value = true;

        const incomingSearch = incomingFilters?.search ?? '';
        const incomingRisk = normalizeRiskFilter(incomingFilters?.risk);

        if (incomingSearch !== searchQuery.value) {
            searchQuery.value = incomingSearch;
        }

        if (incomingRisk !== selectedRisk.value) {
            selectedRisk.value = incomingRisk;
        }

        window.setTimeout(() => {
            isSyncingFromServer.value = false;
        }, 0);
    },
);

onBeforeUnmount(() => {
    if (searchDebounceTimeout.value !== null) {
        window.clearTimeout(searchDebounceTimeout.value);
    }
});

const riskBadgeClass: Record<RiskLevel, string> = {
    Low: 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
    Medium: 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
    High: 'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
};
</script>


<template>
    <ClientDashboard :title="'Reports'" :description="'View your plant diagnosis reports and insights.'">
        <section class="mx-auto w-full max-w-6xl space-y-6 px-4 pb-8 sm:px-6 sm:pb-10 lg:px-8">
            <div class="rounded-2xl border border-emerald-100/70 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <label class="relative block w-full max-w-xl">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">Search</span>
                        <input
                            v-model="searchQuery"
                            type="search"
                            aria-label="Search reports"
                            placeholder="Search reports by crop or diagnosis"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-20 pr-4 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-2 focus:ring-emerald-100"
                        />
                    </label>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button
                        v-for="riskOption in riskOptions"
                        :key="riskOption.value"
                        type="button"
                        :aria-pressed="selectedRisk === riskOption.value"
                        :class="[
                            'inline-flex rounded-full px-3 py-1.5 text-xs font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300',
                            selectedRisk === riskOption.value
                                ? riskOption.value === 'high'
                                    ? 'bg-rose-100 text-rose-700 ring-1 ring-rose-200'
                                    : riskOption.value === 'medium'
                                        ? 'bg-amber-100 text-amber-700 ring-1 ring-amber-200'
                                        : riskOption.value === 'low'
                                            ? 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200'
                                            : 'bg-slate-200 text-slate-700 ring-1 ring-slate-300'
                                : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50',
                        ]"
                        @click="applyRiskFilter(riskOption.value)"
                    >
                        {{ riskOption.label }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Total Reports</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">{{ pagination.total }}</p>
                </article>

                <article class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">High Risk Alerts</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-emerald-800">{{ highRiskCount }}</p>
                </article>

                <article class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-amber-700">Recent This Week</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-amber-800">{{ recentWeekCount }}</p>
                </article>
            </div>

            <div v-if="reports.length > 0" class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <article
                    v-for="report in reports"
                    :key="report.id"
                    class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <button
                        type="button"
                        class="w-full p-4 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
                        @click="openReportSheet(report)"
                    >
                        <div class="flex items-start gap-4">
                            <img
                                :src="report.image_url"
                                :alt="report.title"
                                class="h-20 w-20 rounded-xl object-cover ring-1 ring-black/5"
                            />

                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <h3 class="truncate text-base font-bold text-slate-900">{{ report.title }}</h3>
                                    <span :class="riskBadgeClass[report.risk]" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">{{ report.risk }}</span>
                                </div>

                                <p class="text-sm text-slate-600">
                                    {{ report.crop }}
                                    <span class="mx-2 text-slate-300">•</span>
                                    {{ report.submitted_ago }}
                                </p>

                                <p class="line-clamp-2 text-sm text-slate-500">{{ report.notes }}</p>
        
                            </div>
                        </div>
                    </button>
                </article>
            </div>

            <Sheet :open="isReportSheetOpen" @update:open="handleSheetOpenChange">
                <SheetContent
                    side="right"
                    class="w-full overflow-y-auto border-l border-emerald-100/70 bg-slate-50 p-0 will-change-transform data-[state=open]:duration-350 data-[state=closed]:duration-250 data-[state=open]:ease-out data-[state=closed]:ease-in sm:max-w-xl"
                >
                    <template v-if="selectedReport">
                        <SheetHeader class="border-b border-emerald-100/70 bg-gradient-to-b from-emerald-50 to-white">
                            <div class="flex flex-wrap items-center gap-2">
                                <span :class="riskBadgeClass[selectedReport.risk]" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">{{ selectedReport.risk }} Risk</span>
                                <span class="inline-flex rounded-full bg-emerald-100/70 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    {{ selectedReport.crop }}
                                </span>
                            </div>
                            <SheetTitle class="pr-8 text-xl font-extrabold tracking-tight text-slate-900">{{ selectedReport.title }}</SheetTitle>
                            <SheetDescription class="text-slate-600">
                                Submitted {{ selectedReport.submitted_ago }}
                            </SheetDescription>
                        </SheetHeader>

                        <div class="space-y-5 p-4 pb-6 sm:p-5 sm:pb-8">
                            <img
                                :src="selectedReport.image_url"
                                :alt="selectedReport.title"
                                class="h-52 w-full rounded-2xl border border-slate-200 object-cover shadow-sm"
                            />

                            <article class="rounded-2xl border border-emerald-100/70 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-700">Report Notes</p>
                                <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">{{ selectedReport.notes }}</p>
                            </article>

                            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Report ID</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-900">#{{ selectedReport.id }}</dd>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Submitted</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ selectedReport.submitted_ago }}</dd>
                                </div>
                            </dl>

                            <div>
                                <AlertDialog
                                    title="Delete report permanently?"
                                    :description="`This action cannot be undone.`"
                                    trigger-label="Delete Report"
                                    confirm-label="Delete"
                                    cancel-label="Cancel"
                                    trigger-class="rounded-xl border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100"
                                    confirm-class="rounded-xl bg-rose-600 text-white hover:bg-rose-700"
                                    :trigger-disabled="isDeletingReport"
                                    :confirm-disabled="isDeletingReport"
                                    @confirm="deleteSelectedReport"
                                />
                            </div>
                        </div>
                    </template>
                </SheetContent>
            </Sheet>

            <div v-if="isLoadingReports" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <p class="text-sm font-medium text-emerald-800">Updating reports...</p>
            </div>

            <div v-if="pagination.last_page > 1" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-3">
                <p class="text-sm text-slate-600">
                    Showing
                    <span class="font-semibold text-slate-900">{{ pagination.from ?? 0 }}</span>
                    to
                    <span class="font-semibold text-slate-900">{{ pagination.to ?? 0 }}</span>
                    of
                    <span class="font-semibold text-slate-900">{{ pagination.total }}</span>
                </p>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="pagination.current_page <= 1 || isLoadingReports"
                        @click="requestReports(pagination.current_page - 1)"
                    >
                        Previous
                    </button>

                    <span class="text-sm text-slate-600">
                        Page
                        <span class="font-semibold text-slate-900">{{ pagination.current_page }}</span>
                        of
                        <span class="font-semibold text-slate-900">{{ pagination.last_page }}</span>
                    </span>

                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="pagination.current_page >= pagination.last_page || isLoadingReports"
                        @click="requestReports(pagination.current_page + 1)"
                    >
                        Next
                    </button>
                </div>
            </div>

            <article v-if="!isLoadingReports && reports.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                <p class="text-base font-semibold text-slate-800">No reports match your filters.</p>
                <p class="mt-1 text-sm text-slate-500">Try another keyword or switch the risk filter to All.</p>
            </article>
        </section>
    </ClientDashboard>

</template>
