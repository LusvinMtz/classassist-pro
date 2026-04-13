<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Portal Estudiantil — {{ config('app.name', 'ClassAssist Pro') }}</title>

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
                --guest-muted: #4a5568;
                --guest-input-bg: #eff3fc;
                --guest-input-border: #dde6f5;
            }
            @media (prefers-color-scheme: dark) {
                :root {
                    --guest-bg: #071e27;
                    --guest-logo-bg: #303c9a;
                    --guest-title: #bcc2ff;
                    --guest-subtitle: #8890a8;
                    --guest-card-bg: #1e333c;
                    --guest-footer: #767683;
                    --guest-muted: #8890a8;
                    --guest-input-bg: #162532;
                    --guest-input-border: #2a3f4d;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased" style="background-color: var(--guest-bg);">

        <!-- Botón acceso catedrático (esquina superior derecha) -->
        <div class="fixed top-4 right-4 z-50">
            <a href="{{ route('login') }}"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold tracking-wide text-white shadow transition hover:opacity-90 active:scale-95"
                style="background-color: var(--guest-logo-bg);">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                Acceso Catedrático
            </a>
        </div>

        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-16">

            <!-- Logo + Títulos -->
            <div class="flex flex-col items-center mb-8 text-center">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 shadow-lg"
                    style="background-color: var(--guest-logo-bg);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold tracking-tight" style="color: var(--guest-title);">
                    {{ config('app.name', 'ClassAssist Pro') }}
                </h1>
                <p class="text-xs font-semibold tracking-widest mt-1" style="color: var(--guest-subtitle);">
                    PORTAL ESTUDIANTIL
                </p>
                <p class="mt-3 text-sm max-w-sm" style="color: var(--guest-muted);">
                    Consulta tu asistencia y calificaciones ingresando tu carnet y correo electrónico.
                </p>
            </div>

            <!-- Card con componente Livewire -->
            <div class="w-full max-w-lg rounded-2xl shadow-lg px-8 py-8" style="background-color: var(--guest-card-bg);">
                @livewire('portal.estudiante')
            </div>

            <p class="mt-6 text-xs tracking-widest" style="color: var(--guest-footer);">
                &copy; {{ date('Y') }} {{ strtoupper(config('app.name', 'CLASSASSIST PRO')) }}. TODOS LOS DERECHOS RESERVADOS.
            </p>
        </div>

        @livewireScripts
    </body>
</html>
