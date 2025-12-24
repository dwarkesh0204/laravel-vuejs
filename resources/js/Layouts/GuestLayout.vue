<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gradient-to-br from-slate-800 via-slate-900 to-slate-950 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-cyan-400" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-slate-800/80 backdrop-blur-sm border border-slate-700/50 px-6 py-8 shadow-xl sm:max-w-md sm:rounded-2xl"
        >
            <slot />
        </div>
    </div>
</template>
