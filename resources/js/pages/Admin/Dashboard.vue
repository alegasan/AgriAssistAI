<script lang="ts" setup>
import {
    AlertCircle,
    Biohazard,
    ClipboardList,
    Clock,
    UserCog,
    Users,
} from "lucide-vue-next"
import AdminDashboard from "@/layouts/Admin/AdminDashboard.vue";
import CardInfo from "@/components/CardInfo.vue";
</script>


<template>

    <AdminDashboard :title="'Admin Dashboard'" :description="'Heres an overview of the latest stats and activity on PlantGuard AI.'">

        <div class="mt-8 grid gap-5 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
            <CardInfo title="Total Users" value="1,284" change="+12" description="Total registered farmers" :icon="Users" />
            <CardInfo title="Active Diseases" value="47" change="+3" description="Diseases in knowledge base" :icon="Biohazard" />
            <CardInfo title="Total Diagnoses" value="8,432" change="+47" description="Diagnoses submitted by farmers" :icon="ClipboardList" />
            <CardInfo title="Pending Reviews" value="89" change="+5" description="Diagnoses awaiting expert review" :icon="Clock" />
        </div>

        <section class="mt-8 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-base font-semibold text-slate-900">
                    <span class="text-emerald-600">Quick Actions</span>
                </div>

                <div class="space-y-3">
                    <div
                        class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                        <div class="grid h-12 w-12 place-items-center rounded-full bg-emerald-100 text-emerald-700">
                            <UserCog class="h-6 w-6" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Manage Users</p>
                            <p class="text-xs text-slate-500">View and manage all accounts</p>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                        <div class="grid h-12 w-12 place-items-center rounded-full bg-rose-100 text-rose-600">
                            <Biohazard class="h-6 w-6" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Manage Diseases</p>
                            <p class="text-xs text-slate-500">CRUD disease knowledge base</p>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                        <div class="grid h-12 w-12 place-items-center rounded-full bg-amber-100 text-amber-600">
                            <ClipboardList class="h-6 w-6" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">All Diagnoses</p>
                            <p class="text-xs text-slate-500">Browse submitted diagnoses</p>
                        </div>
                    </div>
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