<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ClassAssist Pro') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --guest-bg: #e8eef7;
                --guest-logo-bg: #1a237e;
                --guest-title: #1a237e;
                --guest-subtitle: #7986a3;
                --guest-card-bg: #ffffff;
                --guest-footer: #b0b8cc;
            }
            @media (prefers-color-scheme: dark) {
                :root {
                    --guest-bg: #071e27;
                    --guest-logo-bg: #303c9a;
                    --guest-title: #bcc2ff;
                    --guest-subtitle: #8890a8;
                    --guest-card-bg: #1e333c;
                    --guest-footer: #767683;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased" style="background-color: var(--guest-bg);">
        <div class="min-h-screen flex flex-col items-center justify-center px-4">

            <!-- Logo + Title -->
            <div class="flex flex-col items-center mb-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background-color: var(--guest-logo-bg);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold tracking-tight" style="color: var(--guest-title);">{{ config('app.name', 'ClassAssist Pro') }}</h1>
                <p class="text-xs font-semibold tracking-widest mt-1" style="color: var(--guest-subtitle);">PORTAL CATEDRÁTICO</p>
            </div>

            <!-- Card -->
            <div class="w-full max-w-md rounded-2xl shadow-lg px-8 py-8" style="background-color: var(--guest-card-bg);">
                {{ $slot }}
            </div>

            <p class="mt-3 text-xs tracking-widest" style="color: var(--guest-footer);">
                &copy; {{ date('Y') }} {{ strtoupper(config('app.name', 'CLASSASSIST PRO')) }}. TODOS LOS DERECHOS RESERVADOS.
            </p>
        </div>
    </body>
</html>
