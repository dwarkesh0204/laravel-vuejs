<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    refreshTrigger: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['order-cancelled']);
const toast = useToast();

const orders = ref([]);
const loading = ref(false);

const fetchOrders = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/orders');
        if (response.data.success) {
            orders.value = response.data.data.orders;
        }
    } catch (err) {
        console.error('Failed to fetch orders:', err);
        toast.error('Failed to fetch orders');
    } finally {
        loading.value = false;
    }
};

const cancelOrder = async (orderId) => {
    try {
        const response = await axios.post(`/api/orders/${orderId}/cancel`);
        if (response.data.success) {
            toast.success('Order cancelled successfully');
            await fetchOrders();
            emit('order-cancelled', response.data.data.order);
        }
    } catch (err) {
        console.error('Failed to cancel order:', err);
        toast.error(err.response?.data?.message || 'Failed to cancel order');
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 1: return 'text-blue-400 bg-blue-500/20 border-blue-500/30';
        case 2: return 'text-emerald-400 bg-emerald-500/20 border-emerald-500/30';
        case 3: return 'text-slate-400 bg-slate-500/20 border-slate-500/30';
        default: return 'text-slate-400 bg-slate-500/20 border-slate-500/30';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 1: return 'Open';
        case 2: return 'Filled';
        case 3: return 'Cancelled';
        default: return 'Unknown';
    }
};

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleString();
};

watch(() => props.refreshTrigger, fetchOrders);

onMounted(fetchOrders);
</script>

<template>
    <div class="bg-slate-900 rounded-2xl p-6 border border-slate-700/50 shadow-xl">
        <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            My Orders
        </h2>
        
        <div v-if="loading && orders.length === 0" class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
        <div v-else-if="orders.length === 0" class="text-center py-12 text-slate-500">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-lg">No orders yet</p>
            <p class="text-sm mt-1">Place your first order to get started!</p>
        </div>
        
        <div v-else class="space-y-3 max-h-96 overflow-y-auto custom-scrollbar">
            <div 
                v-for="order in orders" 
                :key="order.id"
                class="bg-slate-800/50 rounded-xl p-4 border border-slate-700/50 hover:border-slate-600 transition-all"
            >
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span :class="[
                            'px-3 py-1 rounded-full text-xs font-bold uppercase',
                            order.side === 'buy' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'
                        ]">
                            {{ order.side }}
                        </span>
                        <span class="text-white font-semibold">{{ order.symbol }}/USD</span>
                    </div>
                    <span :class="['px-3 py-1 rounded-full text-xs font-medium border', getStatusColor(order.status)]">
                        {{ getStatusLabel(order.status) }}
                    </span>
                </div>
                
                <div class="grid grid-cols-3 gap-4 text-sm mb-3">
                    <div>
                        <div class="text-slate-500 text-xs">Price</div>
                        <div class="text-white font-mono">${{ parseFloat(order.price).toFixed(2) }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Amount</div>
                        <div class="text-white font-mono">{{ parseFloat(order.amount).toFixed(4) }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Filled</div>
                        <div class="text-white font-mono">{{ parseFloat(order.filled_amount).toFixed(4) }}</div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">{{ formatDate(order.created_at) }}</span>
                    <button 
                        v-if="order.status === 1"
                        @click="cancelOrder(order.id)"
                        class="text-rose-400 hover:text-rose-300 hover:underline transition-colors"
                    >
                        Cancel Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 2px;
}
</style>

