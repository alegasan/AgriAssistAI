<script lang="ts" setup>
import {
    Biohazard,
    ClipboardList,
    Clock,
    UserCog,
    Users,
} from "lucide-vue-next"
import CardInfo from "@/components/CardInfo.vue";
import QuickActionsCard from "@/components/Client/QuickActionsCard.vue";
import AdminDashboard from "@/layouts/Admin/AdminDashboard.vue";

type RecentActivityItem = {
    id: number
    action: string
    message: string
    icon_key: string
    occurred_at: string | null
    occurred_at_human: string | null
}

defineProps<{
    stats: {
        farmers:   { total: number; new_today: number }
        diseases:  { total: number | null; new_today: number | null }
        diagnoses: { total: number; new_today: number }
    }
    recentActivity?: RecentActivityItem[]
}>()

function activityIcon(iconKey: string) {
    switch (iconKey) {
        case "users":
            return Users
        case "clipboard":
            return ClipboardList
        case "user-cog":
            return UserCog
        default:
            return Clock
    }
}

function activityBadgeClasses(iconKey: string) {
    switch (iconKey) {
        case "users":
            return "bg-emerald-100 text-emerald-700"
        case "clipboard":
            return "bg-amber-100 text-amber-600"
        case "user-cog":
            return "bg-emerald-100 text-emerald-700"
        default:
            return "bg-slate-100 text-slate-600"
    }
}
</script>


<template>

    <AdminDashboard :title="'Admin Dashboard'" :description="'Here\'s an overview of the latest stats and activity on AgriAssist AI.'">

        <div class="mt-8 grid gap-5 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            <CardInfo title="Total Users" :value="(stats?.farmers?.total ?? 0).toLocaleString()"  :change="`+${stats?.farmers?.new_today ?? 0}`" description="Total registered farmers" :icon="Users" />
            <CardInfo
                title="Active Diseases"
                :value="stats?.diseases?.total === null ? 'N/A' : (stats?.diseases?.total ?? 0).toLocaleString()"
                :change="stats?.diseases?.new_today === null ? '—' : `+${stats?.diseases?.new_today ?? 0}`"
                description="Diseases in knowledge base"
                :icon="Biohazard"
            />
            <CardInfo title="Total Diagnoses" :value="(stats?.diagnoses?.total ?? 0).toLocaleString()" :change="`+${stats?.diagnoses?.new_today ?? 0}`" description="Diagnoses submitted by farmers" :icon="ClipboardList" />
        </div>

        <section class="mt-8 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-base font-semibold text-slate-900">
                    <span class="text-emerald-600">Quick Actions</span>
                </div>

                <div class="space-x-1">
                    <QuickActionsCard
                        title="Manage Users"
                        description="View and manage all accounts"
                        :icon="UserCog"
                        layout="inline"
                        href="/admin/users"
                        :class="' bg-emerald-100 text-emerald-700'"
                       
                    />
                    <QuickActionsCard
                        title="Manage Diseases"
                        description="View disease knowledge base"
                        :icon="Biohazard"
                        layout="inline"
                        href="/admin/diseases"
                        :class="' bg-rose-100 text-rose-600'"
                    />
                    <QuickActionsCard
                        title="All Diagnoses"
                        description="Browse submitted diagnoses"
                        :icon="ClipboardList"
                        layout="inline"
                        href="/admin/diagnoses"
                        :class="' bg-amber-100 text-amber-600'"
                    />
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center gap-2 text-base font-semibold text-slate-900">
                    <Clock class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                    <span>Recent Activity</span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="space-y-0 divide-y divide-slate-100 max-h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-emerald-200 scrollbar-track-slate-50">
                        <template v-if="recentActivity?.length">
                            <div v-for="item in recentActivity" :key="item.id" class="flex items-center gap-4 px-5 py-4">
                                <div
                                    class="grid h-10 w-10 place-items-center rounded-full"
                                    :class="activityBadgeClasses(item.icon_key)"
                                >
                                    <component :is="activityIcon(item.icon_key)" class="h-5 w-5" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ item.message }}</p>
                                    <p class="text-xs text-slate-500">{{ item.occurred_at_human ?? "—" }}</p>
                                </div>
                            </div>
                        </template>

                        <div v-else class="flex items-center gap-4 px-5 py-4">
                            <div class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600">
                                <Clock class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">No recent activity yet</p>
                                <p class="text-xs text-slate-500">—</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </AdminDashboard>

</template>