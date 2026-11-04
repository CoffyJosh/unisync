
<footer class="bg-[#1E3A5F] text-white mt-auto">

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">

            <!-- Branding -->
            <div class="space-y-4">

                <a href="index.php" class="inline-flex items-center gap-2.5">

                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
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
                        <i class="fa-solid fa-calendar-days text-white text-lg"></i>
                    </div>

                    <span class="text-2xl font-bold tracking-tight">
                        UniSync
                    </span>

                </a>

                <p class="text-sm leading-relaxed text-blue-100 max-w-sm">
                    Connecting students with campus events, organizations,
                    and opportunities to get involved in university life.
                </p>

            </div>
        </div>


        <!-- Divider -->
        <div class="border-t border-white/15 mt-10 pt-2">

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">

                <p class="text-xs text-blue-200 text-center sm:text-left">
                    &copy; <?= date('Y'); ?> UniSync. All rights reserved.
                </p>

                <p class="text-xs text-blue-200 text-center">
                    University Event and Organization Scheduling System
                </p>

            </div>

        </div>

    </div>

</footer>
