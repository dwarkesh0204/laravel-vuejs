<script setup>
import { computed } from 'vue';

const props = defineProps({
    balance: {
        type: [String, Number],
        default: '0'
    },
    assets: {
        type: Array,
        default: () => []
    }
});

const formattedBalance = computed(() => {
    const num = parseFloat(props.balance);
    return isNaN(num) ? '0.00' : num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 8 });
});
</script>

<template>
    <div class="bg-slate-900 rounded-2xl p-6 border border-slate-700/50 shadow-xl">
        <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Account Balance
        </h2>
        
        <!-- USD Balance -->
        <div class="bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-xl p-5 border border-amber-500/30 mb-6">
            <div class="text-sm text-amber-300/80 mb-1">USD Balance</div>
            <div class="text-3xl font-bold text-white flex items-baseline gap-1">
                <span class="text-amber-400">$</span>
                {{ formattedBalance }}
            </div>
        </div>
        
        <!-- Asset Balances -->
        <div class="space-y-3">
            <div class="text-sm font-medium text-slate-400 mb-3">Crypto Assets</div>
            
            <div v-if="assets.length === 0" class="text-center py-8 text-slate-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <p>No assets yet</p>
            </div>
            
            <div 
                v-for="asset in assets" 
                :key="asset.symbol"
                class="bg-slate-800/50 rounded-xl p-4 border border-slate-700/50 hover:border-slate-600 transition-colors"
            >
                <div class="flex justify-between items-center">
                    <div>
                        <div class="font-semibold text-white">{{ asset.symbol }}</div>
                        <div class="text-xs text-slate-500">Available / Locked</div>
                    </div>
                    <div class="text-right">
                        <div class="font-mono text-white">{{ parseFloat(asset.amount).toFixed(8) }}</div>
                        <div class="text-xs text-slate-500">
                            <span class="text-amber-400">{{ parseFloat(asset.locked_amount).toFixed(8) }}</span> locked
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


