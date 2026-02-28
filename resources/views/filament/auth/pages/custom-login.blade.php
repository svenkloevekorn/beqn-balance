<div class="fi-simple-page min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-5xl overflow-hidden rounded-2xl shadow-2xl ring-1 ring-gray-950/5 dark:ring-white/10 flex flex-col lg:flex-row">

        {{-- Linke Seite: Branding --}}
        <div class="relative flex flex-col justify-center p-8 lg:p-12 lg:w-1/2 bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700 text-white overflow-hidden">
            {{-- Dekorative Elemente --}}
            <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-sky-400/20 blur-3xl"></div>

            <div class="relative z-10">
                {{-- Text-Logo --}}
                <div class="mb-8">
                    <span class="text-3xl lg:text-4xl font-bold tracking-tight">balance</span>
                    <span class="text-lg lg:text-xl font-light text-sky-200 ml-2">by BEQN</span>
                </div>

                {{-- Slogan --}}
                <p class="text-xl lg:text-2xl font-light leading-relaxed text-sky-100">
                    Dein Business<br>
                    <span class="font-semibold text-white">im Gleichgewicht</span><br>
                    halten.
                </p>
            </div>

        </div>

        {{-- Rechte Seite: Login-Formular --}}
        <div class="flex flex-col justify-center p-8 lg:p-12 lg:w-1/2 bg-white dark:bg-gray-900">
            <div class="w-full max-w-sm mx-auto">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
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
