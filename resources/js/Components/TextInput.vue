<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full rounded-xl border border-slate-600 bg-slate-900/50 px-4 py-2.5 text-white placeholder-slate-500 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 focus:ring-1 focus:outline-none transition-colors"
        v-model="model"
        ref="input"
    />
</template>
