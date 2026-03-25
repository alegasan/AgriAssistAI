<script lang="ts" setup>
import {
    Biohazard,
    ClipboardList,
    Clock,
    UserCog,
    Users,
} from "lucide-vue-next"
import CardInfo from "@/components/CardInfo.vue";
import AdminDashboard from "@/layouts/Admin/AdminDashboard.vue";
import QuickActionsCard from "@/components/Client/QuickActionsCard.vue";

defineProps<{
    stats: {
        farmers:   { total: number; new_today: number }
        diseases:  { total: number | null; new_today: number | null }
        diagnoses: { total: number; new_today: number }
    }
}>()
</script>


<template>

    <AdminDashboard :title="'Admin Dashboard'" :description="'Heres an overview of the latest stats and activity on PlantGuard AI.'">

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
                    <div class="space-y-0 divide-y divide-slate-100">
                        <div class="flex items-center gap-4 px-5 py-4">
                            <div class="grid h-10 w-10 place-items-center rounded-full bg-emerald-100 text-emerald-700">
                                <Users class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">New farmer registered: Suresh Patel</p>
                                <p class="text-xs text-slate-500">5 min ago</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 px-5 py-4">
                            <div class="grid h-10 w-10 place-items-center rounded-full bg-amber-100 text-amber-600">
                                <ClipboardList class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Diagnosis submitted for Rice - Leaf
                                    Blight
                                </p>
                                <p class="text-xs text-slate-500">12 min ago</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 px-5 py-4">
                            <div class="grid h-10 w-10 place-items-center rounded-full bg-amber-100 text-amber-600">
                                <Biohazard class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Disease entry updated: Tomato Mosaic
                                    Virus
                                </p>
                                <p class="text-xs text-slate-500">1 hour ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </AdminDashboard>

</template>