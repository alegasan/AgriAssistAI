<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { route } from "ziggy-js";
import AdminDashboard from "@/layouts/Admin/AdminDashboard.vue";

type DiseaseRow = {
    id: number | string;
    name: string | null;
    image_url: string | null;
    symptoms: string | null;
    treatment: string | null;
    total_diagnoses: number | null;
    last_diagnosed_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedDiseases = {
    data: DiseaseRow[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
};

const props = defineProps<{
    diseases: PaginatedDiseases;
    filters?: {
        search?: string;
    };
}>();

const diseases = computed(() => props.diseases);
const searchQuery = ref(props.filters?.search ?? "");
const searchDebounceTimeout = ref<number | null>(null);
const isLoading = ref(false);
const isSyncingFromServer = ref(false);

const requestDiseases = (): void => {
    const trimmedSearch = searchQuery.value.trim();

    router.get(
        route("admin.diseases.index"),
        {
            search: trimmedSearch !== "" ? trimmedSearch : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ["diseases", "filters"],
            onStart: () => {
                isLoading.value = true;
            },
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

const formatDate = (value: string | null): string => {
    if (!value) {
        return "—";
    }

    const parsed = new Date(value);

    if (Number.isNaN(parsed.getTime())) {
        return value;
    }

    return parsed.toLocaleDateString();
};

const previewText = (value: string | null, fallback: string): string => {
    if (!value || value.trim() === "") {
        return fallback;
    }

    return value;
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
            requestDiseases();
        }, 350);
    },
);

watch(
    () => props.filters,
    (incomingFilters) => {
        isSyncingFromServer.value = true;

        const incomingSearch = incomingFilters?.search ?? "";

        if (incomingSearch !== searchQuery.value) {
            searchQuery.value = incomingSearch;
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
</script>

<template>
    <AdminDashboard :title="'Diseases'" :description="'List of all diseases diagnosed through the system.'">
        <div class="mt-8 space-y-5">
            <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="min-w-[16rem] flex-1">
                    <label class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                        Search diseases
                    </label>
                    <input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Search by disease name or symptoms"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200"
                    />
                </div>
                <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">
                    {{ diseases.total }} total entries
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Image</th>
                                <th class="px-6 py-4">Disease</th>
                                <th class="px-6 py-4">Symptoms</th>
                                <th class="px-6 py-4">Treatment</th>
                                <th class="px-6 py-4">Diagnoses</th>
                                <th class="px-6 py-4">Last seen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="disease in diseases.data" :key="disease.id" class="bg-white">
                                <td class="px-6 py-4">
                                    <div class="flex h-12 w-16 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                        <img
                                            v-if="disease.image_url"
                                            :src="disease.image_url"
                                            :alt="disease.name ?? 'Disease image'"
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        />
                                        <span v-else class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">No image</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">
                                        {{ disease.name ?? "Unknown disease" }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <p class="max-w-[18rem] truncate">
                                        {{ previewText(disease.symptoms, "No symptom notes yet.") }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <p class="max-w-[18rem] truncate">
                                        {{ previewText(disease.treatment, "No treatment details yet.") }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    {{ disease.total_diagnoses ?? 0 }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ formatDate(disease.last_diagnosed_at) }}
                                </td>
                            </tr>
                            <tr v-if="!isLoading && diseases.data.length === 0">
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">
                                    No diseases found yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                v-if="diseases.last_page > 1"
                class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-3"
            >
                <p class="text-xs text-slate-500">
                    Showing
                    <span class="font-semibold text-slate-900">{{ diseases.from ?? 0 }}</span>
                    to
                    <span class="font-semibold text-slate-900">{{ diseases.to ?? 0 }}</span>
                    of
                    <span class="font-semibold text-slate-900">{{ diseases.total }}</span>
                    results
                </p>
                <div class="flex flex-wrap gap-1">
                    <template v-for="link in diseases.links" :key="link.label">
                        <span
                            v-if="!link.url"
                            class="rounded-lg border border-slate-200 px-3 py-1 text-xs text-slate-400"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            class="rounded-lg border border-slate-200 px-3 py-1 text-xs text-slate-600 transition hover:border-emerald-200 hover:text-emerald-700"
                            :class="link.active ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : ''"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminDashboard>
</template>