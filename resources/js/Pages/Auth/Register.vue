<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';

const toast = useToast();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
        onError: () => {
            toast.error('Registration failed. Please check your input.');
        },
        onSuccess: () => {
            toast.success('Account created successfully! Welcome aboard!');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-white">Create Account</h1>
            <p class="text-slate-400 text-sm mt-1">Start trading today</p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-2"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Enter your name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2"
                    v-model="form.email"
                    required
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
                    autocomplete="new-password"
                    placeholder="Create a password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-2"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm your password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link
                    :href="route('login')"
                    class="text-sm text-slate-400 hover:text-cyan-400 transition-colors"
                >
                    Already registered?
                </Link>

                <button
                    type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-semibold rounded-xl hover:from-cyan-600 hover:to-emerald-600 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-slate-800 transition-all duration-200 disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Register
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
