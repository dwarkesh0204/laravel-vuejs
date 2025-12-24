<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    symbol: {
        type: String,
        default: 'BTC'
    },
    refreshTrigger: {
        type: Number,
        default: 0
    }
});

const buyOrders = ref([]);
const sellOrders = ref([]);
const loading = ref(false);

const fetchOrderBook = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/orderbook/${props.symbol}`);
        if (response.data.success) {
            buyOrders.value = response.data.data.buy_orders;
            sellOrders.value = response.data.data.sell_orders;
        }
    } catch (err) {
        console.error('Failed to fetch order book:', err);
    } finally {
        loading.value = false;
    }
};

watch(() => props.symbol, fetchOrderBook);
watch(() => props.refreshTrigger, fetchOrderBook);

onMounted(fetchOrderBook);

// Auto-refresh every 10 seconds
setInterval(fetchOrderBook, 10000);
</script>

<template>
    <div class="bg-slate-900 rounded-2xl p-6 border border-slate-700/50 shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Order Book
            </h2>
            <span class="text-sm text-slate-400 bg-slate-800 px-3 py-1 rounded-full">{{ symbol }}/USD</span>
        </div>
        
        <div v-if="loading && !buyOrders.length && !sellOrders.length" class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
        <div v-else class="grid grid-cols-2 gap-4">
            <!-- Buy Orders (Bids) -->
            <div>
                <div class="text-sm font-medium text-emerald-400 mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                    Buy Orders
                </div>
                <div class="space-y-1 max-h-64 overflow-y-auto custom-scrollbar">
                    <div v-if="buyOrders.length === 0" class="text-center py-6 text-slate-500 text-sm">
                        No buy orders
                    </div>
                    <div 
                        v-for="order in buyOrders" 
                        :key="order.id"
                        class="flex justify-between items-center py-2 px-3 bg-emerald-500/10 rounded-lg border border-emerald-500/20 hover:border-emerald-500/40 transition-colors"
                    >
                        <span class="font-mono text-emerald-400 text-sm">${{ parseFloat(order.price).toFixed(2) }}</span>
                        <span class="font-mono text-slate-300 text-sm">{{ parseFloat(order.amount).toFixed(4) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Sell Orders (Asks) -->
            <div>
                <div class="text-sm font-medium text-rose-400 mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 bg-rose-400 rounded-full"></span>
                    Sell Orders
                </div>
                <div class="space-y-1 max-h-64 overflow-y-auto custom-scrollbar">
                    <div v-if="sellOrders.length === 0" class="text-center py-6 text-slate-500 text-sm">
                        No sell orders
                    </div>
                    <div 
                        v-for="order in sellOrders" 
                        :key="order.id"
                        class="flex justify-between items-center py-2 px-3 bg-rose-500/10 rounded-lg border border-rose-500/20 hover:border-rose-500/40 transition-colors"
                    >
                        <span class="font-mono text-rose-400 text-sm">${{ parseFloat(order.price).toFixed(2) }}</span>
                        <span class="font-mono text-slate-300 text-sm">{{ parseFloat(order.amount).toFixed(4) }}</span>
                    </div>
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


