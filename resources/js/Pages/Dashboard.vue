<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import LimitOrderForm from '@/Components/Trading/LimitOrderForm.vue';
import BalanceCard from '@/Components/Trading/BalanceCard.vue';
import OrderBook from '@/Components/Trading/OrderBook.vue';
import MyOrders from '@/Components/Trading/MyOrders.vue';
import TradeNotification from '@/Components/Trading/TradeNotification.vue';
import { useToast } from '@/Composables/useToast';

const page = usePage();
const userId = page.props.auth.user.id;
const toast = useToast();

// State
const balance = ref('0');
const assets = ref([]);
const selectedSymbol = ref('BTC');
const refreshTrigger = ref(0);
const notificationRef = ref(null);
const isConnected = ref(false);

// Fetch user profile data
const fetchProfile = async () => {
    try {
        const response = await axios.get('/api/profile');
        if (response.data.success) {
            balance.value = response.data.data.user.balance;
            assets.value = response.data.data.assets;
        }
    } catch (err) {
        console.error('Failed to fetch profile:', err);
        toast.error('Failed to fetch profile data');
    }
};

// Handle order placed
const handleOrderPlaced = (order) => {
    fetchProfile();
    refreshTrigger.value++;
};

// Handle order cancelled
const handleOrderCancelled = (order) => {
    fetchProfile();
    refreshTrigger.value++;
};

// Setup Laravel Echo for real-time updates
const setupEcho = () => {
    if (typeof window.Echo === 'undefined') {
        console.warn('Laravel Echo not available');
        toast.warning('Real-time updates not available');
        return;
    }
    
    const channel = window.Echo.private(`user.${userId}`);
    
    channel.listen('.order.matched', (event) => {
        console.log('Order matched event received:', event);
        
        // Show toast notification for trade
        const trade = event.trade;
        toast.success(`🎉 Trade executed! ${trade.amount} ${trade.symbol} @ $${parseFloat(trade.price).toFixed(2)}`, 6000);
        
        // Show notification
        if (notificationRef.value) {
            notificationRef.value.addNotification(event.trade);
        }
        
        // Refresh data
        fetchProfile();
        refreshTrigger.value++;
    });
    
    channel.subscribed(() => {
        isConnected.value = true;
        console.log('Subscribed to private channel');
        toast.info('Connected to real-time updates');
    });
    
    channel.error((error) => {
        console.error('Channel error:', error);
        isConnected.value = false;
        toast.error('Real-time connection lost');
    });
};

// Cleanup Echo subscription
const cleanupEcho = () => {
    if (typeof window.Echo !== 'undefined') {
        window.Echo.leave(`user.${userId}`);
    }
};

onMounted(() => {
    fetchProfile();
    setupEcho();
});

onUnmounted(() => {
    cleanupEcho();
});

// Available symbols
const symbols = ['BTC', 'ETH', 'SOL', 'ADA', 'DOT'];
</script>

<template>
    <Head title="Trading Dashboard" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-white">
                    Trading Dashboard
                </h2>
                <div class="flex items-center gap-2">
                    <span 
                        :class="[
                            'flex items-center gap-1.5 text-xs px-3 py-1 rounded-full',
                            isConnected 
                                ? 'bg-emerald-100 text-emerald-700' 
                                : 'bg-slate-100 text-slate-500'
                        ]"
                    >
                        <span :class="['w-2 h-2 rounded-full', isConnected ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400']"></span>
                        {{ isConnected ? 'Live' : 'Connecting...' }}
                    </span>
                </div>
            </div>
        </template>
        
        <!-- Trade Notifications -->
        <TradeNotification ref="notificationRef" />
        
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Symbol Selector -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 overflow-x-auto pb-2">
                        <button
                            v-for="symbol in symbols"
                            :key="symbol"
                            @click="selectedSymbol = symbol"
                            :class="[
                                'px-4 py-2 rounded-xl font-medium transition-all duration-200',
                                selectedSymbol === symbol
                                    ? 'bg-slate-900 text-white shadow-lg'
                                    : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'
                            ]"
                        >
                            {{ symbol }}/USD
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left Column - Order Form & Balance -->
                    <div class="lg:col-span-4 space-y-6">
                        <LimitOrderForm 
                            :symbols="symbols"
                            @order-placed="handleOrderPlaced"
                        />
                        <BalanceCard 
                            :balance="balance"
                            :assets="assets"
                        />
                    </div>
                    
                    <!-- Middle Column - Order Book -->
                    <div class="lg:col-span-4">
                        <OrderBook 
                            :symbol="selectedSymbol"
                            :refresh-trigger="refreshTrigger"
                        />
                    </div>
                    
                    <!-- Right Column - My Orders -->
                    <div class="lg:col-span-4">
                        <MyOrders 
                            :refresh-trigger="refreshTrigger"
                            @order-cancelled="handleOrderCancelled"
                        />
                    </div>
                </div>
                
                <!-- Recent Trades Section -->
                <div class="mt-6 bg-slate-900 rounded-2xl p-6 border border-slate-700/50 shadow-xl">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Trading Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700/50">
                            <div class="text-slate-400 text-sm mb-1">Commission Rate</div>
                            <div class="text-2xl font-bold text-cyan-400">1.5%</div>
                            <div class="text-xs text-slate-500 mt-1">Applied to seller on each trade</div>
                        </div>
                        <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700/50">
                            <div class="text-slate-400 text-sm mb-1">Order Matching</div>
                            <div class="text-2xl font-bold text-emerald-400">Full Match</div>
                            <div class="text-xs text-slate-500 mt-1">Orders match when amounts are equal</div>
                        </div>
                        <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700/50">
                            <div class="text-slate-400 text-sm mb-1">Precision</div>
                            <div class="text-2xl font-bold text-amber-400">8 Decimals</div>
                            <div class="text-xs text-slate-500 mt-1">Maximum precision for all amounts</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
