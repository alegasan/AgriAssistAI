<script setup lang="ts">
import { Sun, Moon, Cloud, CloudRain, CloudSnow, CloudLightning, CloudDrizzle, CloudFog } from 'lucide-vue-next'
import { ref, onMounted, computed } from 'vue'

const loading = ref(true)
const weather = ref<any | null>(null)

const iconMap: Record<string, any> = {
  '01d': Sun,
  '01n': Moon,
  '02d': Cloud,
  '02n': Cloud,
  '03d': Cloud,
  '03n': Cloud,
  '04d': Cloud,
  '04n': Cloud,
  '09d': CloudDrizzle,
  '09n': CloudDrizzle,
  '10d': CloudRain,
  '10n': CloudRain,
  '11d': CloudLightning,
  '11n': CloudLightning,
  '13d': CloudSnow,
  '13n': CloudSnow,
  '50d': CloudFog,
  '50n': CloudFog,
}

const iconComponent = computed(() => {
  if (!weather.value || !weather.value.icon) return Sun
  return iconMap[weather.value.icon] ?? Sun
})

async function fetchWeather() {
  try {
    const res = await fetch('/client/weather/current')
    if (!res.ok) return
    const json = await res.json()
    if (json.success) weather.value = json.data
  } catch {
  } finally {
    loading.value = false
  }
}
onMounted(() => void fetchWeather())
</script>

<template>
  <div v-if="loading" class="flex items-center gap-3 rounded-2xl bg-white/90 px-5 py-4 text-slate-900 shadow-sm">
    <div class="h-4 w-24 bg-slate-200 animate-pulse"></div>
  </div>

  <div v-else-if="weather" class="flex items-center gap-3 rounded-2xl bg-white/90 px-5 py-4 text-slate-900 shadow-sm">
    <div class="grid h-12 w-12 place-items-center rounded-full bg-amber-100 text-amber-500">
      <component :is="iconComponent" class="h-6 w-6" aria-hidden="true" />
    </div>
    <div>
      <p class="text-sm font-semibold">{{ weather.condition }} · {{ weather.temperature }}°C</p>
      <p class="text-xs text-slate-500">{{ weather.description }} · {{ weather.humidity }}% humidity</p>
    </div>
  </div>

  <div v-else class="flex items-center gap-3 rounded-2xl bg-white/90 px-5 py-4 text-slate-900 shadow-sm">
    <div>No weather data</div>
  </div>
</template>
