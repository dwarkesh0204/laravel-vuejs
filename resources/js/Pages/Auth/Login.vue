<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import { watch } from 'vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const toast = useToast();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// Show status message as toast
watch(() => props.status, (newStatus) => {
    if (newStatus) {
        toast.success(newStatus);
    }
}, { immediate: true });

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
        onError: () => {
            toast.error('Invalid credentials. Please try again.');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-white">Welcome Back</h1>
            <p class="text-slate-400 text-sm mt-1">Sign in to your trading account</p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter your email"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-2"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-slate-400">Remember me</span>
                </label>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-slate-400 hover:text-cyan-400 transition-colors"
                >
                    Forgot your password?
                </Link>

                <button
                    type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-semibold rounded-xl hover:from-cyan-600 hover:to-emerald-600 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-slate-800 transition-all duration-200 disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Sign In
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
