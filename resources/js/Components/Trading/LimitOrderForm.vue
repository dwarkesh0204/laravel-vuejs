<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    symbols: {
        type: Array,
        default: () => ['BTC', 'ETH', 'SOL', 'ADA', 'DOT']
    }
});

const emit = defineEmits(['order-placed', 'error']);
const toast = useToast();

const form = ref({
    symbol: 'BTC',
    side: 'buy',
    price: '',
    amount: ''
});

const loading = ref(false);

// Format number to avoid scientific notation
const formatNumber = (value) => {
    if (!value || value === '') return '';
    const num = parseFloat(value);
    if (isNaN(num)) return '';
    // Convert to fixed decimal string to avoid scientific notation
    return num.toFixed(8).replace(/\.?0+$/, '');
};

// Handle input to prevent scientific notation
const handlePriceInput = (event) => {
    let value = event.target.value;
    // Allow only valid decimal input
    if (value === '' || /^[0-9]*\.?[0-9]*$/.test(value)) {
        form.value.price = value;
    }
};

const handleAmountInput = (event) => {
    let value = event.target.value;
    // Allow only valid decimal input
    if (value === '' || /^[0-9]*\.?[0-9]*$/.test(value)) {
        form.value.amount = value;
    }
};

const totalCost = computed(() => {
    if (!form.value.price || !form.value.amount) return '0.00';
    const total = parseFloat(form.value.price) * parseFloat(form.value.amount);
    if (isNaN(total)) return '0.00';
    // Format without scientific notation
    return total.toFixed(8).replace(/\.?0+$/, '');
});

const isValid = computed(() => {
    return form.value.symbol && 
           form.value.side && 
           parseFloat(form.value.price) > 0 && 
           parseFloat(form.value.amount) > 0;
});

const submitOrder = async () => {
    if (!isValid.value || loading.value) return;
    
    loading.value = true;
    
    try {
        const response = await axios.post('/api/orders', {
            symbol: form.value.symbol,
            side: form.value.side,
            price: form.value.price,
            amount: form.value.amount
        });
        
        if (response.data.success) {
            const order = response.data.data.order;
            const statusLabel = order.status_label || (order.status === 2 ? 'Filled' : 'Open');
            
            if (order.status === 2) {
                toast.success(`🎉 Order matched and filled! ${form.value.amount} ${form.value.symbol} @ $${form.value.price}`);
            } else {
                toast.success(`✓ ${form.value.side.toUpperCase()} order placed for ${form.value.amount} ${form.value.symbol} @ $${form.value.price}`);
            }
            
            emit('order-placed', order);
            
            // Reset form
            form.value.price = '';
            form.value.amount = '';
        }
    } catch (err) {
        const message = err.response?.data?.message || 'Failed to place order';
        toast.error(message);
        emit('error', message);
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="bg-slate-900 rounded-2xl p-6 border border-slate-700/50 shadow-xl">
        <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Place Limit Order
        </h2>
        
        <form @submit.prevent="submitOrder" class="space-y-5">
            <!-- Symbol Select -->
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">Trading Pair</label>
                <select 
                    v-model="form.symbol"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                >
                    <option v-for="symbol in symbols" :key="symbol" :value="symbol">
                        {{ symbol }}/USD
                    </option>
                </select>
            </div>
            
            <!-- Side Toggle -->
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">Order Side</label>
                <div class="grid grid-cols-2 gap-2">
                    <button 
                        type="button"
                        @click="form.side = 'buy'"
                        :class="[
                            'py-3 px-4 rounded-xl font-semibold transition-all duration-200',
                            form.side === 'buy' 
                                ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' 
                                : 'bg-slate-800 text-slate-400 hover:bg-slate-700'
                        ]"
                    >
                        BUY
                    </button>
                    <button 
                        type="button"
                        @click="form.side = 'sell'"
                        :class="[
                            'py-3 px-4 rounded-xl font-semibold transition-all duration-200',
                            form.side === 'sell' 
                                ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/30' 
                                : 'bg-slate-800 text-slate-400 hover:bg-slate-700'
                        ]"
                    >
                        SELL
                    </button>
                </div>
            </div>
            
            <!-- Price Input -->
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">Price (USD)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                    <input 
                        :value="form.price"
                        @input="handlePriceInput"
                        type="text"
                        inputmode="decimal"
                        pattern="[0-9]*\.?[0-9]*"
                        placeholder="0.00"
                        class="w-full bg-slate-800 border border-slate-600 rounded-xl pl-8 pr-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                    />
                </div>
            </div>
            
            <!-- Amount Input -->
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">Amount ({{ form.symbol }})</label>
                <input 
                    :value="form.amount"
                    @input="handleAmountInput"
                    type="text"
                    inputmode="decimal"
                    pattern="[0-9]*\.?[0-9]*"
                    placeholder="0.00"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                />
            </div>
            
            <!-- Total -->
            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700">
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">Total</span>
                    <span class="text-xl font-bold text-white">${{ totalCost }}</span>
                </div>
            </div>
            
            <!-- Submit Button -->
            <button 
                type="submit"
                :disabled="!isValid || loading"
                :class="[
                    'w-full py-4 rounded-xl font-bold text-lg transition-all duration-200',
                    form.side === 'buy' 
                        ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 shadow-lg shadow-emerald-500/30' 
                        : 'bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 shadow-lg shadow-rose-500/30',
                    (!isValid || loading) ? 'opacity-50 cursor-not-allowed' : 'hover:scale-[1.02]'
                ]"
            >
                <span v-if="loading" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
                <span v-else>
                    {{ form.side === 'buy' ? 'Buy' : 'Sell' }} {{ form.symbol }}
                </span>
            </button>
        </form>
    </div>
</template>

