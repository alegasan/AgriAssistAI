<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Camera,
    Check,
    Cpu,
    FileText,
    Leaf,
    Menu,
    Scan,
    ShieldCheck,
    Sparkles,
    TrendingUp,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue'

const mobileNavOpen = ref(false)

</script>

<template>
    <Head title="Welcome" />
    <div class="relative min-h-screen overflow-x-hidden bg-[#f8faf9] font-sans selection:bg-emerald-500 selection:text-white">
      
        <nav class="sticky top-0 z-50 bg-white/50 px-4 py-4 backdrop-blur-md sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Leaf class="w-8 h-8 text-emerald-600" />
                    <span class="text-xl font-bold text-slate-900">AgriAssist AI</span>
                </div>

                <div class="hidden items-center gap-8 sm:flex">
                    <div class="flex items-center gap-8">
                        <a href="#features" class="text-slate-600 hover:text-emerald-700 font-medium transition-colors">Features</a>
                        <a href="#how-it-works" class="text-slate-600 hover:text-emerald-700 font-medium transition-colors">How It Works</a>
                    </div>

                    <Link href="/login" class="px-5 py-2.5 text-sm font-semibold text-emerald-700 bg-emerald-100/70 rounded-full hover:bg-emerald-100 transition-colors shadow-sm ring-1 ring-emerald-200 ring-offset-2">
                        Get Started
                    </Link>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg p-2 text-emerald-700 hover:text-emerald-800 hover:bg-white/60 ring-1 ring-emerald-200 sm:hidden"
                    :aria-expanded="mobileNavOpen"
                    aria-controls="mobile-nav"
                    aria-label="Toggle navigation"
                    @click="mobileNavOpen = !mobileNavOpen"
                >
                    <Menu v-if="!mobileNavOpen" class="size-5" aria-hidden="true" />
                    <X v-else class="size-5" aria-hidden="true" />
                </button>
            </div>

            <div v-if="mobileNavOpen" id="mobile-nav" class="mt-3 grid gap-2 rounded-2xl border border-slate-100 bg-white/90 p-3 shadow-sm sm:hidden">
                <a
                    href="#features"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    @click="mobileNavOpen = false"
                >
                    Features
                </a>
                <a
                    href="#how-it-works"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    @click="mobileNavOpen = false"
                >
                    How It Works
                </a>
                <Link
                    href="/login"
                    class="mt-1 inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700"
                    @click="mobileNavOpen = false"
                >
                    Get Started
                </Link>
            </div>
        </nav>

    
        <main class="pt-6 md:pt-10">
            <!-- Hero Section -->
            <section class="max-w-7xl mx-auto px-6 lg:px-8 pt-16 md:pt-20 pb-20 grid lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-8">

                    <h1 class="text-5xl md:text-6xl font-bold tracking-tight text-slate-900 leading-[1.1]">
                        Protect Your Plants with <br />
                        <span class="text-emerald-600">AI-Powered</span> Diagnosis
                    </h1>

                    <p class="text-lg text-slate-600 leading-relaxed max-w-lg">
                        Upload a plant photo to start a queued AI diagnosis, track the status, and review results with confidence, symptoms, and treatment.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-4 pt-4">
                        <Link href="/login" class="w-full sm:w-auto px-8 py-3.5 text-base font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-all shadow-sm flex items-center justify-center">
                            Try It Now
                        </Link>
                    </div>
                </div>

                <div class="relative w-full aspect-4/3">
                    <div class="absolute inset-0 rounded-3xl overflow-hidden shadow-xl">
                        <img 
                            src="https://images.unsplash.com/photo-1599940824399-b87987ceb72a?q=80&w=2000&auto=format&fit=crop" 
                            alt="Healthy green plant leaves with water droplets" 
                            class="w-full h-full object-cover"
                        />
                    </div>

                    <div class="absolute -bottom-6 left-4 md:bottom-8 md:-left-8 bg-white/95 backdrop-blur px-5 py-4 rounded-2xl shadow-xl flex items-center gap-4 max-w-xs border border-slate-100">
                        <div class="w-10 h-10 rounded-full bg-emerald flex items-center justify-center shrink-0">
                            <Check class="w-5 h-5 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Healthy Leaf</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Confidence: 98.4%
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features" class="bg-[#f8faf9] py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="text-center max-w-2xl mx-auto mb-16">
                        <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Everything You Need</h2>
                        <p class="text-lg text-slate-600">From photo upload to detailed results — AgriAssist AI helps you diagnose issues and act with confidence.</p>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Feature 1 -->
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-6">
                                <Scan class="w-6 h-6 text-emerald-600" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Photo Upload</h3>
                            <p class="text-slate-600">Upload a clear plant photo to start a diagnosis.</p>
                        </div>
                        <!-- Feature 2 -->
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-6">
                                <Sparkles class="w-6 h-6 text-emerald-600" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">AI-Powered</h3>
                            <p class="text-slate-600">AI analysis returns a disease name and confidence score.</p>
                        </div>
                        <!-- Feature 3 -->
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-6">
                                <ShieldCheck class="w-6 h-6 text-emerald-600" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Treatment Guidance</h3>
                            <p class="text-slate-600">Get suggested symptoms and treatment steps from the report.</p>
                        </div>
                        <!-- Feature 4 -->
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-6">
                                <TrendingUp class="w-6 h-6 text-emerald-600" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Diagnosis History</h3>
                            <p class="text-slate-600">Review past diagnoses and open detailed results anytime.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How It Works Section -->
            <section id="how-it-works" class="py-24 bg-white border-t border-slate-100">
                <div class="max-w-7xl mx-auto px-6 lg:px-8 grid lg:grid-cols-2 gap-16 items-center">
                    <div class="relative rounded-3xl overflow-hidden shadow-xl aspect-4/3 w-full">
                        <img 
                            src="https://images.unsplash.com/photo-1501004318641-b39e6451bec6?q=80&w=2000&auto=format&fit=crop" 
                            alt="Hands holding a young plant in soil" 
                            class="w-full h-full object-cover"
                        />
                    </div>
                    
                    <div class="space-y-12">
                        <div>
                            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">How It Works</h2>
                            <p class="text-lg text-slate-600">Three simple steps to diagnose any plant disease — no expertise needed.</p>
                        </div>
                        
                        <div class="space-y-8">
                            <div class="flex gap-6">
                                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                                    <Camera class="w-6 h-6 text-emerald-600" />
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-emerald-600 mb-1 block">01</span>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">Snap a Photo</h3>
                                    <p class="text-slate-600">Take a clear photo of the affected leaf using your phone or upload an existing image.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-6">
                                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                                    <Cpu class="w-6 h-6 text-emerald-600" />
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-emerald-600 mb-1 block">02</span>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">AI Analyzes</h3>
                                    <p class="text-slate-600">The diagnosis runs in the background — you can track the status as it completes.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-6">
                                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                                    <FileText class="w-6 h-6 text-emerald-600" />
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-emerald-600 mb-1 block">03</span>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">Get Your Report</h3>
                                    <p class="text-slate-600">Open a detailed report with disease name, confidence, symptoms, and treatment.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<style>
.animate-bounce-slow {
    animation: bounce 3s infinite;
}
@keyframes bounce {
    0%, 100% { transform: translateY(-5%); }
    50% { transform: translateY(0); }
}
</style>
