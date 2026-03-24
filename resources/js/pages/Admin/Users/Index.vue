<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { CircleAlert, CircleCheckBig, Filter } from "lucide-vue-next";
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { route } from "ziggy-js";
import AlertDialog from "@/components/AlertDialog.vue";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import AdminDashboard from "@/layouts/Admin/AdminDashboard.vue";

type StatusFilter = "all" | "active" | "inactive";

type UserRow = {
    id: number | string;
    name: string;
    email: string;
    diagnoses_count?: number | null;
    is_active?: boolean | null;
    created_at_formatted?: string | null;
};

const props = defineProps<{
    users: UserRow[];
    filters?: {
        search?: string;
        status?: string;
    };
}>();

const statusOptions: Array<{ label: string; value: StatusFilter }> = [
    { label: "All", value: "all" },
    { label: "Active", value: "active" },
    { label: "Inactive", value: "inactive" },
];

const normalizeStatusFilter = (value?: string): StatusFilter => {
    if (value === "active" || value === "inactive") {
        return value;
    }

    return "all";
};

const searchQuery = ref(props.filters?.search ?? "");
const selectedStatus = ref<StatusFilter>(normalizeStatusFilter(props.filters?.status));
const searchDebounceTimeout = ref<number | null>(null);
const isLoadingUsers = ref(false);
const isSyncingFromServer = ref(false);
const togglingUserId = ref<number | string | null>(null);
const showToast = ref(false);
const toastMessage = ref("");
const toastVariant = ref<"success" | "error">("success");
const toastHideTimeout = ref<number | null>(null);

const users = computed(() => props.users ?? []);

const requestUsers = (): void => {
    const trimmedSearch = searchQuery.value.trim();

    router.get(
        route("admin.users.index"),
        {
            search: trimmedSearch !== "" ? trimmedSearch : undefined,
            status: selectedStatus.value !== "all" ? selectedStatus.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ["users", "filters"],
            onStart: () => {
                isLoadingUsers.value = true;
            },
            onFinish: () => {
                isLoadingUsers.value = false;
            },
        },
    );
};

const getCsrfToken = (): string => {
    const metaTag = document.querySelector('meta[name="csrf-token"]');

    if (metaTag instanceof HTMLMetaElement && metaTag.content) {
        return metaTag.content;
    }

    return "";
};

const clearToastTimeout = (): void => {
    if (toastHideTimeout.value !== null) {
        window.clearTimeout(toastHideTimeout.value);
        toastHideTimeout.value = null;
    }
};

const showToastMessage = (message: string, variant: "success" | "error"): void => {
    clearToastTimeout();
    toastMessage.value = message;
    toastVariant.value = variant;
    showToast.value = true;

    toastHideTimeout.value = window.setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

const toggleUserStatus = async (user: UserRow): Promise<void> => {
    if (togglingUserId.value !== null) {
        return;
    }

    togglingUserId.value = user.id;

    try {
        try {
            const response = await window.fetch(route("admin.users.toggle-status", user.id), {
                method: "PATCH",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
                credentials: "same-origin",
            });

            let message = "User status updated successfully.";

            try {
                const payload = (await response.json()) as { message?: string };
                if (payload?.message) {
                    message = payload.message;
                }
            } catch {
                // Keep default message if response is not JSON.
            }

            if (!response.ok) {
                showToastMessage(message || "Unable to update user status.", "error");
                return;
            }

            requestUsers();
            showToastMessage(message, "success");
        } catch {
            showToastMessage("Network error: Unable to update user status.", "error");
        }
    } finally {
        togglingUserId.value = null;
    }
};

const statusLabel = (value?: boolean | null) => (value ? "Active" : "Inactive");

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
            requestUsers();
        }, 350);
    },
);

watch(
    () => selectedStatus.value,
    () => {
        if (isSyncingFromServer.value) {
            return;
        }

        requestUsers();
    },
);

watch(
    () => props.filters,
    (incomingFilters) => {
        isSyncingFromServer.value = true;

        const incomingSearch = incomingFilters?.search ?? "";
        const incomingStatus = normalizeStatusFilter(incomingFilters?.status);

        if (incomingSearch !== searchQuery.value) {
            searchQuery.value = incomingSearch;
        }

        if (incomingStatus !== selectedStatus.value) {
            selectedStatus.value = incomingStatus;
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

    clearToastTimeout();
});

const toggleActionLabel = (user: UserRow): string => (user.is_active ? "Deactivate" : "Activate");

const toggleDialogTitle = (user: UserRow): string =>
    user.is_active ? "Deactivate this user?" : "Activate this user?";

const toggleDialogDescription = (user: UserRow): string =>
    user.is_active
        ? `This will prevent ${user.name} from accessing their account until reactivated.`
        : `This will restore ${user.name}'s access to their account.`;

const toggleTriggerClass = (user: UserRow): string =>
    user.is_active
        ? "rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100"
        : "rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100";

const toggleConfirmClass = (user: UserRow): string =>
    user.is_active
        ? "rounded-xl bg-rose-600 text-white hover:bg-rose-700"
        : "rounded-xl bg-emerald-600 text-white hover:bg-emerald-700";
</script>

<template>
    <AdminDashboard :title="'Users'" :description="'Manage all registered farmers and their accounts.'">
        <Transition
            enter-active-class="transition-all duration-500 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition-all duration-500 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div v-if="showToast" class="fixed right-3 top-3 z-[70] w-[min(92vw,26rem)] sm:right-6">
                <Alert
                    :class="
                        toastVariant === 'success'
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-900 shadow-lg'
                            : 'border-rose-200 bg-rose-50 text-rose-900 shadow-lg'
                    "
                >
                    <CircleCheckBig v-if="toastVariant === 'success'" class="text-emerald-700" />
                    <CircleAlert v-else class="text-rose-700" />
                    <AlertTitle class="text-current">
                        {{ toastVariant === "success" ? "Success" : "Error" }}
                    </AlertTitle>
                    <AlertDescription class="text-current/80">
                        {{ toastMessage }}
                    </AlertDescription>
                </Alert>
            </div>
        </Transition>
        <div class="mt-8 space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search users..."
                    class="w-full flex-1 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    :aria-busy="isLoadingUsers"
                />
                <div class="relative w-full sm:w-48">
                    <Filter class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <select
                        v-model="selectedStatus"
                        class="w-full rounded-full border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        :aria-busy="isLoadingUsers"
                    >
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-4">User</th>
                                <th class="px-6 py-4">Joined</th>
                                <th class="px-6 py-4">Diagnoses</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="user in users" :key="user.id" class="bg-white">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">{{ user.name }}</div>
                                    <div class="text-xs text-slate-500">{{ user.email }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ user.created_at_formatted}}</td>
                                <td class="px-6 py-4 text-slate-900">{{ user.diagnoses_count ?? 0  }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            user.is_active
                                                ? 'bg-emerald-100/70 text-emerald-700 ring-1 ring-emerald-200'
                                                : 'bg-rose-100/80 text-rose-700 ring-1 ring-rose-200'
                                        "
                                    >
                                        {{ statusLabel(user.is_active) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-slate-400">
                                    <AlertDialog
                                        :title="toggleDialogTitle(user)"
                                        :description="toggleDialogDescription(user)"
                                        :trigger-label="toggleActionLabel(user)"
                                        :confirm-label="toggleActionLabel(user)"
                                        cancel-label="Cancel"
                                        :trigger-class="toggleTriggerClass(user)"
                                        :confirm-class="toggleConfirmClass(user)"
                                        :trigger-disabled="togglingUserId === user.id"
                                        :confirm-disabled="togglingUserId === user.id"
                                        @confirm="toggleUserStatus(user)"
                                    />
                                </td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">
                                    No users found for the selected filters.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminDashboard>
</template>