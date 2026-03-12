<script setup lang="ts">
import AdminDashboard from "@/layouts/Admin/AdminDashboard.vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const users = page.props.users as Array<{
    id: number | string;
    name: string;
    email: string;
    diagnoses?: number | null;
    is_active?: boolean | null;
    created_at_formatted?: string | null;
}>;



const statusLabel = (value?: boolean | null) => (value ? "Active" : "Inactive");
</script>

<template>
    <AdminDashboard :title="'Users'" :description="'Manage all registered farmers and their accounts.'">
        <div class="mt-8 space-y-5">
            <div class="relative">
                <input
                    type="text"
                    placeholder="Search users..."
                    class="w-full rounded-full border border-slate-200 bg-white px-5 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                />
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
                                <td class="px-6 py-4 text-slate-900">{{ user.diagnoses ?? 0 }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            user.is_active
                                                ? 'bg-emerald-100/70 text-emerald-700'
                                                : 'bg-slate-100 text-slate-600'
                                        "
                                    >
                                        {{ statusLabel(user.is_active) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-slate-400">
                                    <button
                                        type="button"
                                        class="rounded-full px-2 py-1 text-lg font-semibold text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                                        aria-label="User actions"
                                    >
                                        ...
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminDashboard>
</template>