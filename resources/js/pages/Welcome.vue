<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

</script>

<template>
    <Head title="Welcome" />
    <div class="relative min-h-screen bg-[#f8faf9] font-sans selection:bg-emerald-500 selection:text-white">
      
        <nav class="flex items-center justify-between px-6 lg:px-8 py-4 bg-white/50 backdrop-blur-md sticky top-0 z-50">
            <div class="flex items-center gap-2">
                <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xl font-bold text-slate-900">PlantGuard AI</span>
            </div>
            
            <div class="flex items-center gap-8">
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-slate-600 hover:text-emerald-700 font-medium transition-colors">Features</a>
                    <a href="#how-it-works" class="text-slate-600 hover:text-emerald-700 font-medium transition-colors">How It Works</a>
                    <a href="#testimonials" class="text-slate-600 hover:text-emerald-700 font-medium transition-colors">Testimonials</a>
                </div>

                <div class="flex items-center gap-4">
                    <Link href="/login" class="px-5 py-2.5 text-sm font-semibold text-white bg-[#228b22] rounded-full hover:bg-emerald-700 transition-colors shadow-sm ring-1 ring-[#228b22] ring-offset-2">
                        Get Started
                    </Link>
                </div>
            </div>
        </nav>

    
        <main>
            <!-- Hero Section -->
            <section class="max-w-7xl mx-auto px-6 lg:px-8 pt-24 pb-24 grid lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 text-sm font-medium text-slate-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        AI-Powered Plant Diagnosis
                    </div>

                    <h1 class="text-5xl md:text-6xl font-bold tracking-tight text-slate-900 leading-[1.1]">
                        Protect Your Crops with <br />
                        <span class="text-emerald-600">Instant AI</span> Diagnosis
                    </h1>

                    <p class="text-lg text-slate-600 leading-relaxed max-w-lg">
                        Snap a photo of any plant leaf and get an instant disease diagnosis, treatment plan, and prevention tips — powered by cutting-edge AI.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-4 pt-4">
                        <Link href="/scan" class="w-full sm:w-auto px-8 py-3.5 text-base font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-all shadow-sm flex items-center justify-center">
                            Try Free Scan
                        </Link>
                        <button class="w-full sm:w-auto px-8 py-3.5 text-base font-semibold text-emerald-700 bg-white rounded-lg border-2 border-emerald-600 hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center">
                            Watch Demo
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-6 gap-y-3 pt-6 text-sm font-medium text-slate-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            98% accuracy
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            50+ diseases
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Free tier
                        </div>
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
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
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
                                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Instant Scan</h3>
                            <p class="text-slate-600">Point your camera at any leaf and get results in under 3 seconds.</p>
                        </div>
                        <!-- Feature 2 -->
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-6">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">AI-Powered</h3>
                            <p class="text-slate-600">Trained on millions of samples with 98% diagnostic accuracy.</p>
                        </div>
                        <!-- Feature 3 -->
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-6">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Treatment Plans</h3>
                            <p class="text-slate-600">Get personalized treatment and prevention recommendations.</p>
                        </div>
                        <!-- Feature 4 -->
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-6">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
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
                            src="https://images.unsplash.com/photo-1592079927431-3f8edd0cb267?q=80&w=2000&auto=format&fit=crop" 
                            alt="Person taking a photo of a sick plant leaf with smartphone" 
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
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-emerald-600 mb-1 block">01</span>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">Snap a Photo</h3>
                                    <p class="text-slate-600">Take a clear photo of the affected leaf using your phone or upload an existing image.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-6">
                                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-emerald-600 mb-1 block">02</span>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">AI Analyzes</h3>
                                    <p class="text-slate-600">Our AI model processes the image and identifies the disease within seconds.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-6">
                                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
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
