<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

// Animated counter
const stats = ref({
    trades: 0,
    volume: 0,
    users: 0,
});

// Chart data for realistic animation
const chartBars = ref([]);
const currentPrice = ref(45230.50);
const priceChange = ref(2.34);
let chartInterval = null;

// Generate initial chart data
const generateChartData = () => {
    const bars = [];
    let baseHeight = 50;
    for (let i = 0; i < 24; i++) {
        // Smooth wave pattern with small variations
        const wave = Math.sin(i * 0.3) * 15;
        const noise = (Math.random() - 0.5) * 10;
        const height = Math.max(20, Math.min(85, baseHeight + wave + noise));
        const isGreen = Math.random() > 0.4; // 60% green, 40% red
        bars.push({ height, isGreen });
        baseHeight = height; // Smooth transition
    }
    return bars;
};

// Update chart with smooth animation
const updateChart = () => {
    // Shift bars left and add new one
    chartBars.value.shift();
    
    const lastBar = chartBars.value[chartBars.value.length - 1];
    const wave = Math.sin(Date.now() * 0.001) * 8;
    const noise = (Math.random() - 0.5) * 12;
    const newHeight = Math.max(20, Math.min(85, lastBar.height + wave + noise));
    const isGreen = newHeight >= lastBar.height;
    
    chartBars.value.push({ height: newHeight, isGreen });
    
    // Update price
    const priceNoise = (Math.random() - 0.5) * 50;
    currentPrice.value = Math.max(44800, Math.min(45600, currentPrice.value + priceNoise));
    priceChange.value = ((currentPrice.value - 44200) / 44200 * 100).toFixed(2);
};

onMounted(() => {
    // Initialize chart
    chartBars.value = generateChartData();
    
    // Start chart animation
    chartInterval = setInterval(updateChart, 1500);
    
    // Animate numbers
    const animateValue = (key, end, duration) => {
        let start = 0;
        const increment = end / (duration / 16);
        const timer = setInterval(() => {
            start += increment;
            if (start >= end) {
                stats.value[key] = end;
                clearInterval(timer);
            } else {
                stats.value[key] = Math.floor(start);
            }
        }, 16);
    };
    
    setTimeout(() => {
        animateValue('trades', 50000, 2000);
        animateValue('volume', 125, 2000);
        animateValue('users', 10000, 2000);
    }, 500);
});

onUnmounted(() => {
    if (chartInterval) {
        clearInterval(chartInterval);
    }
});

const features = [
    {
        icon: 'lightning',
        title: 'Real-Time Trading',
        description: 'Experience instant order matching with live updates via WebSocket technology. Your trades execute in milliseconds.'
    },
    {
        icon: 'shield',
        title: 'Secure & Atomic',
        description: 'Database transactions ensure your funds are always safe. Every trade is atomic - it either completes fully or not at all.'
    },
    {
        icon: 'chart',
        title: 'Live Order Book',
        description: 'Watch the market in real-time with our dynamic order book. See buy and sell orders update instantly.'
    },
    {
        icon: 'wallet',
        title: 'Multi-Asset Support',
        description: 'Trade multiple cryptocurrencies including BTC, ETH, SOL, and ADA against USD with 8-decimal precision.'
    },
];

const tradingPairs = ['BTC/USD', 'ETH/USD', 'SOL/USD', 'ADA/USD', 'DOT/USD'];
</script>

<template>
    <Head title="Trade Crypto - Limit Order Exchange" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950">
        <!-- Navigation -->
        <nav class="relative z-50 px-6 py-4">
            <div class="mx-auto max-w-7xl flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-emerald-400 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-white">LimitX</span>
                </div>
                
                <!-- Nav Links -->
                <div v-if="canLogin" class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                        class="px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-semibold rounded-xl hover:from-cyan-600 hover:to-emerald-600 transition-all duration-200"
                        >
                        Trading Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                            class="px-5 py-2 text-slate-300 hover:text-white transition-colors font-medium"
                            >
                            Sign In
                            </Link>

                            <Link
                                v-if="canRegister"
                            :href="route('register')"
                            class="px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-semibold rounded-xl hover:from-cyan-600 hover:to-emerald-600 transition-all duration-200"
                        >
                            Start Trading
                        </Link>
                    </template>
                </div>
            </div>
        </nav>
        
        <!-- Hero Section -->
        <section class="relative overflow-hidden px-6 pt-16 pb-24">
            <!-- Background Effects -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-cyan-500/20 rounded-full blur-3xl"></div>
                <div class="absolute top-60 -left-40 w-80 h-80 bg-emerald-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>
            </div>
            
            <div class="relative mx-auto max-w-7xl">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Content -->
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800/80 border border-slate-700/50 rounded-full mb-6">
                            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                            <span class="text-sm text-slate-300">Live Trading Available</span>
                        </div>
                        
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6">
                            Trade Crypto with
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-emerald-400">
                                Precision
                            </span>
                        </h1>
                        
                        <p class="text-lg text-slate-400 mb-8 max-w-xl mx-auto lg:mx-0">
                            Experience the power of limit order trading. Set your price, control your trades, 
                            and execute with confidence on our real-time exchange platform.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link
                                v-if="canRegister && !$page.props.auth.user"
                                :href="route('register')"
                                class="px-8 py-4 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-semibold rounded-xl hover:from-cyan-600 hover:to-emerald-600 transition-all duration-200 text-center shadow-lg shadow-cyan-500/25"
                            >
                                Create Free Account
                            </Link>
                            <Link
                                v-else-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="px-8 py-4 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-semibold rounded-xl hover:from-cyan-600 hover:to-emerald-600 transition-all duration-200 text-center shadow-lg shadow-cyan-500/25"
                            >
                                Go to Dashboard
                            </Link>
                            <a
                                href="#features"
                                class="px-8 py-4 bg-slate-800/80 border border-slate-700 text-white font-semibold rounded-xl hover:bg-slate-700/80 transition-all duration-200 text-center"
                            >
                                Learn More
                            </a>
                        </div>
                        
                        <!-- Trading Pairs Ticker -->
                        <div class="mt-12 flex items-center gap-4 justify-center lg:justify-start flex-wrap">
                            <span class="text-sm text-slate-500">Trade:</span>
                            <div class="flex gap-3">
                                <span
                                    v-for="pair in tradingPairs"
                                    :key="pair"
                                    class="px-3 py-1 bg-slate-800/60 border border-slate-700/50 rounded-lg text-sm text-slate-300"
                                >
                                    {{ pair }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Content - Trading Preview -->
                    <div class="relative">
                        <div class="bg-slate-800/60 backdrop-blur-sm border border-slate-700/50 rounded-2xl p-6 shadow-2xl">
                            <!-- Mock Trading Interface -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-amber-500/20 rounded-full flex items-center justify-center">
                                        <span class="text-amber-400 font-bold text-lg">₿</span>
                                    </div>
                                    <div>
                                        <div class="text-white font-semibold">BTC/USD</div>
                                        <div :class="priceChange >= 0 ? 'text-emerald-400' : 'text-red-400'" class="text-sm font-medium">
                                            {{ priceChange >= 0 ? '+' : '' }}{{ priceChange }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-white">${{ currentPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
                                    <div class="text-slate-400 text-sm flex items-center justify-end gap-1">
                                        <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                                        Live Price
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Realistic Chart -->
                            <div class="h-48 bg-slate-900/50 rounded-xl mb-6 flex items-end justify-center gap-0.5 p-4 overflow-hidden">
                                <div 
                                    v-for="(bar, index) in chartBars" 
                                    :key="index"
                                    class="flex-1 rounded-t transition-all duration-700 ease-in-out"
                                    :class="bar.isGreen 
                                        ? 'bg-gradient-to-t from-cyan-600 via-cyan-500/80 to-emerald-400/60' 
                                        : 'bg-gradient-to-t from-slate-600 via-slate-500/80 to-slate-400/60'"
                                    :style="{ height: `${bar.height}%` }"
                                ></div>
                            </div>

                            <!-- Order Book Preview -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-xs text-slate-500 mb-2 uppercase tracking-wide">Buy Orders</div>
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between text-sm relative">
                                            <div class="absolute inset-0 bg-emerald-500/10 rounded" style="width: 70%"></div>
                                            <span class="text-emerald-400 relative z-10 font-mono">$45,200</span>
                                            <span class="text-slate-400 relative z-10">0.50 BTC</span>
                                        </div>
                                        <div class="flex justify-between text-sm relative">
                                            <div class="absolute inset-0 bg-emerald-500/10 rounded" style="width: 90%"></div>
                                            <span class="text-emerald-400 relative z-10 font-mono">$45,150</span>
                                            <span class="text-slate-400 relative z-10">1.20 BTC</span>
                                        </div>
                                        <div class="flex justify-between text-sm relative">
                                            <div class="absolute inset-0 bg-emerald-500/10 rounded" style="width: 55%"></div>
                                            <span class="text-emerald-400 relative z-10 font-mono">$45,100</span>
                                            <span class="text-slate-400 relative z-10">0.80 BTC</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-500 mb-2 uppercase tracking-wide">Sell Orders</div>
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between text-sm relative">
                                            <div class="absolute inset-0 right-0 left-auto bg-red-500/10 rounded" style="width: 40%"></div>
                                            <span class="text-red-400 relative z-10 font-mono">$45,250</span>
                                            <span class="text-slate-400 relative z-10">0.30 BTC</span>
                                        </div>
                                        <div class="flex justify-between text-sm relative">
                                            <div class="absolute inset-0 right-0 left-auto bg-red-500/10 rounded" style="width: 85%"></div>
                                            <span class="text-red-400 relative z-10 font-mono">$45,300</span>
                                            <span class="text-slate-400 relative z-10">1.50 BTC</span>
                                        </div>
                                        <div class="flex justify-between text-sm relative">
                                            <div class="absolute inset-0 right-0 left-auto bg-red-500/10 rounded" style="width: 50%"></div>
                                            <span class="text-red-400 relative z-10 font-mono">$45,350</span>
                                            <span class="text-slate-400 relative z-10">0.70 BTC</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Badge -->
                        <div class="absolute -top-4 -right-4 px-4 py-2 bg-emerald-500 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/25">
                            1.5% Commission
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Stats Section -->
        <section class="px-6 py-16 border-y border-slate-800">
            <div class="mx-auto max-w-7xl">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="text-3xl md:text-4xl font-bold text-white mb-2">{{ stats.trades.toLocaleString() }}+</div>
                        <div class="text-slate-400">Trades Executed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl md:text-4xl font-bold text-white mb-2">${{ stats.volume }}M+</div>
                        <div class="text-slate-400">Trading Volume</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl md:text-4xl font-bold text-white mb-2">{{ stats.users.toLocaleString() }}+</div>
                        <div class="text-slate-400">Active Traders</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl md:text-4xl font-bold text-white mb-2">8</div>
                        <div class="text-slate-400">Decimal Precision</div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Features Section -->
        <section id="features" class="px-6 py-24">
            <div class="mx-auto max-w-7xl">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                        Powerful Trading Features
                    </h2>
                    <p class="text-slate-400 max-w-2xl mx-auto">
                        Built with the latest technology stack for a seamless trading experience. 
                        Real-time updates, secure transactions, and precision trading.
                    </p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="bg-slate-800/50 border border-slate-700/50 rounded-2xl p-6 hover:bg-slate-800/70 hover:border-slate-600/50 transition-all duration-300"
                    >
                        <div class="w-12 h-12 bg-gradient-to-br from-cyan-500/20 to-emerald-500/20 rounded-xl flex items-center justify-center mb-4">
                            <svg v-if="feature.icon === 'lightning'" class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <svg v-if="feature.icon === 'shield'" class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <svg v-if="feature.icon === 'chart'" class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <svg v-if="feature.icon === 'wallet'" class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                        <h3 class="text-lg font-semibold text-white mb-2">{{ feature.title }}</h3>
                        <p class="text-slate-400 text-sm">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- How It Works Section -->
        <section class="px-6 py-24 bg-slate-800/30">
            <div class="mx-auto max-w-7xl">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                        How Limit Orders Work
                                </h2>
                    <p class="text-slate-400 max-w-2xl mx-auto">
                        Take control of your trades with precision limit orders. 
                        Set your price and let the matching engine do the rest.
                    </p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Step 1 -->
                    <div class="relative">
                        <div class="absolute top-0 left-8 w-px h-full bg-gradient-to-b from-cyan-500 to-transparent md:hidden"></div>
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-emerald-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-cyan-500/25">
                                <span class="text-2xl font-bold text-white">1</span>
                            </div>
                            <div class="pt-2">
                                <h3 class="text-xl font-semibold text-white mb-2">Place Your Order</h3>
                                <p class="text-slate-400">
                                    Choose your asset, set your desired price and amount. 
                                    Buy orders require USD balance, sell orders lock your assets.
                                </p>
                            </div>
                        </div>
                            </div>

                    <!-- Step 2 -->
                    <div class="relative">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-emerald-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-cyan-500/25">
                                <span class="text-2xl font-bold text-white">2</span>
                            </div>
                            <div class="pt-2">
                                <h3 class="text-xl font-semibold text-white mb-2">Auto Matching</h3>
                                <p class="text-slate-400">
                                    Our engine finds matching counter orders instantly. 
                                    Buy orders match with sells at or below your price.
                                </p>
                            </div>
                        </div>
                            </div>

                    <!-- Step 3 -->
                    <div class="relative">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-emerald-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-cyan-500/25">
                                <span class="text-2xl font-bold text-white">3</span>
                            </div>
                            <div class="pt-2">
                                <h3 class="text-xl font-semibold text-white mb-2">Trade Executed</h3>
                                <p class="text-slate-400">
                                    Assets and USD are exchanged atomically. 
                                    Real-time notifications keep you informed instantly.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Technology Stack -->
        <section class="px-6 py-24">
            <div class="mx-auto max-w-7xl">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                        Built with Modern Technology
                    </h2>
                    <p class="text-slate-400 max-w-2xl mx-auto">
                        Powered by Laravel and Vue.js for robust backend operations and a responsive frontend experience.
                    </p>
                </div>
                
                <div class="flex flex-wrap justify-center gap-8">
                    <div class="flex items-center gap-3 px-6 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl">
                        <div class="w-8 h-8 bg-red-500/20 rounded-lg flex items-center justify-center">
                            <span class="text-red-400 font-bold text-sm">L</span>
                        </div>
                        <span class="text-white font-medium">Laravel</span>
                    </div>
                    <div class="flex items-center gap-3 px-6 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl">
                        <div class="w-8 h-8 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                            <span class="text-emerald-400 font-bold text-sm">V</span>
                        </div>
                        <span class="text-white font-medium">Vue.js</span>
                    </div>
                    <div class="flex items-center gap-3 px-6 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl">
                        <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center">
                            <span class="text-blue-400 font-bold text-sm">M</span>
                        </div>
                        <span class="text-white font-medium">MySQL</span>
                    </div>
                    <div class="flex items-center gap-3 px-6 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl">
                        <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center">
                            <span class="text-purple-400 font-bold text-sm">P</span>
                        </div>
                        <span class="text-white font-medium">Pusher</span>
                    </div>
                    <div class="flex items-center gap-3 px-6 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl">
                        <div class="w-8 h-8 bg-cyan-500/20 rounded-lg flex items-center justify-center">
                            <span class="text-cyan-400 font-bold text-sm">T</span>
                        </div>
                        <span class="text-white font-medium">Tailwind CSS</span>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- CTA Section -->
        <section class="px-6 py-24">
            <div class="mx-auto max-w-4xl">
                <div class="relative bg-gradient-to-r from-cyan-500/10 via-emerald-500/10 to-cyan-500/10 border border-slate-700/50 rounded-3xl p-12 text-center overflow-hidden">
                    <!-- Background Glow -->
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-96 bg-cyan-500/20 rounded-full blur-3xl"></div>
                    
                    <div class="relative">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                            Ready to Start Trading?
                        </h2>
                        <p class="text-slate-400 mb-8 max-w-xl mx-auto">
                            Join thousands of traders who trust our platform for secure and efficient limit order trading. 
                            Create your free account today.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link
                                v-if="canRegister && !$page.props.auth.user"
                                :href="route('register')"
                                class="px-8 py-4 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-semibold rounded-xl hover:from-cyan-600 hover:to-emerald-600 transition-all duration-200 shadow-lg shadow-cyan-500/25"
                            >
                                Create Free Account
                            </Link>
                            <Link
                                v-else-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="px-8 py-4 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-semibold rounded-xl hover:from-cyan-600 hover:to-emerald-600 transition-all duration-200 shadow-lg shadow-cyan-500/25"
                            >
                                Start Trading Now
                            </Link>
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('login')"
                                class="px-8 py-4 bg-slate-800 border border-slate-700 text-white font-semibold rounded-xl hover:bg-slate-700 transition-all duration-200"
                            >
                                Sign In
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Footer -->
        <footer class="px-6 py-12 border-t border-slate-800">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-cyan-400 to-emerald-400 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-white">LimitX Exchange</span>
                    </div>
                    
                    <div class="flex items-center gap-6 text-sm text-slate-400">
                        <span>Built for VirgoSoft Technical Assessment</span>
                        <span>•</span>
                        <span>Laravel + Vue.js + Pusher</span>
                    </div>
                    
                    <div class="text-sm text-slate-500">
                        © {{ new Date().getFullYear() }} LimitX. All rights reserved.
                    </div>
            </div>
        </div>
        </footer>
    </div>
</template>
