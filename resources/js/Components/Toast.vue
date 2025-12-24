<script setup>
import { useToast } from '@/Composables/useToast';

const { toasts, removeToast } = useToast();

const getIcon = (type) => {
    switch (type) {
        case 'success': return 'check';
        case 'error': return 'x';
        case 'warning': return 'alert';
        default: return 'info';
    }
};

const getStyles = (type) => {
    switch (type) {
        case 'success':
            return {
                bg: 'bg-slate-900 border-emerald-500',
                icon: 'bg-emerald-500 text-white',
                text: 'text-emerald-100',
                progress: 'bg-emerald-500'
            };
        case 'error':
            return {
                bg: 'bg-slate-900 border-red-500',
                icon: 'bg-red-500 text-white',
                text: 'text-red-100',
                progress: 'bg-red-500'
            };
        case 'warning':
            return {
                bg: 'bg-slate-900 border-amber-500',
                icon: 'bg-amber-500 text-white',
                text: 'text-amber-100',
                progress: 'bg-amber-500'
            };
        default:
            return {
                bg: 'bg-slate-900 border-cyan-500',
                icon: 'bg-cyan-500 text-white',
                text: 'text-cyan-100',
                progress: 'bg-cyan-500'
            };
    }
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed top-20 right-4 z-[9999] flex flex-col gap-3 max-w-md w-full pointer-events-none">
            <TransitionGroup
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="translate-x-full opacity-0"
                enter-to-class="translate-x-0 opacity-100"
                leave-active-class="transition-all duration-300 ease-in"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-full opacity-0"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'pointer-events-auto border-l-4 rounded-xl shadow-2xl overflow-hidden shadow-black/50',
                        getStyles(toast.type).bg
                    ]"
                >
                    <div class="p-4 flex items-start gap-3">
                        <!-- Icon -->
                        <div :class="['flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center', getStyles(toast.type).icon]">
                            <!-- Success -->
                            <svg v-if="getIcon(toast.type) === 'check'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <!-- Error -->
                            <svg v-else-if="getIcon(toast.type) === 'x'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <!-- Warning -->
                            <svg v-else-if="getIcon(toast.type) === 'alert'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <!-- Info -->
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        
                        <!-- Message -->
                        <div class="flex-1 min-w-0">
                            <p :class="['text-sm font-medium', getStyles(toast.type).text]">
                                {{ toast.message }}
                            </p>
                        </div>
                        
                        <!-- Close Button -->
                        <button 
                            @click="removeToast(toast.id)"
                            class="flex-shrink-0 text-slate-400 hover:text-white transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="h-1 w-full bg-slate-700/50">
                        <div 
                            :class="['h-full animate-shrink', getStyles(toast.type).progress]"
                        ></div>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
@keyframes shrink {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}

.animate-shrink {
    animation: shrink 4s linear forwards;
}
</style>

