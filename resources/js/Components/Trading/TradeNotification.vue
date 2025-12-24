<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const notifications = ref([]);
let notificationId = 0;

const addNotification = (trade) => {
    const id = ++notificationId;
    notifications.value.push({
        id,
        trade,
        show: true
    });
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        removeNotification(id);
    }, 5000);
};

const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id);
    if (index !== -1) {
        notifications.value[index].show = false;
        setTimeout(() => {
            notifications.value = notifications.value.filter(n => n.id !== id);
        }, 300);
    }
};

defineExpose({ addNotification });
</script>

<template>
    <div class="fixed top-4 right-4 z-50 space-y-3">
        <TransitionGroup name="notification">
            <div 
                v-for="notification in notifications"
                :key="notification.id"
                :class="[
                    'bg-slate-900 border border-emerald-500/50 rounded-xl p-4 shadow-2xl shadow-emerald-500/20 max-w-sm',
                    'transform transition-all duration-300',
                    notification.show ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'
                ]"
            >
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-emerald-500/20 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-emerald-400 font-semibold text-sm">Order Matched!</div>
                        <div class="text-white text-sm mt-1">
                            {{ notification.trade.symbol }}: {{ parseFloat(notification.trade.amount).toFixed(4) }} @ ${{ parseFloat(notification.trade.price).toFixed(2) }}
                        </div>
                        <div class="text-slate-400 text-xs mt-1">
                            Total: ${{ parseFloat(notification.trade.total_value).toFixed(2) }}
                        </div>
                    </div>
                    <button 
                        @click="removeNotification(notification.id)"
                        class="text-slate-500 hover:text-slate-300 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.notification-enter-active,
.notification-leave-active {
    transition: all 0.3s ease;
}
.notification-enter-from {
    opacity: 0;
    transform: translateX(100%);
}
.notification-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>


