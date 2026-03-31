-- =====================================================
-- BASE DE DATOS: ClassAssist Pro
-- Script FINAL — Laravel 12 + Livewire + MySQL
-- =====================================================
-- Correcciones aplicadas sobre el script original:
--
--   [1] usuarios → users
--       Convención Laravel / Breeze: el modelo User busca
--       la tabla `users` por defecto. Sin este cambio,
--       Breeze falla en login/registro.
--
--   [2] users: +remember_token, +email_verified_at
--       Columnas requeridas por Laravel Breeze.
--
--   [3] sesiones: eliminado codigo_qr (redundante)
--       El QR se genera en runtime desde `token`.
--
--   [4] updated_at añadido a tablas sin él
--       Necesario para que Eloquent no lance errores
--       al hacer save() / update() en esos modelos.
--
--   [5] created_at añadido a grupo_estudiante
--       Inconsistencia en el script original: tenía
--       updated_at pero no created_at.
--
--   [6] Índices en columnas FK para performance
--       Los JOINs sobre asistencias, participaciones
--       y grupos son los más frecuentes del sistema.
--
--   [7] Password del seed fuera del SQL
--       Nunca texto plano. Usar DatabaseSeeder Laravel
--       con Hash::make().
-- =====================================================

CREATE DATABASE IF NOT EXISTS classassist_pro
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE classassist_pro;

-- =====================================================
-- TABLA: roles
-- =====================================================
CREATE TABLE roles (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50)  UNIQUE NOT NULL,
    descripcion VARCHAR(255),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA: users                         [CORRECCIÓN 1+2]
-- Renombrada de `usuarios` a `users` para compatibilidad
-- con Laravel Breeze (User model busca `users` por defecto).
-- Añadidos remember_token y email_verified_at [2].
-- =====================================================
CREATE TABLE users (
    id                  BIGINT       AUTO_INCREMENT PRIMARY KEY,
    nombre              VARCHAR(100) NOT NULL,
    email               VARCHAR(100) UNIQUE NOT NULL,
    password            VARCHAR(255) NOT NULL,
    remember_token      VARCHAR(100) NULL,      -- [2] Sesiones "recordar contraseña"
    email_verified_at   TIMESTAMP    NULL,      -- [2] Verificación de correo Breeze
    estado              BOOLEAN DEFAULT TRUE,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);

-- =====================================================
-- TABLA: usuario_rol  (N:M users ↔ roles)
-- =====================================================
CREATE TABLE usuario_rol (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  BIGINT NOT NULL,
    rol_id      BIGINT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,   -- [1]
    FOREIGN KEY (rol_id)     REFERENCES roles(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: clases
-- =====================================================
CREATE TABLE clases (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    descripcion TEXT,
    usuario_id  BIGINT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES users(id)                      -- [1]
);

-- =====================================================
-- TABLA: estudiantes
-- usuario_id es nullable: el estudiante puede o no
-- tener cuenta de acceso en el sistema.
-- =====================================================
CREATE TABLE estudiantes (
    id          BIGINT       AUTO_INCREMENT PRIMARY KEY,
    carnet      VARCHAR(50)  NOT NULL,
    nombre      VARCHAR(100) NOT NULL,
    correo      VARCHAR(100),
    usuario_id  BIGINT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL   -- [1]
);

-- =====================================================
-- TABLA: clase_estudiante  (N:M clases ↔ estudiantes)
-- [4] updated_at añadido
-- =====================================================
CREATE TABLE clase_estudiante (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    clase_id        BIGINT NOT NULL,
    estudiante_id   BIGINT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL,                                    -- [4]
    FOREIGN KEY (clase_id)      REFERENCES clases(id)      ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: sesiones
-- [3] codigo_qr eliminado: el QR se genera en runtime
--     desde `token` usando simplesoftwareio/simple-qrcode:
--     QrCode::size(300)->generate(route('asistencia', $sesion->token));
-- [4] updated_at añadido
-- =====================================================
CREATE TABLE sesiones (
    id          BIGINT       AUTO_INCREMENT PRIMARY KEY,
    clase_id    BIGINT       NOT NULL,
    fecha       DATE         NOT NULL,
    token       VARCHAR(255),                                          -- QR generado desde aquí
    expiracion  DATETIME,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL,                                        -- [4]
    FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: asistencias
-- UNIQUE (sesion_id, estudiante_id): previene duplicados
-- a nivel de BD, independiente de la lógica de Laravel.
-- selfie: campo para Módulo 3.12 (opcional).
-- =====================================================
CREATE TABLE asistencias (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    sesion_id       BIGINT NOT NULL,
    estudiante_id   BIGINT NOT NULL,
    fecha_hora      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    selfie          VARCHAR(255),                                      -- Módulo 3.12
    UNIQUE (sesion_id, estudiante_id),
    FOREIGN KEY (sesion_id)     REFERENCES sesiones(id)    ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: participaciones
-- [4] updated_at añadido
-- =====================================================
CREATE TABLE participaciones (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    sesion_id       BIGINT NOT NULL,
    estudiante_id   BIGINT NOT NULL,
    calificacion    DECIMAL(5,2),
    comentario      TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL,                                    -- [4]
    FOREIGN KEY (sesion_id)     REFERENCES sesiones(id)    ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: grupos
-- [4] updated_at añadido
-- =====================================================
CREATE TABLE grupos (
    id          BIGINT      AUTO_INCREMENT PRIMARY KEY,
    sesion_id   BIGINT      NOT NULL,
    nombre      VARCHAR(50),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL,                                        -- [4]
    FOREIGN KEY (sesion_id) REFERENCES sesiones(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: grupo_estudiante  (N:M grupos ↔ estudiantes)
-- [4] updated_at añadido
-- [5] created_at añadido (faltaba en el script original)
-- =====================================================
CREATE TABLE grupo_estudiante (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    grupo_id        BIGINT NOT NULL,
    estudiante_id   BIGINT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,               -- [5]
    updated_at      TIMESTAMP NULL,                                    -- [4]
    FOREIGN KEY (grupo_id)      REFERENCES grupos(id)      ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: tipos_calificacion  (catálogo — sin ENUM)
-- Más flexible que ENUM: agregar tipos sin ALTER TABLE.
-- =====================================================
CREATE TABLE tipos_calificacion (
    id          BIGINT      AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50) UNIQUE NOT NULL,
    descripcion VARCHAR(255)
);

-- =====================================================
-- TABLA: calificaciones
-- =====================================================
CREATE TABLE calificaciones (
    id                    BIGINT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id         BIGINT NOT NULL,
    clase_id              BIGINT NOT NULL,
    tipo_calificacion_id  BIGINT NOT NULL,
    nota                  DECIMAL(5,2),
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id)        REFERENCES estudiantes(id)        ON DELETE CASCADE,
    FOREIGN KEY (clase_id)             REFERENCES clases(id)             ON DELETE CASCADE,
    FOREIGN KEY (tipo_calificacion_id) REFERENCES tipos_calificacion(id)
);

-- =====================================================
-- CORRECCIÓN [6]: ÍNDICES EN COLUMNAS FK
-- Aceleran los JOINs más frecuentes del sistema.
-- Especialmente críticos en: asistencias, participaciones,
-- grupo_estudiante y calificaciones.
-- =====================================================
ALTER TABLE usuario_rol       ADD INDEX idx_usuario_id    (usuario_id);
ALTER TABLE usuario_rol       ADD INDEX idx_rol_id        (rol_id);
ALTER TABLE clase_estudiante  ADD INDEX idx_clase_id      (clase_id);
ALTER TABLE clase_estudiante  ADD INDEX idx_estudiante_id (estudiante_id);
ALTER TABLE sesiones          ADD INDEX idx_clase_id      (clase_id);
ALTER TABLE asistencias       ADD INDEX idx_sesion_id     (sesion_id);
ALTER TABLE asistencias       ADD INDEX idx_estudiante_id (estudiante_id);
ALTER TABLE participaciones   ADD INDEX idx_sesion_id     (sesion_id);
ALTER TABLE participaciones   ADD INDEX idx_estudiante_id (estudiante_id);
ALTER TABLE grupos            ADD INDEX idx_sesion_id     (sesion_id);
ALTER TABLE grupo_estudiante  ADD INDEX idx_grupo_id      (grupo_id);
ALTER TABLE grupo_estudiante  ADD INDEX idx_estudiante_id (estudiante_id);
ALTER TABLE calificaciones    ADD INDEX idx_estudiante_id (estudiante_id);
ALTER TABLE calificaciones    ADD INDEX idx_clase_id      (clase_id);

-- =====================================================
-- DATOS INICIALES
-- =====================================================

INSERT INTO roles (nombre, descripcion) VALUES
    ('admin',       'Administrador del sistema'),
    ('catedratico', 'Docente'),
    ('estudiante',  'Alumno');

INSERT INTO tipos_calificacion (nombre, descripcion) VALUES
    ('asistencia',    'Puntos por asistencia'),
    ('participacion', 'Puntos por participación'),
    ('grupo',         'Puntos por trabajo en grupo');

-- =====================================================
-- CORRECCIÓN [7]: SEED DEL USUARIO ADMIN
-- NO usar contraseñas en texto plano dentro del SQL.
-- Crear desde el DatabaseSeeder de Laravel:
--
-- database/seeders/DatabaseSeeder.php
-- ----------------------------------------------------
-- use App\Models\User;
-- use Illuminate\Support\Facades\Hash;
--
-- User::create([
--     'nombre'   => 'Admin General',
--     'email'    => 'admin@classassist.com',
--     'password' => Hash::make('123456'),
--     'estado'   => true,
-- ]);
--
-- DB::table('usuario_rol')->insert([
--     'usuario_id' => 1,
--     'rol_id'     => 1,
-- ]);
-- ----------------------------------------------------
-- Ejecutar con: php artisan db:seed
-- =====================================================