<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-100">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-end h-16 items-center space-x-4">
                    <Link
                        :href="route('login')"
                        :class="[
                            'px-3 py-2 text-sm font-medium',
                            route().current('login')
                                ? 'text-indigo-600 border-b-2 border-indigo-600'
                                : 'text-gray-600 hover:text-gray-800'
                        ]"
                    >
                        Login
                    </Link>
                    <Link
                        :href="route('register')"
                        :class="[
                            'px-3 py-2 text-sm font-medium',
                            route().current('register')
                                ? 'text-indigo-600 border-b-2 border-indigo-600'
                                : 'text-gray-600 hover:text-gray-800'
                        ]"
                    >
                        Register
                    </Link>
                </div>
            </div>
        </nav>

        <div class="flex-1 flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <Link :href="route('login')">
                    <ApplicationLogo class="w-20 h-20 fill-current text-gray-500" />
                </Link>
            </div>

            <div
                class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
