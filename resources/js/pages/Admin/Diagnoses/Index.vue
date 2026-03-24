<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from "@/components/ui/sheet";
import AdminDashboard from "@/layouts/Admin/AdminDashboard.vue";

type DiagnosisUser = {
    id: number | string | null;
    name: string | null;
    email: string | null;
};

type DiagnosisRow = {
    id: number | string;
    plant_name: string | null;
    disease_name: string | null;
    status: string;
    confidence_score: number | string | null;
    symptoms: string | null;
    treatment: string | null;
    failure_reason: string | null;
    submitted_at: string | null;
    completed_at: string | null;
    image_url: string | null;
    user: DiagnosisUser | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedDiagnoses = {
    data: DiagnosisRow[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
};

const page = usePage();
const diagnoses = page.props.diagnoses as PaginatedDiagnoses;

const isDiagnosisSheetOpen = ref(false);
const selectedDiagnosis = ref<DiagnosisRow | null>(null);

const statusLabels: Record<string, string> = {
    pending: "Pending",
    processing: "Processing",
    completed: "Completed",
    failed: "Failed",
};

const statusClasses: Record<string, string> = {
    pending: "bg-amber-100 text-amber-700 ring-1 ring-amber-200",
    processing: "bg-slate-100 text-slate-700 ring-1 ring-slate-200",
    completed: "bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200",
    failed: "bg-rose-100 text-rose-700 ring-1 ring-rose-200",
};

const formatConfidence = (value: DiagnosisRow["confidence_score"]): string => {
    if (value === null || value === undefined || value === "") {
        return "—";
    }

    const numericValue = Number(value);

    if (Number.isNaN(numericValue)) {
        return String(value);
    }

    return `${numericValue.toFixed(1)}%`;
};

const openDiagnosis = (diagnosis: DiagnosisRow): void => {
    selectedDiagnosis.value = diagnosis;
    isDiagnosisSheetOpen.value = true;
};

const handleSheetOpenChange = (open: boolean): void => {
    isDiagnosisSheetOpen.value = open;

    if (!open) {
        selectedDiagnosis.value = null;
    }
};
</script>

<template>
    <AdminDashboard :title="'Diagnoses'" :description="'Manage all diagnosed plant conditions and treatments.'">
        <div class="mt-8 space-y-5">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Farmer</th>
                                <th class="px-6 py-4">Plant</th>
                                <th class="px-6 py-4">Diagnosis</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Confidence</th>
                                <th class="px-6 py-4">Submitted</th>
                                <th class="px-6 py-4">Completed</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="diagnosis in diagnoses.data" :key="diagnosis.id" class="bg-white">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">
                                        {{ diagnosis.user?.name ?? "Unknown" }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ diagnosis.user?.email ?? "No email" }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-900">
                                    {{ diagnosis.plant_name ?? "Unknown plant" }}
                                </td>
                                <td class="px-6 py-4 text-slate-900">
                                    {{ diagnosis.disease_name ?? "Unknown diagnosis" }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="statusClasses[diagnosis.status] ?? 'bg-slate-100 text-slate-600 ring-1 ring-slate-200'"
                                    >
                                        {{ statusLabels[diagnosis.status] ?? diagnosis.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    {{ formatConfidence(diagnosis.confidence_score) }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ diagnosis.submitted_at ?? "—" }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ diagnosis.completed_at ?? "—" }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        type="button"
                                        class="rounded-full border border-emerald-200 bg-white px-4 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50"
                                        @click="openDiagnosis(diagnosis)"
                                    >
                                        View all
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="diagnoses.data.length === 0">
                                <td colspan="8" class="px-6 py-8 text-center text-sm text-slate-500">
                                    No diagnoses found yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Sheet :open="isDiagnosisSheetOpen" @update:open="handleSheetOpenChange">
                <SheetContent
                    side="right"
                    class="w-full overflow-y-auto border-l border-emerald-100/70 bg-slate-50 p-0 will-change-transform data-[state=open]:duration-350 data-[state=closed]:duration-250 data-[state=open]:ease-out data-[state=closed]:ease-in sm:max-w-2xl"
                >
                    <template v-if="selectedDiagnosis">
                        <SheetHeader class="border-b border-emerald-100/70 bg-gradient-to-b from-emerald-50 to-white">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="statusClasses[selectedDiagnosis.status] ?? 'bg-slate-100 text-slate-600 ring-1 ring-slate-200'"
                                >
                                    {{ statusLabels[selectedDiagnosis.status] ?? selectedDiagnosis.status }}
                                </span>
                                <span class="inline-flex rounded-full bg-emerald-100/70 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    {{ selectedDiagnosis.plant_name ?? "Unknown plant" }}
                                </span>
                            </div>
                            <SheetTitle class="pr-8 text-xl font-extrabold tracking-tight text-slate-900">
                                {{ selectedDiagnosis.disease_name ?? "Unknown diagnosis" }}
                            </SheetTitle>
                            <SheetDescription class="text-slate-600">
                                Submitted {{ selectedDiagnosis.submitted_at ?? "—" }}
                            </SheetDescription>
                        </SheetHeader>

                        <div class="space-y-5 p-4 pb-6 sm:p-5 sm:pb-8">
                            <img
                                v-if="selectedDiagnosis.image_url"
                                :src="selectedDiagnosis.image_url"
                                :alt="selectedDiagnosis.disease_name ?? 'Diagnosis image'"
                                class="h-64 w-full rounded-2xl border border-slate-200 object-cover shadow-sm"
                            />
                            <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-8 text-center text-sm text-slate-500">
                                No image available for this diagnosis.
                            </div>

                            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Confidence</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ formatConfidence(selectedDiagnosis.confidence_score) }}</dd>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Completed</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ selectedDiagnosis.completed_at ?? "—" }}</dd>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Farmer</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-900">
                                        {{ selectedDiagnosis.user?.name ?? "Unknown" }}
                                    </dd>
                                    <dd class="text-xs text-slate-500">{{ selectedDiagnosis.user?.email ?? "No email" }}</dd>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Status</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-900">
                                        {{ statusLabels[selectedDiagnosis.status] ?? selectedDiagnosis.status }}
                                    </dd>
                                </div>
                            </dl>

                            <article class="rounded-2xl border border-emerald-100/70 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-700">Symptoms</p>
                                <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                                    {{ selectedDiagnosis.symptoms ?? "No symptom details captured." }}
                                </p>
                            </article>

                            <article class="rounded-2xl border border-emerald-100/70 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-700">Treatment</p>
                                <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                                    {{ selectedDiagnosis.treatment ?? "No treatment details captured." }}
                                </p>
                            </article>

                            <article v-if="selectedDiagnosis.failure_reason" class="rounded-2xl border border-rose-100 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-rose-700">Failure Reason</p>
                                <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                                    {{ selectedDiagnosis.failure_reason }}
                                </p>
                            </article>
                        </div>
                    </template>
                </SheetContent>
            </Sheet>

            <div
                v-if="diagnoses.last_page > 1"
                class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-3"
            >
                <p class="text-xs text-slate-500">
                    Showing
                    <span class="font-semibold text-slate-900">{{ diagnoses.from ?? 0 }}</span>
                    to
                    <span class="font-semibold text-slate-900">{{ diagnoses.to ?? 0 }}</span>
                    of
                    <span class="font-semibold text-slate-900">{{ diagnoses.total }}</span>
                    results
                </p>

                <div class="flex flex-wrap items-center gap-2">
                    <template v-for="link in diagnoses.links" :key="`${link.label}-${link.url ?? 'disabled'}`">
                        <span
                            v-if="!link.url"
                            class="rounded-full px-3 py-1 text-xs font-semibold text-slate-400"
                            v-html="link.label"
                        ></span>
                        <Link
                            v-else
                            :href="link.url"
                            class="rounded-full px-3 py-1 text-xs font-semibold transition"
                            :class="
                                link.active
                                    ? 'bg-emerald-600 text-white shadow-sm'
                                    : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'
                            "
                        >
                            <span v-html="link.label"></span>
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </AdminDashboard>
</template>