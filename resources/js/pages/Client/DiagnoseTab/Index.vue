<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Camera, ChevronDown, Image as ImageIcon, Upload } from 'lucide-vue-next'
import ClientDashboard from '@/layouts/Client/ClientDashboard.vue'

type DiagnosisResult = {
    id: number
    plant_name: string | null
    disease_name: string | null
    confidence_score: string | number | null
    symptoms: string | null
    treatment: string | null
    image_url: string
}

const page = usePage()
const previewUrl = ref<string | null>(null)
const fileInputRef = ref<HTMLInputElement | null>(null)
const isLoading = ref(false)
const analysisProgress = ref(0)
const progressInterval = ref<number | null>(null)
const hideLoadingTimeout = ref<number | null>(null)
const submitHasErrors = ref(false)

const form = useForm<{
    image: File | null
    plant_name: string
}>({
    image: null,
    plant_name: '',
})

const flash = computed(() => {
    return (page.props.flash ?? {}) as {
        success?: string
        error?: string
        diagnosis?: DiagnosisResult
    }
})

const diagnosis = ref<DiagnosisResult | null>(null)
const diagnosisError = computed(() => (form.errors as Record<string, string | undefined>).diagnosis)

watch(
    () => flash.value.diagnosis,
    (incomingDiagnosis) => {
        if (incomingDiagnosis) {
            diagnosis.value = incomingDiagnosis
        }
    },
    { immediate: true },
)

const clearProgressInterval = (): void => {
    if (progressInterval.value !== null) {
        window.clearInterval(progressInterval.value)
        progressInterval.value = null
    }
}

const clearHideLoadingTimeout = (): void => {
    if (hideLoadingTimeout.value !== null) {
        window.clearTimeout(hideLoadingTimeout.value)
        hideLoadingTimeout.value = null
    }
}

const startLoadingProgress = (): void => {
    clearProgressInterval()
    clearHideLoadingTimeout()

    isLoading.value = true
    analysisProgress.value = 0

    progressInterval.value = window.setInterval(() => {
        if (analysisProgress.value < 95) {
            analysisProgress.value += Math.max(1, Math.round((95 - analysisProgress.value) / 8))
        }
    }, 280)
}

const finishLoadingProgress = (): void => {
    analysisProgress.value = 100
    clearProgressInterval()
    clearHideLoadingTimeout()

    hideLoadingTimeout.value = window.setTimeout(() => {
        isLoading.value = false
        analysisProgress.value = 0
    }, 350)
}

const cancelLoadingProgress = (): void => {
    clearProgressInterval()
    clearHideLoadingTimeout()
    isLoading.value = false
    analysisProgress.value = 0
}

const setImagePreview = (file: File | null): void => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
        previewUrl.value = null
    }

    if (!file) {
        return
    }

    previewUrl.value = URL.createObjectURL(file)
}

const onImageChange = (event: Event): void => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0] ?? null

    form.image = file
    setImagePreview(file)
}

const triggerFilePicker = (): void => {
    fileInputRef.value?.click()
}

const onDrop = (event: DragEvent): void => {
    event.preventDefault()
    const file = event.dataTransfer?.files?.[0] ?? null

    if (file && !file.type.startsWith('image/')) {
        return
    }

    form.image = file
    setImagePreview(file)
}
const submit = (): void => {
    if (form.processing || isLoading.value) {
        return
    }

    if (!form.image) {
        form.setError('image', 'Please select an image before analyzing.')

        return
    }

    form.clearErrors()
    submitHasErrors.value = false

    form.post(route('client.diagnose.store'), {
        forceFormData: true,
        preserveScroll: true,
        onStart: () => {
            startLoadingProgress()
        },
        onProgress: (progress) => {
            if (typeof progress?.percentage === 'number') {
                analysisProgress.value = Math.min(Math.max(progress.percentage, analysisProgress.value), 98)
            }
        },
        onError: () => {
            submitHasErrors.value = true
            cancelLoadingProgress()
        },
        onSuccess: () => {
            form.image = null
            setImagePreview(null)
        },
        onFinish: () => {
            if (!submitHasErrors.value) {
                finishLoadingProgress()
            }
        },
    })
}

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
    }

    clearProgressInterval()
    clearHideLoadingTimeout()
})
</script>

<template>
    <ClientDashboard :title="'Diagnose'" :description="'Upload images of your plants to get a diagnosis.'">
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isLoading" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 px-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-3xl border border-emerald-200 bg-white p-6 shadow-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">PlantGuard AI</p>
                    <h3 class="mt-2 text-2xl font-extrabold text-slate-900">Analyzing your crop image</h3>
                    <p class="mt-2 text-sm text-slate-600">Please wait while we process and generate diagnosis details.</p>

                    <div class="mt-6">
                        <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                            <span>Processing</span>
                            <span>{{ analysisProgress }}%</span>
                        </div>
                        <div class="mt-2 h-3 w-full overflow-hidden rounded-full bg-slate-200">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-lime-400 transition-all duration-300"
                                :style="{ width: `${analysisProgress}%` }"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <div class="mx-auto mt-4 grid w-full max-w-6xl gap-6 px-4 sm:px-6 lg:px-8 md:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
            <section class="rounded-[28px] border border-emerald-100/80 bg-slate-50 p-6 shadow-[0_18px_40px_-28px_rgba(5,150,105,0.45)]">
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">AI Diagnosis</h2>
                <p class="mt-1 text-sm text-slate-600">Upload a photo to analyze your crops</p>

                <form class="mt-5 space-y-5" @submit.prevent="submit">
                    <input
                        id="image"
                        ref="fileInputRef"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="onImageChange"
                    >

                    <button
                        v-if="!previewUrl"
                        type="button"
                        class="group flex w-full flex-col items-center justify-center rounded-3xl border border-dashed border-emerald-300 bg-white px-4 py-10 text-center transition hover:border-emerald-400 hover:bg-emerald-50/30"
                        @click="triggerFilePicker"
                        @dragover.prevent
                        @drop="onDrop"
                    >
                        <span class="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <Camera class="h-7 w-7" />
                        </span>
                        <span class="text-base font-semibold text-slate-900">Click to upload a photo</span>
                        <span class="mt-1 text-sm text-amber-700/80">or drag and drop</span>
                    </button>

                    <button
                        v-else
                        type="button"
                        class="w-full overflow-hidden rounded-3xl border border-emerald-200"
                        @click="triggerFilePicker"
                    >
                        <img :src="previewUrl" alt="Preview" class="h-64 w-full object-cover">
                    </button>

                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:text-emerald-700"
                            @click="triggerFilePicker"
                        >
                            <Camera class="h-4 w-4" />
                            Camera
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:text-emerald-700"
                            @click="triggerFilePicker"
                        >
                            <ImageIcon class="h-4 w-4" />
                            Gallery
                        </button>
                    </div>

                    <div class="space-y-2">
                        <label for="plant_name" class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                            <ImageIcon class="h-4 w-4 text-slate-500" />
                            Crop Type
                        </label>
                        <div class="relative">
                            <input
                                id="plant_name"
                                v-model="form.plant_name"
                                type="text"
                                placeholder="Select crop type..."
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                            <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
                        </div>
                        <p v-if="form.errors.plant_name" class="text-xs text-red-600">{{ form.errors.plant_name }}</p>
                    </div>

                    <div v-if="form.errors.image" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                        {{ form.errors.image }}
                    </div>

                    <div v-if="diagnosisError" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                        {{ diagnosisError }}
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || isLoading || !form.image"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-300 px-4 py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_-18px_rgba(5,150,105,0.9)] transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <Upload class="h-4 w-4" />
                        {{ form.processing ? 'Analyzing Image...' : 'Analyze Crop' }}
                    </button>
                </form>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold tracking-tight text-slate-900">Latest Result</h2>
                <p class="mt-1 text-sm text-slate-600">Your most recent diagnosis appears here after upload.</p>

                <p v-if="flash.success" class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                    {{ flash.success }}
                </p>

                <p v-if="flash.error" class="mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                    {{ flash.error }}
                </p>

                <div v-if="diagnosis" class="mt-4 space-y-4">
                    <img
                        :src="diagnosis.image_url"
                        alt="Diagnosed plant"
                        class="h-56 w-full rounded-xl border border-slate-200 object-cover"
                    >

                    <div class="grid gap-3 text-sm sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 p-3">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Plant</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ diagnosis.plant_name || 'Not detected' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Disease</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ diagnosis.disease_name || 'No clear disease detected' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 sm:col-span-2">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Confidence</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ diagnosis.confidence_score != null ? `${diagnosis.confidence_score}%` : 'N/A' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 sm:col-span-2">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Symptoms</p>
                            <p class="mt-1 text-slate-700">{{ diagnosis.symptoms || 'No symptom summary provided.' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 sm:col-span-2">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Treatment</p>
                            <p class="mt-1 text-slate-700">{{ diagnosis.treatment || 'No treatment recommendation provided.' }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-600">
                    No diagnosis yet. Upload an image to generate your first analysis.
                </div>
            </section>
        </div>
    </ClientDashboard>
</template>
