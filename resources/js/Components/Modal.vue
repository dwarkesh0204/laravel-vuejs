<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);
const showSlot = ref(props.show);

watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            document.body.style.overflow = 'hidden';
            showSlot.value = true;
        } else {
            document.body.style.overflow = '';
            setTimeout(() => {
                showSlot.value = false;
            }, 200);
        }
    },
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault();
        if (props.show) {
            close();
        }
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = '';
});

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <Teleport to="body">
        <div v-show="show" class="fixed inset-0 z-[9998] overflow-y-auto">
            <!-- Backdrop -->
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-show="show"
                    class="fixed inset-0 bg-black/70 backdrop-blur-sm"
                    @click="close"
                />
            </Transition>

            <!-- Modal Content -->
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <Transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-show="show"
                        class="relative w-full transform overflow-hidden rounded-2xl bg-slate-800 border border-slate-700/50 shadow-2xl transition-all"
                        :class="maxWidthClass"
                    >
                        <slot v-if="showSlot" />
                    </div>
                </Transition>
            </div>
        </div>
    </Teleport>
</template>
