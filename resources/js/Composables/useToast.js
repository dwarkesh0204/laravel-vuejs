import { ref } from 'vue';

const toasts = ref([]);
let toastId = 0;

export function useToast() {
    const addToast = (message, type = 'info', duration = 4000) => {
        const id = ++toastId;
        toasts.value.push({
            id,
            message,
            type,
            visible: true
        });

        // Auto remove after duration
        setTimeout(() => {
            removeToast(id);
        }, duration);

        return id;
    };

    const removeToast = (id) => {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index > -1) {
            toasts.value[index].visible = false;
            // Remove from array after animation
            setTimeout(() => {
                toasts.value = toasts.value.filter(t => t.id !== id);
            }, 300);
        }
    };

    const success = (message, duration) => addToast(message, 'success', duration);
    const error = (message, duration) => addToast(message, 'error', duration);
    const info = (message, duration) => addToast(message, 'info', duration);
    const warning = (message, duration) => addToast(message, 'warning', duration);

    return {
        toasts,
        addToast,
        removeToast,
        success,
        error,
        info,
        warning
    };
}

