<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ClassAssist Pro</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Manrope', sans-serif; }
    </style>
</head>

<body class="bg-[#f3faff] text-[#071e27] dark:bg-[#071e27] dark:text-[#dff4ff]">

    @include('layouts.sidebar')

    <main class="md:ml-64 min-h-screen p-8">
        {{ $slot }}
    </main>

    @stack('pre-scripts')
    @livewireScripts
    @stack('scripts')
</body>
</html>