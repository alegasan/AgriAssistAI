<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Sun, Moon, Cloud, CloudRain, CloudSnow, CloudLightning, CloudDrizzle, CloudFog } from 'lucide-vue-next'

const loading = ref(true)
const forecast = ref<Array<any>>([])
const limitedForecast = computed(() => forecast.value.slice(0, 5))

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

function iconFor(code: string) {
  return iconMap[code] ?? Sun
}

async function fetchForecast() {
  try {
    const res = await fetch('/client/weather/forecast')
    if (!res.ok) {
      const errorBody = await res.text()
      console.error('Weather forecast request failed', res.status, errorBody)
      return
    }

    const json = await res.json()
    if (json?.success && Array.isArray(json.data)) {
      forecast.value = json.data
    } else {
      console.error('Weather forecast response invalid', json)
    }
  } catch (e) {
    console.error('Weather forecast request error', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => void fetchForecast())
</script>

<template>
  <div class="mt-4">
    <div v-if="loading" class="grid grid-cols-3 gap-2">
      <div class="h-20 w-full rounded-lg bg-slate-200 animate-pulse"></div>
      <div class="h-20 w-full rounded-lg bg-slate-200 animate-pulse"></div>
      <div class="h-20 w-full rounded-lg bg-slate-200 animate-pulse"></div>
    </div>

    <div v-else-if="limitedForecast.length" class="flex gap-3 overflow-x-auto pb-1">
      <div v-for="day in limitedForecast" :key="day.date" class="min-w-[120px] rounded-xl bg-white/95 px-3 py-3 text-center shadow-sm">
        <component :is="iconFor(day.icon)" class="mx-auto h-5 w-5 text-amber-500" />
        <div class="mt-2 text-xs font-semibold text-slate-800">{{ day.date }}</div>
        <div class="text-[11px] text-slate-500">{{ day.condition }}</div>
        <div class="mt-1 text-xs text-slate-700">{{ day.temp_min }}° / {{ day.temp_max }}°</div>
      </div>
    </div>

    <div v-else class="text-sm text-slate-500">No forecast available.</div>
  </div>
</template>
