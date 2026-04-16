# ClassAssist Pro — Stack Técnico

> Documentación del conjunto de tecnologías, frameworks, librerías y herramientas utilizadas en el desarrollo del sistema.

---

## Resumen ejecutivo

ClassAssist Pro es una aplicación web monolítica desarrollada sobre el stack **TALL** (Tailwind CSS + Alpine.js + Laravel + Livewire). El backend es PHP/Laravel con renderizado en el servidor; el frontend reactivo se maneja mediante componentes Livewire sin escribir JavaScript personalizado para la lógica de negocio.

---

## Lenguajes

| Lenguaje | Versión | Uso |
|----------|---------|-----|
| **PHP** | 8.2 | Backend: lógica de negocio, modelos, controladores, componentes Livewire |
| **HTML / Blade** | — | Plantillas del servidor (motor de plantillas de Laravel) |
| **CSS** | — | Estilos (generados por Tailwind CSS mediante utilidades) |
| **JavaScript** | ES2022+ (módulos) | Interactividad ligera en cliente (Alpine.js, Vite) |
| **SQL** | SQLite / MySQL | Definición de esquema y consultas via Eloquent ORM |

---

## Framework principal

### Laravel 12

- **Tipo:** Framework PHP MVC full-stack
- **Versión:** `^12.0`
- **Sitio:** https://laravel.com
- **Uso en el proyecto:**
  - Enrutamiento (`routes/web.php`) con grupos de middleware por rol
  - Eloquent ORM para todos los modelos y relaciones
  - Sistema de migraciones para versionado del esquema de base de datos
  - Seeders para datos iniciales (roles, tipos de calificación, sede, carrera, usuario admin)
  - Sistema de autenticación (via Breeze)
  - Middleware personalizado para control de acceso por rol
  - Facades: `Excel`, `PDF`, `Auth`, `Storage`, `DB`
  - Sistema de colas (database driver) para trabajos en background
  - Almacenamiento de archivos (disco `public` apuntando a `public/storage/`)

---

## Autenticación

### Laravel Breeze 2.x

- **Versión:** `^2.4`
- **Sitio:** https://laravel.com/docs/starter-kits#laravel-breeze
- **Tipo:** Paquete de scaffolding de autenticación oficial de Laravel
- **Uso en el proyecto:**
  - Provee las vistas y rutas de login, logout y registro
  - Integrado con Livewire (stack Livewire/Volt)
  - Base para el sistema de roles personalizado (`EnsureAdmin`, `EnsureCatedratico`)
  - Sesiones almacenadas en base de datos (`SESSION_DRIVER=database`)

---

## Frontend reactivo

### Livewire 3.x

- **Versión:** `^3.6.4`
- **Sitio:** https://livewire.laravel.com
- **Tipo:** Framework de componentes full-stack para Laravel (reactivo sin SPA)
- **Uso en el proyecto:**
  - Todos los módulos del sistema son componentes Livewire (`app/Livewire/`)
  - Estado de componente en PHP, sincronizado con el DOM vía AJAX transparente
  - Eventos entre componentes (`dispatch`, `@notify.window`)
  - Upload de archivos (`WithFileUploads` trait) para importación de Excel
  - Validación reactiva inline
  - Módulos: Clases, Estudiantes, Sesiones, Asistencia, Calificaciones, Exportación, Grupos, Ruleta, Desempeño, Bitácora, Sedes, Asignaciones, PantallaClase

### Livewire Volt 1.x

- **Versión:** `^1.7.0`
- **Sitio:** https://livewire.laravel.com/docs/volt
- **Tipo:** API funcional de componentes Livewire (single-file components)
- **Uso en el proyecto:** Componentes de autenticación generados por Breeze

### Alpine.js

- **Versión:** Incluida con Livewire 3 (no requiere instalación separada)
- **Sitio:** https://alpinejs.dev
- **Tipo:** Framework JavaScript minimalista para interactividad en el DOM
- **Uso en el proyecto:**
  - Notificaciones flotantes (`x-data`, `x-show`, `x-transition`, `@notify.window`)
  - Modales y toggles de UI que no requieren estado en servidor
  - Temporizador (cuenta regresiva en `pantalla-clase`)

---

## CSS / Estilos

### Tailwind CSS 3.x

- **Versión:** `^3.1.0`
- **Sitio:** https://tailwindcss.com
- **Tipo:** Framework CSS utility-first
- **Uso en el proyecto:**
  - Sistema de diseño completo: colores, espaciado, tipografía, grids, flex
  - Color principal: `#000b60` (azul oscuro institucional)
  - Soporte dark mode (`dark:` prefijo) en todas las vistas
  - Plugin `@tailwindcss/forms` para estilos base de formularios

### @tailwindcss/forms

- **Versión:** `^0.5.2`
- **Uso:** Normalización de estilos de inputs, selects y checkboxes

---

## Build tool

### Vite 7.x

- **Versión:** `^7.0.7`
- **Sitio:** https://vitejs.dev
- **Tipo:** Bundler y servidor de desarrollo moderno
- **Uso en el proyecto:**
  - Compila y empaqueta CSS y JS para producción (`npm run build`)
  - Hot Module Replacement (HMR) en desarrollo (`npm run dev`)
  - Integrado con Laravel via `laravel-vite-plugin`

---

## Base de datos

### SQLite (desarrollo) / MySQL (producción)

| Aspecto | Detalle |
|---------|---------|
| Motor por defecto | SQLite (archivo `database/database.sqlite`) |
| Motor recomendado en producción | MySQL 8.x o MariaDB 10.x |
| ORM | Eloquent (incluido en Laravel) |
| Migraciones | 25 archivos en `database/migrations/` |
| Convención de nombres | Tablas en singular sin prefijo (ej. `clase`, `estudiante`, `sesion`) |

### Tablas del sistema

| Tabla | Descripción |
|-------|-------------|
| `users` | Usuarios del sistema (admins y catedráticos) |
| `rol` | Catálogo de roles (`admin`, `catedratico`) |
| `usuario_rol` | Pivot usuario–rol |
| `sede` | Sedes universitarias |
| `carrera` | Carreras académicas |
| `sede_carrera` | Pivot sede–carrera |
| `clase` | Cursos/clases impartidas |
| `clase_catedratico` | Pivot clase–catedrático (asignaciones) |
| `estudiante` | Alumnos inscritos |
| `clase_estudiante` | Pivot clase–estudiante |
| `sesion` | Sesiones de clase (días de clase) |
| `asistencia` | Registro de asistencia por sesión |
| `participacion` | Participaciones en ruleta con calificación |
| `grupo` | Grupos de trabajo por sesión |
| `grupo_estudiante` | Pivot grupo–estudiante |
| `tipo_calificacion` | Tipos de evaluación (Parcial, Proyecto, etc.) |
| `calificacion` | Notas por estudiante, clase y tipo |
| `actividad` | Actividades individuales y grupales |
| `actividad_nota` | Notas de actividades por estudiante |
| `asignacion` | Asignaciones académicas de catedráticos |
| `bitacora` | Registro de acciones del administrador |
| `estadisticas_ruido` | Mediciones del sensor de ruido de PantallaClase |
| `sessions` | Sesiones de usuario (Laravel session driver) |
| `cache` | Caché del framework |
| `jobs` | Cola de trabajos en background |

---

## Librerías PHP (Composer)

### maatwebsite/excel 3.1

- **Versión:** `^3.1`
- **Sitio:** https://laravel-excel.com
- **Tipo:** Exportación e importación de archivos Excel/CSV para Laravel
- **Uso en el proyecto:**
  - Exportación multi-hoja del sistema completo (asistencia, participaciones, resumen, calificaciones)
  - Exportación de plantilla de actividades con notas pre-cargadas
  - Importación de notas desde plantilla Excel (`ActividadesImport`)
  - Importación masiva de estudiantes (`EstudiantesImport`)
  - Clases en `app/Exports/` y `app/Imports/`

### barryvdh/laravel-dompdf 3.1

- **Versión:** `^3.1`
- **Sitio:** https://github.com/barryvdh/laravel-dompdf
- **Tipo:** Generación de PDF desde vistas Blade usando DomPDF
- **Uso en el proyecto:**
  - Generación del Acta Oficial de Calificaciones (PDF con diseño institucional)
  - Formato carta horizontal (`letter`, `landscape`)
  - Fuentes embebidas: DejaVu Sans (unicode completo para caracteres en español)
  - Clase en `app/Http/Controllers/CalificacionesPdfController.php`
  - Vista en `resources/views/pdf/acta-calificaciones.blade.php`

### simplesoftwareio/simple-qrcode 4.2

- **Versión:** `^4.2`
- **Sitio:** https://www.simplesoftware.io/docs/simple-qrcode
- **Tipo:** Generación de códigos QR en PHP
- **Uso en el proyecto:**
  - QR de inscripción a clase (estudiantes escanean para auto-inscribirse)
  - QR de registro de asistencia (estudiantes escanean al inicio de sesión)
  - Generados dinámicamente con token único por clase/sesión

### laravel/tinker 2.x

- **Versión:** `^2.10.1`
- **Tipo:** REPL interactivo para Laravel
- **Uso:** Herramienta de depuración y pruebas en consola

---

## Librerías PHP de desarrollo

| Librería | Versión | Uso |
|----------|---------|-----|
| `laravel/breeze` | `^2.4` | Scaffolding de autenticación |
| `laravel/pail` | `^1.2.2` | Streaming de logs en tiempo real en la terminal |
| `laravel/pint` | `^1.24` | Formateador de código PHP (estilo PSR-12) |
| `laravel/sail` | `^1.41` | Entorno Docker para desarrollo local |
| `fakerphp/faker` | `^1.23` | Generación de datos falsos para factories/seeders |
| `phpunit/phpunit` | `^11.5.50` | Framework de pruebas unitarias |
| `mockery/mockery` | `^1.6` | Mocking para pruebas |
| `nunomaduro/collision` | `^8.6` | Reporte de errores mejorado en CLI |

---

## Librerías JavaScript / npm

| Librería | Versión | Uso |
|----------|---------|-----|
| `vite` | `^7.0.7` | Bundler y dev server |
| `laravel-vite-plugin` | `^2.0.0` | Integración Vite–Laravel (manifest, HMR) |
| `tailwindcss` | `^3.1.0` | Framework CSS |
| `@tailwindcss/forms` | `^0.5.2` | Plugin de formularios Tailwind |
| `@tailwindcss/vite` | `^4.0.0` | Plugin Vite para Tailwind v4 |
| `autoprefixer` | `^10.4.2` | PostCSS: prefijos CSS automáticos |
| `postcss` | `^8.4.31` | Procesador CSS (usado por Tailwind) |
| `axios` | `^1.11.0` | Cliente HTTP para AJAX (usado por Livewire internamente) |
| `concurrently` | `^9.0.1` | Ejecución paralela de múltiples procesos en desarrollo |

---

## Íconos

### Material Symbols (Google Fonts)

- **Tipo:** Fuente de íconos vectoriales
- **Variante usada:** Outlined (contorno)
- **Carga:** CDN de Google Fonts (enlazado en el layout principal)
- **Uso:** Iconografía de toda la interfaz — botones, pestañas, estados, menú de navegación

---

## Control de acceso (Roles y Middleware)

| Clase | Ruta | Descripción |
|-------|------|-------------|
| `EnsureAdmin` | `app/Http/Middleware/EnsureAdmin.php` | Solo permite acceso a usuarios con rol `admin` |
| `EnsureCatedratico` | `app/Http/Middleware/EnsureCatedratico.php` | Permite acceso a `admin` y `catedratico` |

Los grupos de rutas en `routes/web.php`:
- `middleware(['auth', 'verified', 'role.admin'])` → panel de administración (`/admin/*`)
- `middleware(['auth', 'verified', 'role.catedratico'])` → aplicación principal

---

## Arquitectura del sistema

```
┌─────────────────────────────────────────────────────────┐
│                     Navegador (Cliente)                  │
│          Tailwind CSS · Alpine.js · Livewire JS          │
└─────────────────────┬───────────────────────────────────┘
                      │ HTTP / WebSocket (Livewire)
┌─────────────────────▼───────────────────────────────────┐
│                  Laravel 12 (Servidor)                   │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────────┐  │
│  │   Livewire   │  │ Controladores│  │  Middleware   │  │
│  │ Componentes  │  │  HTTP (PDF)  │  │  (Roles/Auth) │  │
│  └──────┬───────┘  └──────┬───────┘  └───────────────┘  │
│         │                 │                               │
│  ┌──────▼─────────────────▼──────────────────────────┐   │
│  │              Eloquent ORM / Models                 │   │
│  └──────────────────────────┬─────────────────────────┘  │
│                             │                            │
│  ┌──────────────────────────▼─────────────────────────┐  │
│  │         Base de Datos (SQLite / MySQL)              │  │
│  │              25 tablas · Migraciones                │  │
│  └─────────────────────────────────────────────────────┘  │
│                                                          │
│  ┌─────────────────┐  ┌──────────────┐                  │
│  │  Exports/Imports│  │  PDF (DomPDF)│                  │
│  │  (Laravel Excel)│  │              │                  │
│  └─────────────────┘  └──────────────┘                  │
└─────────────────────────────────────────────────────────┘
```

---

## Módulos del sistema

| Módulo | Componente Livewire | Descripción |
|--------|---------------------|-------------|
| Dashboard | `Dashboard\Table` | Vista general de estadísticas |
| Clases | `Clases\Index` | CRUD de cursos/clases |
| Estudiantes | `Estudiantes\Index` | CRUD de alumnos, QR de inscripción |
| Sesiones | `Sesiones\Index`, `Detalle` | Gestión de sesiones de clase |
| Asistencia | `Asistencia\Index`, `Registrar` | Registro de asistencia con QR |
| Calificaciones | `Calificaciones\Index` | Notas, actividades, resumen, PDF acta |
| Grupos | `Grupos\Index`, `Historial` | Grupos de trabajo, historial |
| Ruleta | `Ruleta\Index` | Ruleta de participación con puntos |
| Desempeño | `Desempeno\Index` | Estadísticas de rendimiento |
| Exportación | `Exportacion\Index` | Descarga Excel multi-hoja |
| Pantalla Clase | `PantallaClase\Index` | Proyección en clase, sensor de ruido |
| Sedes | `Sedes\Index` | Gestión de sedes universitarias |
| Asignaciones | `Asignaciones\Index` | Asignación catedrático–clase |
| Admin — Usuarios | `Admin\Usuarios` | CRUD de usuarios del sistema |
| Admin — Tipos Calif. | `Admin\TiposCalificacion` | Configuración de tipos de evaluación |
| Admin — Bitácora | `Admin\Bitacora` | Registro de acciones administrativas |
| Portal Estudiante | `Portal\Estudiante` | Consulta de notas (estudiantes) |

---

## Flujo de exportación Excel

```
Usuario → Livewire (Exportacion\Index)
  → AsistenciaExport (WithMultipleSheets)
      ├── ResumenSheet         → Hoja 1: Resumen de asistencia por estudiante
      ├── DetalleAsistenciaSheet → Hoja 2: Detalle por sesión
      ├── ParticipacionesSheet → Hoja 3: Participaciones en ruleta
      └── CalificacionesSheet  → Hoja 4: Calificaciones por tipo
```

## Flujo de generación PDF

```
Usuario → Botón "Descargar Acta PDF"
  → GET /calificaciones/{claseId}/acta-pdf
  → CalificacionesPdfController@download
      → Consulta clase, tipos, estudiantes, calificaciones, actividades
      → calcularResumen() → Collection de filas por estudiante
      → Pdf::loadView('pdf.acta-calificaciones', $datos)
      → Descarga como acta_{clase}_{fecha}.pdf
```

---

## Convenciones de desarrollo

- **Nomenclatura de tablas:** singular sin prefijo (`clase`, `estudiante`, `sesion`)
- **Nomenclatura de columnas FK:** `{tabla}_id` (ej. `clase_id`, `usuario_id`)
- **Identificador de estudiante:** carné formato `0000-00-0+` (año-sede-correlativo)
- **Email institucional:** `@miumg.edu.gt`
- **Puntuación máxima:** siempre sobre 100 puntos (suma de todos los tipos)
- **Aprobación:** nota total ≥ 61 puntos
- **Idioma:** Español (Guatemala) — vistas, mensajes y datos en español

---

*Documentación generada: abril 2026 — ClassAssist Pro v1.0*
