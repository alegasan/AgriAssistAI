<script lang="ts" setup>
import { Head } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useForm } from '@inertiajs/vue3'


const form = useForm()

const logout = () => {
    form.post(route('logout'))
}
</script>


<template>
    <Head title="Admin Dashboard" />

    <div class="p-4">
        <h1 class="text-2xl font-bold">Admin Dashboard</h1>
        <p>Welcome to the admin dashboard. Here you can manage users, view analytics, and configure settings.</p>

        <form @submit.prevent="logout">
            <button type="submit" class="mt-4 rounded bg-red-500 px-4 py-2 text-white hover:bg-red-600">
                Logout
            </button>
        </form>
    </div>


</template>