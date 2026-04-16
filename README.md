# ClassAssist Pro

Sistema de gestión académica diseñado para catedráticos universitarios. Permite administrar clases, sesiones, asistencia, calificaciones, actividades, grupos y generación de reportes.

---

## Requisitos previos

| Herramienta | Versión mínima |
|-------------|----------------|
| PHP | 8.2 |
| Composer | 2.x |
| Node.js | 18.x o superior |
| npm | 9.x o superior |
| SQLite / MySQL / PostgreSQL | (ver configuración) |

> Por defecto el proyecto usa **SQLite** (sin servidor de base de datos adicional). Para producción se recomienda MySQL.

---

## Instalación paso a paso

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio> classassist-pro
cd classassist-pro
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Instalar dependencias JavaScript

```bash
npm install
```

### 4. Copiar el archivo de entorno

```bash
cp .env.example .env
```

### 5. Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 6. Configurar la base de datos

#### Opción A — SQLite (desarrollo rápido, sin configuración adicional)

El archivo `.env` ya viene configurado para SQLite. Solo hay que crear el archivo:

```bash
touch database/database.sqlite
```

#### Opción B — MySQL (recomendado para producción)

Editar `.env` y reemplazar la sección `DB_*`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=classassist_pro
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

Crear la base de datos en MySQL:

```sql
CREATE DATABASE classassist_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 7. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

Esto crea todas las tablas y carga los datos iniciales:
- Roles: `admin` y `catedratico`
- Tipos de calificación: Parcial 1 (15), Parcial 2 (15), Actividades (25), Proyecto (10), Examen Final (35)
- Sede: Sanarate, El Progreso
- Carrera: Ingeniería en Sistemas (50 cursos precargados)
- Usuario administrador por defecto

### 8. Compilar los assets frontend

```bash
# Desarrollo (con hot-reload)
npm run dev

# Producción (build optimizado)
npm run build
```

### 9. Levantar el servidor de desarrollo

```bash
php artisan serve
```

La aplicación estará disponible en: **http://localhost:8000**

---

## Credenciales por defecto

| Campo    | Valor                    |
|----------|--------------------------|
| Email    | `admin@classassist.com`  |
| Password | `123`                    |
| Rol      | Administrador            |

> Cambiar la contraseña inmediatamente después del primer acceso en producción.

---

## Instalación en una sola línea (script automático)

El `composer.json` incluye un script que ejecuta todos los pasos necesarios:

```bash
composer run setup
```

Esto ejecuta en orden: `composer install` → copia `.env` → genera la clave → ejecuta migraciones → `npm install` → `npm run build`.

---

## Modo desarrollo completo (todos los servicios en paralelo)

```bash
composer run dev
```

Levanta simultáneamente:
- Servidor PHP (`php artisan serve`)
- Worker de colas (`php artisan queue:listen`)
- Log en tiempo real (`php artisan pail`)
- Vite con hot-reload (`npm run dev`)

---

## Configuración de almacenamiento de archivos

El sistema guarda selfies y archivos en `public/storage/`. El disco `public` está configurado para apuntar directamente a esa carpeta sin necesidad de symlink:

```env
FILESYSTEM_DISK=local
```

En hosting compartido no se requiere ejecutar `php artisan storage:link`.

---

## Variables de entorno importantes

```env
APP_NAME=ClassAssist Pro
APP_ENV=local          # cambiar a "production" en servidor
APP_DEBUG=true         # cambiar a "false" en producción
APP_URL=http://localhost

# Base de datos
DB_CONNECTION=sqlite   # o mysql

# Sesiones y caché (database por defecto, no requiere Redis)
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

---

## Estructura de carpetas relevante

```
classassist-pro/
├── app/
│   ├── Http/Controllers/     # Controladores HTTP (PDF, etc.)
│   ├── Livewire/             # Componentes Livewire (lógica de UI)
│   ├── Models/               # Modelos Eloquent
│   ├── Exports/              # Clases de exportación Excel
│   └── Imports/              # Clases de importación Excel
├── database/
│   ├── migrations/           # Migraciones de base de datos
│   └── seeders/              # Datos iniciales
├── documentacion/            # Documentación técnica y manuales
├── resources/
│   ├── views/
│   │   ├── livewire/         # Vistas de componentes Livewire
│   │   └── pdf/              # Plantillas para generación de PDF
│   └── css/                  # Estilos (Tailwind)
└── routes/
    └── web.php               # Definición de rutas
```

---

## Ejecutar pruebas

```bash
composer run test
```

---

## Documentación adicional

La carpeta [`documentacion/`](documentacion/) contiene:

| Archivo | Contenido |
|---------|-----------|
| [`00_stack_tecnico.md`](documentacion/00_stack_tecnico.md) | Frameworks, librerías, lenguajes y motor de base de datos |
| [`01_modelo_datos_y_casos_uso.md`](documentacion/01_modelo_datos_y_casos_uso.md) | Modelo de datos y casos de uso |
| [`02_manual_admin.md`](documentacion/02_manual_admin.md) | Manual del administrador |
| [`03_manual_catedratico.md`](documentacion/03_manual_catedratico.md) | Manual del catedrático |
| [`04_manual_estudiante.md`](documentacion/04_manual_estudiante.md) | Manual del estudiante (portal) |

---

## Licencia

Proyecto académico — Universidad Mariano Gálvez de Guatemala, Sede Sanarate.
