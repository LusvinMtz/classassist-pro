<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ClassAssist Pro') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg:        #e8eef7;
            --bg2:       #d6dfef;
            --primary:   #1a237e;
            --primary-l: #303c9a;
            --accent:    #5c6bc0;
            --card:      #ffffff;
            --muted:     #4a5568;
            --subtle:    #7986a3;
            --border:    #dde6f5;
            --input-bg:  #eff3fc;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg:        #071e27;
                --bg2:       #0d2a35;
                --primary:   #bcc2ff;
                --primary-l: #303c9a;
                --accent:    #5c6bc0;
                --card:      #1e333c;
                --muted:     #8890a8;
                --subtle:    #8890a8;
                --border:    #2a3f4d;
                --input-bg:  #162532;
            }
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Fondo con formas decorativas ── */
        .bg-shapes {
            position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0;
        }
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: .08;
            background: var(--primary-l);
        }
        .shape-1 { width: 600px; height: 600px; top: -200px; left: -150px; }
        .shape-2 { width: 400px; height: 400px; bottom: -100px; right: -100px; }
        .shape-3 { width: 200px; height: 200px; top: 40%; left: 60%; }
        @media (prefers-color-scheme: dark) {
            .shape { opacity: .06; }
        }

        /* ── Layout ── */
        .page {
            position: relative; z-index: 1;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            gap: 3rem;
        }

        /* ── Hero ── */
        .hero { text-align: center; }

        .logo-wrap {
            width: 72px; height: 72px;
            border-radius: 20px;
            background: var(--primary-l);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 24px rgba(26,35,126,.30);
        }
        .logo-wrap svg { width: 36px; height: 36px; color: #fff; }

        .hero-title {
            font-size: clamp(2rem, 5vw, 2.75rem);
            font-weight: 800;
            letter-spacing: -.5px;
            color: var(--primary);
            line-height: 1.1;
        }
        .hero-sub {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .25em;
            text-transform: uppercase;
            color: var(--subtle);
            margin-top: .45rem;
        }
        .hero-desc {
            margin-top: 1rem;
            font-size: .95rem;
            color: var(--muted);
            max-width: 420px;
            margin-inline: auto;
            line-height: 1.65;
        }

        /* ── Cards ── */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 1.25rem;
            width: 100%;
            max-width: 640px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            text-decoration: none;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,.12);
            border-color: var(--accent);
        }
        .card:active { transform: translateY(-1px); }

        .card-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .card-icon svg { width: 26px; height: 26px; }

        .card-icon.prof  { background: #e8eaf6; color: #3949ab; }
        .card-icon.stud  { background: #e0f2fe; color: #0277bd; }
        @media (prefers-color-scheme: dark) {
            .card-icon.prof { background: #23295a; color: #9fa8da; }
            .card-icon.stud { background: #0b2d42; color: #4fc3f7; }
        }

        .card-body { flex: 1; }

        .card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: .35rem;
        }

        .card-desc {
            font-size: .82rem;
            color: var(--muted);
            line-height: 1.6;
        }

        .card-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .06em;
            padding: .35rem .85rem;
            border-radius: 999px;
            width: fit-content;
            transition: opacity .15s;
        }
        .card:hover .card-badge { opacity: .85; }

        .card-badge.prof { background: #3949ab; color: #fff; }
        .card-badge.stud { background: #0277bd; color: #fff; }

        .card-badge svg { width: 13px; height: 13px; }

        /* ── Divider ── */
        .divider {
            display: flex; align-items: center; gap: .75rem;
            width: 100%; max-width: 640px;
        }
        .divider-line { flex: 1; height: 1px; background: var(--border); }
        .divider-text { font-size: .7rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: var(--subtle); }

        /* ── Features strip ── */
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: .65rem 1.5rem;
            max-width: 560px;
        }
        .feature {
            display: flex;
            align-items: center;
            gap: .4rem;
            font-size: .75rem;
            color: var(--subtle);
        }
        .feature svg { width: 14px; height: 14px; color: var(--accent); flex-shrink: 0; }

        /* ── Footer ── */
        footer {
            position: relative; z-index: 1;
            text-align: center;
            padding: 1rem;
            font-size: .65rem;
            font-weight: 600;
            letter-spacing: .15em;
            text-transform: uppercase;
            color: var(--subtle);
        }
    </style>
</head>
<body>

    <!-- Formas decorativas de fondo -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <main class="page">

        <!-- Hero -->
        <div class="hero">
            <div class="logo-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                </svg>
            </div>
            <h1 class="hero-title">{{ config('app.name', 'ClassAssist Pro') }}</h1>
            <p class="hero-sub">Plataforma de gestión académica</p>
            <p class="hero-desc">
                Bienvenido. Selecciona tu perfil para continuar.
            </p>
        </div>

        <!-- Tarjetas de acceso -->
        <div class="cards">

            <!-- Catedrático -->
            <a href="{{ route('login') }}" class="card">
                <div class="card-icon prof">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </div>
                <div class="card-body">
                    <p class="card-title">Soy catedrático</p>
                    <p class="card-desc">
                        Accede para gestionar tus clases, registrar asistencia, calificar a tus estudiantes y más.
                    </p>
                </div>
                <span class="card-badge prof">
                    Iniciar sesión
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </span>
            </a>

            <!-- Estudiante -->
            <a href="{{ route('portal.index') }}" class="card">
                <div class="card-icon stud">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
                <div class="card-body">
                    <p class="card-title">Soy estudiante</p>
                    <p class="card-desc">
                        Consulta tu asistencia y calificaciones ingresando únicamente tu carnet y correo electrónico.
                    </p>
                </div>
                <span class="card-badge stud">
                    Consultar mi información
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </span>
            </a>

        </div>

        <!-- Separador -->
        <div class="divider">
            <div class="divider-line"></div>
            <span class="divider-text">Características</span>
            <div class="divider-line"></div>
        </div>

        <!-- Features -->
        <div class="features">
            <div class="feature">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Control de asistencia
            </div>
            <div class="feature">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Registro de calificaciones
            </div>
            <div class="feature">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Gestión de grupos
            </div>
            <div class="feature">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Pantalla de clase en tiempo real
            </div>
            <div class="feature">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Exportación de reportes
            </div>
            <div class="feature">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Ruleta de participación
            </div>
        </div>

    </main>

    <footer>
        &copy; {{ date('Y') }} {{ strtoupper(config('app.name', 'CLASSASSIST PRO')) }}. Todos los derechos reservados.
    </footer>

</body>
</html>
