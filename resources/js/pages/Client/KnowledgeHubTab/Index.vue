<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { route } from "ziggy-js";
import ClientDashboard from "@/layouts/Client/ClientDashboard.vue";

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
const isSyncingFromServer = ref(false);
const isLoading = ref(false);

const requestDiseases = (): void => {
    const trimmedSearch = searchQuery.value.trim();

    router.get(
        route("client.knowledgehub"),
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

const decodeHtmlEntities = (value: string): string => {
    const textarea = document.createElement("textarea");
    textarea.innerHTML = value;
    return textarea.value;
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
    <ClientDashboard
        :title="'Knowledge Hub'"
        :description="'Search trusted summaries of diagnosed plant diseases.'">
    

    <section class="mx-auto w-full max-w-6xl space-y-6 px-4 pb-16 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="min-w-[16rem] flex-1">
                <label class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                    Search diseases
                </label>
                <input
                    v-model="searchQuery"
                    type="search"
                    placeholder="Try leaf spot, blight, or rust"
                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200"
                />
            </div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">
                {{ diseases.total }} topics
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <article
                v-for="disease in diseases.data"
                :key="disease.id"
                class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div class="relative h-40 w-full bg-slate-100">
                    <img
                        v-if="disease.image_url"
                        :src="disease.image_url"
                        :alt="disease.name ?? 'Disease image'"
                        class="h-full w-full object-cover"
                        loading="lazy"
                    />
                    <div v-else class="flex h-full items-center justify-center text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                        No image
                    </div>
                </div>

                <div class="flex flex-1 flex-col gap-4 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-extrabold tracking-tight text-slate-900">
                                {{ disease.name ?? "Unknown disease" }}
                            </h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Last diagnosed {{ formatDate(disease.last_diagnosed_at) }}
                            </p>
                        </div>
                        <span class="rounded-full bg-emerald-100/70 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                            {{ disease.total_diagnoses ?? 0 }} reports
                        </span>
                    </div>

                    <div class="space-y-4 text-sm text-slate-700">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-700">Symptoms</p>
                        <p class="mt-2 whitespace-pre-wrap">
                            {{ previewText(disease.symptoms, "No symptom notes yet.") }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-700">Treatment</p>
                        <p class="mt-2 whitespace-pre-wrap">
                            {{ previewText(disease.treatment, "No treatment guidance yet.") }}
                        </p>
                    </div>
                    </div>
                </div>
            </article>
        </div>

        <div v-if="!isLoading && diseases.data.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <p class="text-sm font-semibold text-slate-900">No disease entries yet.</p>
            <p class="mt-2 text-xs text-slate-500">Once diagnoses are completed, the Knowledge Hub will populate.</p>
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
                        v-text="decodeHtmlEntities(link.label)"
                    ></span>
                    <Link
                        v-else
                        :href="link.url"
                        class="rounded-lg border border-slate-200 px-3 py-1 text-xs text-slate-600 transition hover:border-emerald-200 hover:text-emerald-700"
                        :class="link.active ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : ''"
                        v-text="decodeHtmlEntities(link.label)"
                    ></Link>
                </template>
            </div>
        </div>
    </section>
    </ClientDashboard>
</template>
