<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useToast } from "@/Composables/useToast";

const page = usePage();
const toast = useToast();

// Track the last processed flash to avoid duplicates
const lastProcessedFlash = ref(null);

// Create a unique signature for current flash messages
const getFlashSignature = (flash) => {
    if (!flash) return null;
    const parts = [];
    if (flash.success) parts.push(`s:${flash.success}`);
    if (flash.error) parts.push(`e:${flash.error}`);
    if (flash.warning) parts.push(`w:${flash.warning}`);
    if (flash.info) parts.push(`i:${flash.info}`);
    return parts.length > 0 ? parts.join("|") : null;
};

// Process flash messages
const processFlash = () => {
    const flash = page.props.flash;
    if (!flash) return;

    const signature = getFlashSignature(flash);

    // Skip if we've already processed this exact flash
    if (signature === null || signature === lastProcessedFlash.value) {
        return;
    }

    // Mark as processed
    lastProcessedFlash.value = signature;

    // Show the toasts
    if (flash.success) toast.success(flash.success);
    if (flash.error) toast.error(flash.error);
    if (flash.warning) toast.warning(flash.warning);
    if (flash.info) toast.info(flash.info);
};

// Listen for Inertia success events (new page loads with fresh data)
const removeSuccessListener = router.on("success", () => {
    // Reset on new successful navigation to allow fresh flash messages
    lastProcessedFlash.value = null;
    // Use nextTick to ensure props are updated
    setTimeout(() => processFlash(), 0);
});

// Process on initial mount
onMounted(() => {
    processFlash();
});

// Cleanup
onUnmounted(() => {
    removeSuccessListener();
});
</script>

<template>
    <!-- This component doesn't render anything, it just handles flash messages -->
</template>
