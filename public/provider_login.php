
<?php
session_start();
require_once __DIR__ . '/../php/app/auth/process_provider_login.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Provider Login | UniSync</title>

    <script src="../javascript/tailwind.js"></script>
    <script src="../javascript/jquery.js"></script>
</head>

<body class="min-h-screen bg-[#F5F7F5] text-slate-800">

    <!-- Provider Navbar -->
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">

            <a href="home.php" class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#14532D] rounded-xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-6 h-6 text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01" />
                    </svg>
                </div>

                <div>
                    <h1 class="text-xl font-bold text-[#14532D]">UniSync</h1>
                    <p class="text-[10px] font-semibold tracking-[0.2em] text-emerald-700">
                        VENUE PROVIDER
                    </p>
                </div>
            </a>

            <a href="account_login.php"
                class="text-sm font-medium text-slate-500 hover:text-[#14532D] transition-colors">
                Back to Student Portal
            </a>

        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-[calc(100vh-81px)] flex items-center justify-center px-4 py-12">

        <div class="w-full max-w-6xl grid lg:grid-cols-2 bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-200">

            <!-- Login Form -->
            <section class="p-8 sm:p-12 lg:p-14 flex flex-col justify-center">

                <div class="max-w-md w-full mx-auto">

                    <div class="mb-9">
                        <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-[#15803D]"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4" />
                            </svg>
                        </div>

                        <p class="text-sm font-semibold text-emerald-700 mb-2">
                            VENUE PROVIDER PORTAL
                        </p>

                        <h2 class="text-3xl font-bold text-slate-900">
                            Welcome back.
                        </h2>

                        <p class="text-slate-500 mt-2">
                            Sign in using your existing UniSync account.
                        </p>
                    </div>

                    <!-- General Error -->
                    <div id="generalError"
                        class="hidden mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm">
                    </div>

                    <!-- Login Form -->
                    <form id="providerLoginForm" class="space-y-5">

                        <!-- Email -->
                        <div>
                            <label for="email"
                                class="block text-sm font-semibold text-slate-700 mb-2">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email address"
                                autocomplete="email"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 transition"
                                required>

                            <div class="field-error text-red-500 text-sm mt-1"
                                data-error="email"></div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password"
                                class="block text-sm font-semibold text-slate-700 mb-2">
                                Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-emerald-700 focus:border-emerald-700 transition"
                                required>

                            <div class="field-error text-red-500 text-sm mt-1"
                                data-error="password"></div>
                        </div>

                        <!-- Login Button -->
                        <button
                            type="submit"
                            id="providerLoginButton"
                            class="w-full bg-[#14532D] hover:bg-[#166534] text-white font-semibold py-3.5 rounded-xl transition-colors disabled:opacity-60 disabled:cursor-not-allowed">

                            Sign In
                        </button>

                    </form>

                    <!-- Registration -->
                    <div class="mt-8 text-center">
                        <p class="text-sm text-slate-500">
                            New to UniSync?
                            <a href="account_registration.php?redirect=provider"
                                class="text-[#15803D] font-semibold hover:underline">
                                Create an account
                            </a>
                        </p>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                        <p class="text-xs text-slate-400">
                            One UniSync account. Access to multiple portals.
                        </p>
                    </div>

                </div>

            </section>

            <!-- Branding Panel -->
            <section class="hidden lg:flex relative min-h-[650px] bg-[#123B28]">

                <img
                    src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1400&q=85"
                    alt="Elegant event venue"
                    class="absolute inset-0 w-full h-full object-cover opacity-45">

                <div class="absolute inset-0 bg-gradient-to-t from-[#092719] via-[#123B28]/60 to-[#123B28]/20"></div>

                <div class="relative z-10 flex flex-col justify-end p-12 text-white">

                    <div class="mb-8">
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/15 border border-white/20 text-xs font-semibold tracking-wider">
                            <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                            VENUE PARTNERS
                        </span>
                    </div>

                    <h2 class="text-4xl xl:text-5xl font-bold leading-tight">
                        Your space.
                        <br>
                        More possibilities.
                    </h2>

                    <p class="text-emerald-50/90 mt-5 text-base leading-relaxed max-w-md">
                        Make your venue part of the UniSync community.
                        Showcase your space and help bring events to life.
                    </p>

                    <div class="mt-10 flex items-center gap-3">
                        <div class="w-10 h-1 bg-emerald-400 rounded-full"></div>
                        <div class="w-3 h-1 bg-white/40 rounded-full"></div>
                        <div class="w-3 h-1 bg-white/40 rounded-full"></div>
                    </div>

                </div>

            </section>

        </div>

    </main>

    <!-- Login JavaScript -->
    <script src="../javascript/app/provider_login.js?v=1"></script>

</body>
</html>
