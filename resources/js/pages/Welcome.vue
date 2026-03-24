<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Camera,
    Check,
    Cpu,
    FileText,
    Leaf,
    Scan,
    ShieldCheck,
    Sparkles,
    TrendingUp,
} from 'lucide-vue-next';

</script>

<template>
    <Head title="Welcome" />
    <div class="relative min-h-screen bg-[#f8faf9] font-sans selection:bg-emerald-500 selection:text-white">
      
        <nav class="flex items-center justify-between px-6 lg:px-8 py-4 bg-white/50 backdrop-blur-md sticky top-0 z-50">
            <div class="flex items-center gap-2">
                <Leaf class="w-8 h-8 text-emerald-600" />
                <span class="text-xl font-bold text-slate-900">AgriAssist AI</span>
            </div>
            
            <div class="flex items-center gap-8">
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-slate-600 hover:text-emerald-700 font-medium transition-colors">Features</a>
                    <a href="#how-it-works" class="text-slate-600 hover:text-emerald-700 font-medium transition-colors">How It Works</a>
                </div>

                <div class="flex items-center gap-4">
                    <Link href="/login" class="px-5 py-2.5 text-sm font-semibold text-emerald-700 bg-emerald-100/70 rounded-full hover:bg-emerald-100 transition-colors shadow-sm ring-1 ring-emerald-200 ring-offset-2">
                        Get Started
                    </Link>
                </div>
            </div>
        </nav>

    
        <main class="pt-6 md:pt-10">
            <!-- Hero Section -->
            <section class="max-w-7xl mx-auto px-6 lg:px-8 pt-16 md:pt-20 pb-20 grid lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-8">

                    <h1 class="text-5xl md:text-6xl font-bold tracking-tight text-slate-900 leading-[1.1]">
                        Protect Your Crops with <br />
                        <span class="text-emerald-600">Instant AI</span> Diagnosis
                    </h1>

                    <p class="text-lg text-slate-600 leading-relaxed max-w-lg">
                        Snap a photo of any plant leaf and get an instant disease diagnosis, treatment plan, and prevention tips — powered by cutting-edge AI.
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

                    <div class="absolute -bottom-6 -left-6 md:bottom-8 md:-left-8 bg-white/95 backdrop-blur px-5 py-4 rounded-2xl shadow-xl flex items-center gap-4 max-w-xs border border-slate-100">
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
                        <p class="text-lg text-slate-600">From quick scans to long-term monitoring — PlantGuard AI gives you the tools to keep every plant healthy.</p>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Feature 1 -->
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-6">
                                <Scan class="w-6 h-6 text-emerald-600" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Instant Scan</h3>
                            <p class="text-slate-600">Point your camera at any leaf and get results.</p>
                        </div>
                        <!-- Feature 2 -->
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-6">
                                <Sparkles class="w-6 h-6 text-emerald-600" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">AI-Powered</h3>
                            <p class="text-slate-600">Trained on millions of samples with 98% diagnostic accuracy.</p>
                        </div>
                        <!-- Feature 3 -->
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-6">
                                <ShieldCheck class="w-6 h-6 text-emerald-600" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Treatment Plans</h3>
                            <p class="text-slate-600">Get personalized treatment and prevention recommendations.</p>
                        </div>
                        <!-- Feature 4 -->
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-6">
                                <TrendingUp class="w-6 h-6 text-emerald-600" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Crop Monitoring</h3>
                            <p class="text-slate-600">Track your plants' health over time with detailed analytics.</p>
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
                                    <p class="text-slate-600">Our AI model processes the image and identifies the disease within seconds.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-6">
                                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                                    <FileText class="w-6 h-6 text-emerald-600" />
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-emerald-600 mb-1 block">03</span>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">Get Your Report</h3>
                                    <p class="text-slate-600">Receive a detailed diagnosis with treatment options and prevention tips.</p>
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
