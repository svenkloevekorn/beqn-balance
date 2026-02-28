<div class="fi-simple-page min-h-screen flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-950">
    <div class="w-full max-w-4xl overflow-hidden rounded-2xl shadow-2xl ring-1 ring-gray-950/5 dark:ring-white/10 flex flex-col lg:flex-row min-h-[480px]">

        {{-- Linke Seite: Branding --}}
        <div class="relative flex flex-col justify-center items-center p-10 lg:p-16 lg:w-5/12 bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700 text-white overflow-hidden">
            {{-- Dekorative Elemente --}}
            <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-sky-400/20 blur-3xl"></div>

            <div class="relative z-10 text-center">
                {{-- Text-Logo --}}
                <div class="mb-6">
                    <div class="text-3xl font-bold tracking-tight leading-none">balance</div>
                    <div class="text-sm font-medium tracking-widest uppercase text-sky-200 mt-1">by BEQN</div>
                </div>

                {{-- Trennlinie --}}
                <div class="w-10 h-px bg-sky-300/50 mx-auto mb-6"></div>

                {{-- Slogan --}}
                <p class="text-base font-light leading-relaxed text-sky-100">
                    Dein Business<br>
                    <span class="font-semibold text-white">im Gleichgewicht</span> halten.
                </p>
            </div>
        </div>

        {{-- Rechte Seite: Login-Formular --}}
        <div class="flex flex-col justify-center p-10 lg:p-16 lg:w-7/12 bg-white dark:bg-gray-900">
            <div class="w-full max-w-sm mx-auto">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                    Willkommen zurück
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
                    Melde dich an, um fortzufahren.
                </p>

                {{ $this->content }}
            </div>
        </div>

    </div>

    <x-filament-actions::modals />
</div>
