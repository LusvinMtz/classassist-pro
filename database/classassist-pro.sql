-- =====================================================================
-- BASE DE DATOS: ClassAssist Pro
-- Universidad Mariano Gálvez — Sedes Guastatoya y Sanarate
-- Script limpio: tablas (migraciones) + datos (seeder)
-- Ejecutar con: mysql -u root -p < classassist-pro.sql
-- O desde phpMyAdmin / DBeaver importando este archivo.
--
-- NOTA: La contraseña del admin está como hash bcrypt de '123'.
--       Para regenerarla ejecutar el seeder de Laravel:
--       php artisan migrate:fresh --seed
-- =====================================================================

CREATE DATABASE IF NOT EXISTS classassist_pro
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE classassist_pro;

SET FOREIGN_KEY_CHECKS = 0;


-- ─────────────────────────────────────────────────────────────────────
-- TABLAS LARAVEL CORE
-- ─────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS migrations (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch     INT          NOT NULL
);

CREATE TABLE IF NOT EXISTS cache (
    `key`      VARCHAR(255) NOT NULL PRIMARY KEY,
    value      MEDIUMTEXT   NOT NULL,
    expiration INT          NOT NULL,
    INDEX idx_expiration (expiration)
);

CREATE TABLE IF NOT EXISTS cache_locks (
    `key`      VARCHAR(255) NOT NULL PRIMARY KEY,
    owner      VARCHAR(255) NOT NULL,
    expiration INT          NOT NULL,
    INDEX idx_expiration (expiration)
);

CREATE TABLE IF NOT EXISTS jobs (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue        VARCHAR(255) NOT NULL,
    payload      LONGTEXT     NOT NULL,
    attempts     TINYINT UNSIGNED NOT NULL,
    reserved_at  INT UNSIGNED     NULL,
    available_at INT UNSIGNED     NOT NULL,
    created_at   INT UNSIGNED     NOT NULL,
    INDEX idx_queue (queue)
);

CREATE TABLE IF NOT EXISTS job_batches (
    id             VARCHAR(255) NOT NULL PRIMARY KEY,
    name           VARCHAR(255) NOT NULL,
    total_jobs     INT          NOT NULL,
    pending_jobs   INT          NOT NULL,
    failed_jobs    INT          NOT NULL,
    failed_job_ids LONGTEXT     NOT NULL,
    options        MEDIUMTEXT   NULL,
    cancelled_at   INT          NULL,
    created_at     INT          NOT NULL,
    finished_at    INT          NULL
);

CREATE TABLE IF NOT EXISTS failed_jobs (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid       VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT         NOT NULL,
    queue      TEXT         NOT NULL,
    payload    LONGTEXT     NOT NULL,
    exception  LONGTEXT     NOT NULL,
    failed_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email      VARCHAR(255) NOT NULL PRIMARY KEY,
    token      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NULL
);

CREATE TABLE IF NOT EXISTS sessions (
    id            VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id       BIGINT UNSIGNED NULL,
    ip_address    VARCHAR(45)  NULL,
    user_agent    TEXT         NULL,
    payload       LONGTEXT     NOT NULL,
    last_activity INT          NOT NULL,
    INDEX idx_user_id       (user_id),
    INDEX idx_last_activity (last_activity)
);


-- ─────────────────────────────────────────────────────────────────────
-- TABLAS DE APLICACIÓN
-- ─────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS users (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre            VARCHAR(100) NOT NULL,
    email             VARCHAR(100) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP    NULL,
    password          VARCHAR(255) NOT NULL,
    remember_token    VARCHAR(100) NULL,
    estado            BOOLEAN DEFAULT TRUE,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rol (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50)  UNIQUE NOT NULL,
    descripcion VARCHAR(255) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS usuario_rol (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    rol_id     BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id)     REFERENCES rol(id)   ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_rol_id     (rol_id)
);

CREATE TABLE IF NOT EXISTS tipo_calificacion (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50)   UNIQUE NOT NULL,
    descripcion VARCHAR(255)  NULL,
    punteo_max  DECIMAL(5,2)  NOT NULL DEFAULT 100.00,
    orden       TINYINT       NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS sede (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    codigo     VARCHAR(10)  UNIQUE NOT NULL,
    direccion  VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS carrera (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    codigo     VARCHAR(10)  NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sede_carrera (
    sede_id    BIGINT UNSIGNED NOT NULL,
    carrera_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (sede_id, carrera_id),
    FOREIGN KEY (sede_id)    REFERENCES sede(id)    ON DELETE CASCADE,
    FOREIGN KEY (carrera_id) REFERENCES carrera(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS clase (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100)  NOT NULL,
    descripcion TEXT          NULL,
    usuario_id  BIGINT UNSIGNED NOT NULL,
    carrera_id  BIGINT UNSIGNED NULL,
    codigo      VARCHAR(20)   NULL,
    ciclo       TINYINT       NULL COMMENT '1-10',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id),
    FOREIGN KEY (carrera_id) REFERENCES carrera(id) ON DELETE SET NULL,
    INDEX idx_carrera_ciclo (carrera_id, ciclo)
);

CREATE TABLE IF NOT EXISTS clase_catedratico (
    clase_id   BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (clase_id, usuario_id),
    FOREIGN KEY (clase_id)   REFERENCES clase(id)  ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id)  ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS estudiante (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    carnet     VARCHAR(50)  NOT NULL,
    nombre     VARCHAR(100) NOT NULL,
    correo     VARCHAR(100) NULL,
    usuario_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS clase_estudiante (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clase_id      BIGINT UNSIGNED NOT NULL,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (clase_id)      REFERENCES clase(id)      ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiante(id) ON DELETE CASCADE,
    INDEX idx_clase_id      (clase_id),
    INDEX idx_estudiante_id (estudiante_id)
);

CREATE TABLE IF NOT EXISTS asignacion (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    clase_id      BIGINT UNSIGNED NOT NULL,
    anio          SMALLINT        NOT NULL COMMENT 'Año de la asignación',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_asignacion (estudiante_id, clase_id, anio),
    FOREIGN KEY (estudiante_id) REFERENCES estudiante(id) ON DELETE CASCADE,
    FOREIGN KEY (clase_id)      REFERENCES clase(id)      ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS sesion (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clase_id   BIGINT UNSIGNED NOT NULL,
    fecha      DATE            NOT NULL,
    token      VARCHAR(255)    NULL,
    expiracion DATETIME        NULL,
    finalizada BOOLEAN         NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (clase_id) REFERENCES clase(id) ON DELETE CASCADE,
    INDEX idx_clase_id (clase_id)
);

CREATE TABLE IF NOT EXISTS asistencia (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sesion_id     BIGINT UNSIGNED NOT NULL,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    fecha_hora    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    selfie        VARCHAR(255) NULL,
    UNIQUE KEY uq_asistencia (sesion_id, estudiante_id),
    FOREIGN KEY (sesion_id)     REFERENCES sesion(id)     ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiante(id) ON DELETE CASCADE,
    INDEX idx_sesion_id     (sesion_id),
    INDEX idx_estudiante_id (estudiante_id)
);

CREATE TABLE IF NOT EXISTS participacion (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sesion_id     BIGINT UNSIGNED NOT NULL,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    calificacion  DECIMAL(5,2) NULL,
    comentario    TEXT         NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sesion_id)     REFERENCES sesion(id)     ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiante(id) ON DELETE CASCADE,
    INDEX idx_sesion_id     (sesion_id),
    INDEX idx_estudiante_id (estudiante_id)
);

CREATE TABLE IF NOT EXISTS grupo (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sesion_id  BIGINT UNSIGNED NOT NULL,
    nombre     VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sesion_id) REFERENCES sesion(id) ON DELETE CASCADE,
    INDEX idx_sesion_id (sesion_id)
);

CREATE TABLE IF NOT EXISTS grupo_estudiante (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    grupo_id      BIGINT UNSIGNED NOT NULL,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (grupo_id)      REFERENCES grupo(id)      ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiante(id) ON DELETE CASCADE,
    INDEX idx_grupo_id      (grupo_id),
    INDEX idx_estudiante_id (estudiante_id)
);

CREATE TABLE IF NOT EXISTS calificacion (
    id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estudiante_id        BIGINT UNSIGNED NOT NULL,
    clase_id             BIGINT UNSIGNED NOT NULL,
    tipo_calificacion_id BIGINT UNSIGNED NOT NULL,
    nota                 DECIMAL(5,2) NULL,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id)        REFERENCES estudiante(id)        ON DELETE CASCADE,
    FOREIGN KEY (clase_id)             REFERENCES clase(id)             ON DELETE CASCADE,
    FOREIGN KEY (tipo_calificacion_id) REFERENCES tipo_calificacion(id),
    INDEX idx_estudiante_id (estudiante_id),
    INDEX idx_clase_id      (clase_id)
);

CREATE TABLE IF NOT EXISTS actividad (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clase_id   BIGINT UNSIGNED NOT NULL,
    nombre     VARCHAR(100) NOT NULL,
    punteo_max DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    orden      TINYINT      NOT NULL DEFAULT 0,
    FOREIGN KEY (clase_id) REFERENCES clase(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS actividad_nota (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actividad_id BIGINT UNSIGNED NOT NULL,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    nota         DECIMAL(5,2) NULL,
    UNIQUE KEY uq_actividad_nota (actividad_id, estudiante_id),
    FOREIGN KEY (actividad_id)    REFERENCES actividad(id)   ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id)   REFERENCES estudiante(id)  ON DELETE CASCADE
);


-- ─────────────────────────────────────────────────────────────────────
-- DATOS INICIALES (equivalente a php artisan db:seed)
-- ─────────────────────────────────────────────────────────────────────

-- Roles
INSERT INTO rol (nombre, descripcion) VALUES
    ('admin',       'Administrador del sistema'),
    ('catedratico', 'Docente');

-- Tipos de calificación (total = 100 puntos)
INSERT INTO tipo_calificacion (nombre, descripcion, punteo_max, orden) VALUES
    ('Parcial 1',    'Primer examen parcial',   15.00, 1),
    ('Parcial 2',    'Segundo examen parcial',  15.00, 2),
    ('Actividades',  'Tareas y actividades',    25.00, 3),
    ('Proyecto',     'Proyecto del curso',      10.00, 4),
    ('Examen Final', 'Examen final del curso',  35.00, 5);

-- Sedes
INSERT INTO sede (nombre, codigo, direccion) VALUES
    ('Guastatoya', 'GT', 'El Progreso, Guatemala'),
    ('Sanarate',   'SN', 'El Progreso, Guatemala');

-- Carreras
INSERT INTO carrera (nombre, codigo) VALUES
    ('Ingeniería en Sistemas',         'IS'),
    ('Administración de Empresas',     'AE'),
    ('Ciencias Jurídicas y Sociales',  'CJS'),
    ('Contaduría Pública y Auditoría', 'CPA'),
    ('Psicología',                     'PSI'),
    ('Trabajo Social',                 'TS');

-- Sede ↔ Carrera  (Guastatoya = 1, Sanarate = 2 | IS=1 AE=2 CJS=3 CPA=4 PSI=5 TS=6)
INSERT INTO sede_carrera (sede_id, carrera_id) VALUES
    -- Guastatoya: todas
    (1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6),
    -- Sanarate: IS, AE, CJS
    (2, 1), (2, 2), (2, 3);

-- Usuario administrador
-- Contraseña: '123'  (hash bcrypt generado con Hash::make('123') de Laravel)
-- Para regenerar: php artisan tinker --execute="echo \Illuminate\Support\Facades\Hash::make('123');"
INSERT INTO users (nombre, email, password, estado, created_at) VALUES
    ('Administrador', 'admin@classassist.com',
     '$2y$12$QKIf8P7XpG3LzJmJ5A4VCuvLXF6Y9kgMdNjRsI5OBjN2eNKOBcw0K',
     TRUE, NOW());

INSERT INTO usuario_rol (usuario_id, rol_id) VALUES (1, 1);


-- ─────────────────────────────────────────────────────────────────────
-- CLASES — PENSUM UMG
-- ─────────────────────────────────────────────────────────────────────

-- ── IS: Ingeniería en Sistemas (10 ciclos · 50 cursos) ───────────────
INSERT INTO clase (nombre, ciclo, codigo, carrera_id, usuario_id) VALUES
    ('Contabilidad I',                              1, 'IS-01', 1, 1),
    ('Desarrollo Humano y Profesional',             1, 'IS-02', 1, 1),
    ('Introducción a los Sistemas de Cómputo',      1, 'IS-03', 1, 1),
    ('Lógica de Sistemas',                          1, 'IS-04', 1, 1),
    ('Metodología de la Investigación',             1, 'IS-05', 1, 1),
    ('Álgebra Lineal',                              2, 'IS-06', 1, 1),
    ('Algoritmos',                                  2, 'IS-07', 1, 1),
    ('Contabilidad II',                             2, 'IS-08', 1, 1),
    ('Matemática Discreta',                         2, 'IS-09', 1, 1),
    ('Precálculo',                                  2, 'IS-10', 1, 1),
    ('Cálculo I',                                   3, 'IS-11', 1, 1),
    ('Derecho Informático',                         3, 'IS-12', 1, 1),
    ('Física I',                                    3, 'IS-13', 1, 1),
    ('Proceso Administrativo',                      3, 'IS-14', 1, 1),
    ('Programación I',                              3, 'IS-15', 1, 1),
    ('Cálculo II',                                  4, 'IS-16', 1, 1),
    ('Estadística I',                               4, 'IS-17', 1, 1),
    ('Física II',                                   4, 'IS-18', 1, 1),
    ('Microeconomía',                               4, 'IS-19', 1, 1),
    ('Programación II',                             4, 'IS-20', 1, 1),
    ('Electrónica Analógica',                       5, 'IS-21', 1, 1),
    ('Emprendedores de Negocios',                   5, 'IS-22', 1, 1),
    ('Estadística II',                              5, 'IS-23', 1, 1),
    ('Métodos Numéricos',                           5, 'IS-24', 1, 1),
    ('Programación III',                            5, 'IS-25', 1, 1),
    ('Autómatas y Lenguajes Formales',              6, 'IS-26', 1, 1),
    ('Bases de Datos I',                            6, 'IS-27', 1, 1),
    ('Electrónica Digital',                         6, 'IS-28', 1, 1),
    ('Investigación de Operaciones',                6, 'IS-29', 1, 1),
    ('Sistemas Operativos I',                       6, 'IS-30', 1, 1),
    ('Análisis de Sistemas I',                      7, 'IS-31', 1, 1),
    ('Arquitectura de Computadoras I',              7, 'IS-32', 1, 1),
    ('Bases de Datos II',                           7, 'IS-33', 1, 1),
    ('Compiladores',                                7, 'IS-34', 1, 1),
    ('Sistemas Operativos II',                      7, 'IS-35', 1, 1),
    ('Análisis de Sistemas II',                     8, 'IS-36', 1, 1),
    ('Arquitectura de Computadoras II',             8, 'IS-37', 1, 1),
    ('Desarrollo Web',                              8, 'IS-38', 1, 1),
    ('Ética Profesional',                           8, 'IS-39', 1, 1),
    ('Redes de Computadoras I',                     8, 'IS-40', 1, 1),
    ('Administración de Tecnologías de Información',9, 'IS-41', 1, 1),
    ('Ingeniería de Software',                      9, 'IS-42', 1, 1),
    ('Inteligencia Artificial',                     9, 'IS-43', 1, 1),
    ('Proyecto de Graduación I',                    9, 'IS-44', 1, 1),
    ('Redes de Computadoras II',                    9, 'IS-45', 1, 1),
    ('Aseguramiento de la Calidad de Software',    10, 'IS-46', 1, 1),
    ('Proyecto de Graduación II',                  10, 'IS-47', 1, 1),
    ('Seguridad y Auditoría de Sistemas',           10, 'IS-48', 1, 1),
    ('Seminario de Tecnologías de Información',    10, 'IS-49', 1, 1),
    ('Telecomunicaciones',                         10, 'IS-50', 1, 1);

-- ── AE: Administración de Empresas (8 ciclos · 40 cursos) ────────────
INSERT INTO clase (nombre, ciclo, codigo, carrera_id, usuario_id) VALUES
    ('Administración I',                            1, 'AE-01', 2, 1),
    ('Derecho I',                                   1, 'AE-02', 2, 1),
    ('Desarrollo Humano y Profesional',             1, 'AE-03', 2, 1),
    ('Filosofía Administrativa',                    1, 'AE-04', 2, 1),
    ('Lenguaje e Investigación Documental',         1, 'AE-05', 2, 1),
    ('Administración II',                           2, 'AE-06', 2, 1),
    ('Contabilidad I',                              2, 'AE-07', 2, 1),
    ('Derecho II',                                  2, 'AE-08', 2, 1),
    ('Economía General',                            2, 'AE-09', 2, 1),
    ('Matemática I',                                2, 'AE-10', 2, 1),
    ('Administración III',                          3, 'AE-11', 2, 1),
    ('Contabilidad II',                             3, 'AE-12', 2, 1),
    ('Matemática Administrativa',                   3, 'AE-13', 2, 1),
    ('Microeconomía',                               3, 'AE-14', 2, 1),
    ('Psicología Organizacional',                   3, 'AE-15', 2, 1),
    ('Administración IV',                           4, 'AE-16', 2, 1),
    ('Estadística I',                               4, 'AE-17', 2, 1),
    ('Informática I',                               4, 'AE-18', 2, 1),
    ('Macroeconomía',                               4, 'AE-19', 2, 1),
    ('Sociología de los Negocios',                  4, 'AE-20', 2, 1),
    ('Administración de Costos',                    5, 'AE-21', 2, 1),
    ('Administración del Talento Humano',           5, 'AE-22', 2, 1),
    ('Estadística II',                              5, 'AE-23', 2, 1),
    ('Informática II',                              5, 'AE-24', 2, 1),
    ('Mercadotecnia I',                             5, 'AE-25', 2, 1),
    ('Derecho III',                                 6, 'AE-26', 2, 1),
    ('Economía Internacional',                      6, 'AE-27', 2, 1),
    ('Gestión de Estados Financieros',              6, 'AE-28', 2, 1),
    ('Investigación de Operaciones',                6, 'AE-29', 2, 1),
    ('Mercadotecnia II',                            6, 'AE-30', 2, 1),
    ('Administración de Presupuestos',              7, 'AE-31', 2, 1),
    ('Administración Industrial',                   7, 'AE-32', 2, 1),
    ('Matemática Financiera',                       7, 'AE-33', 2, 1),
    ('Mercadotecnia III',                           7, 'AE-34', 2, 1),
    ('Proyecto de Graduación',                      7, 'AE-35', 2, 1),
    ('Administración Financiera',                   8, 'AE-36', 2, 1),
    ('Elaboración y Evaluación de Proyectos',       8, 'AE-37', 2, 1),
    ('Logística Administrativa',                    8, 'AE-38', 2, 1),
    ('Mercadotecnia IV',                            8, 'AE-39', 2, 1),
    ('Seminario de Competencias Gerenciales',       8, 'AE-40', 2, 1);

-- ── CJS: Ciencias Jurídicas y Sociales (10 ciclos · 55 cursos) ───────
INSERT INTO clase (nombre, ciclo, codigo, carrera_id, usuario_id) VALUES
    ('Economía',                                    1, 'CJS-01', 3, 1),
    ('Filosofía',                                   1, 'CJS-02', 3, 1),
    ('Introducción al Derecho I',                   1, 'CJS-03', 3, 1),
    ('Lenguaje y Técnicas de Investigación',        1, 'CJS-04', 3, 1),
    ('Sociología de Guatemala',                     1, 'CJS-05', 3, 1),
    ('Criminología',                                2, 'CJS-06', 3, 1),
    ('Derecho Penal I',                             2, 'CJS-07', 3, 1),
    ('Derecho Romano y Español',                    2, 'CJS-08', 3, 1),
    ('Desarrollo Humano y Profesional',             2, 'CJS-09', 3, 1),
    ('Introducción al Derecho II',                  2, 'CJS-10', 3, 1),
    ('Teoría General del Estado',                   2, 'CJS-11', 3, 1),
    ('Derecho Civil I',                             3, 'CJS-12', 3, 1),
    ('Derecho Constitucional Guatemalteco',         3, 'CJS-13', 3, 1),
    ('Derecho Penal II',                            3, 'CJS-14', 3, 1),
    ('Medicina Forense',                            3, 'CJS-15', 3, 1),
    ('Teoría General del Proceso',                  3, 'CJS-16', 3, 1),
    ('Derecho Administrativo I',                    4, 'CJS-17', 3, 1),
    ('Derecho Ambiental',                           4, 'CJS-18', 3, 1),
    ('Derecho Civil II',                            4, 'CJS-19', 3, 1),
    ('Derecho Penal III',                           4, 'CJS-20', 3, 1),
    ('Derechos Humanos',                            4, 'CJS-21', 3, 1),
    ('Lógica Jurídica y Ética Profesional',         4, 'CJS-22', 3, 1),
    ('Derecho Administrativo II',                   5, 'CJS-23', 3, 1),
    ('Derecho Civil III',                           5, 'CJS-24', 3, 1),
    ('Derecho del Trabajo I',                       5, 'CJS-25', 3, 1),
    ('Derecho Financiero y Tributario',             5, 'CJS-26', 3, 1),
    ('Derecho Procesal Penal I',                    5, 'CJS-27', 3, 1),
    ('Oratoria Forense',                            5, 'CJS-28', 3, 1),
    ('Derecho Civil IV',                            6, 'CJS-29', 3, 1),
    ('Derecho del Trabajo II',                      6, 'CJS-30', 3, 1),
    ('Derecho Mercantil I',                         6, 'CJS-31', 3, 1),
    ('Derecho Procesal Administrativo',             6, 'CJS-32', 3, 1),
    ('Derecho Procesal Penal II',                   6, 'CJS-33', 3, 1),
    ('Clínica Procesal Penal I',                    7, 'CJS-34', 3, 1),
    ('Derecho Civil V',                             7, 'CJS-35', 3, 1),
    ('Derecho Mercantil II',                        7, 'CJS-36', 3, 1),
    ('Derecho Notarial I',                          7, 'CJS-37', 3, 1),
    ('Derecho Procesal del Trabajo',                7, 'CJS-38', 3, 1),
    ('Seminario de Trabajo de Graduación',          7, 'CJS-39', 3, 1),
    ('Clínica Procesal Laboral',                    8, 'CJS-40', 3, 1),
    ('Clínica Procesal Penal II',                   8, 'CJS-41', 3, 1),
    ('Derecho Internacional Público',               8, 'CJS-42', 3, 1),
    ('Derecho Mercantil III',                       8, 'CJS-43', 3, 1),
    ('Derecho Notarial II',                         8, 'CJS-44', 3, 1),
    ('Derecho Procesal Civil y Mercantil I',        8, 'CJS-45', 3, 1),
    ('Clínica Procesal Civil I',                    9, 'CJS-46', 3, 1),
    ('Derecho Notarial III',                        9, 'CJS-47', 3, 1),
    ('Derecho Procesal Civil y Mercantil II',       9, 'CJS-48', 3, 1),
    ('Derecho Procesal Constitucional',             9, 'CJS-49', 3, 1),
    ('Derecho Registral',                           9, 'CJS-50', 3, 1),
    ('Clínica Procesal Civil II',                  10, 'CJS-51', 3, 1),
    ('Derecho Bancario y Bursátil',                10, 'CJS-52', 3, 1),
    ('Derecho Internacional Privado',              10, 'CJS-53', 3, 1),
    ('Derecho Procesal Civil y Mercantil III',     10, 'CJS-54', 3, 1),
    ('Filosofía del Derecho',                      10, 'CJS-55', 3, 1);

-- ── CPA: Contaduría Pública y Auditoría (9 ciclos · 45 cursos) ───────
INSERT INTO clase (nombre, ciclo, codigo, carrera_id, usuario_id) VALUES
    ('Contabilidad Básica',                                          1, 'CPA-01', 4, 1),
    ('Introducción a la Economía',                                   1, 'CPA-02', 4, 1),
    ('Introducción al Derecho',                                      1, 'CPA-03', 4, 1),
    ('Matemática I',                                                 1, 'CPA-04', 4, 1),
    ('Técnicas de Investigación',                                    1, 'CPA-05', 4, 1),
    ('Contabilidad de Sociedades',                                   2, 'CPA-06', 4, 1),
    ('Legislación Mercantil',                                        2, 'CPA-07', 4, 1),
    ('Matemática II',                                                2, 'CPA-08', 4, 1),
    ('Microeconomía I',                                              2, 'CPA-09', 4, 1),
    ('Normas Internacionales de Información Financiera I',           2, 'CPA-10', 4, 1),
    ('Contabilidad Avanzada I',                                      3, 'CPA-11', 4, 1),
    ('Matemática Financiera I',                                      3, 'CPA-12', 4, 1),
    ('Métodos Estadísticos I',                                       3, 'CPA-13', 4, 1),
    ('Microeconomía II',                                             3, 'CPA-14', 4, 1),
    ('Normas Internacionales de Información Financiera II',          3, 'CPA-15', 4, 1),
    ('Contabilidad Avanzada II',                                     4, 'CPA-16', 4, 1),
    ('Desarrollo Humano y Profesional',                              4, 'CPA-17', 4, 1),
    ('Matemática Financiera II',                                     4, 'CPA-18', 4, 1),
    ('Métodos Estadísticos II',                                      4, 'CPA-19', 4, 1),
    ('Teoría Administrativa',                                        4, 'CPA-20', 4, 1),
    ('Auditoría I',                                                  5, 'CPA-21', 4, 1),
    ('Contabilidad de Costos I',                                     5, 'CPA-22', 4, 1),
    ('Finanzas Públicas',                                            5, 'CPA-23', 4, 1),
    ('Legislación Tributaria',                                       5, 'CPA-24', 4, 1),
    ('Normas Internacionales de Auditoría I',                        5, 'CPA-25', 4, 1),
    ('Auditoría II',                                                 6, 'CPA-26', 4, 1),
    ('Contabilidad de Costos II',                                    6, 'CPA-27', 4, 1),
    ('Legislación Laboral',                                          6, 'CPA-28', 4, 1),
    ('Moneda y Banca',                                               6, 'CPA-29', 4, 1),
    ('Normas Internacionales de Auditoría II',                       6, 'CPA-30', 4, 1),
    ('Análisis e Interpretación de Estados Financieros',             7, 'CPA-31', 4, 1),
    ('Auditoría III',                                                7, 'CPA-32', 4, 1),
    ('Ética Profesional',                                            7, 'CPA-33', 4, 1),
    ('Presupuestos',                                                 7, 'CPA-34', 4, 1),
    ('Procedimientos Legales y Administrativos',                     7, 'CPA-35', 4, 1),
    ('Auditoría Administrativa',                                     8, 'CPA-36', 4, 1),
    ('Auditoría de Sistemas de Información',                         8, 'CPA-37', 4, 1),
    ('Contabilidad y Organización Bancaria',                         8, 'CPA-38', 4, 1),
    ('Contabilidades Especiales',                                    8, 'CPA-39', 4, 1),
    ('Redacción de Informes Técnicos',                               8, 'CPA-40', 4, 1),
    ('Administración y Gestión de Riesgos',                          9, 'CPA-41', 4, 1),
    ('Elaboración y Evaluación de Proyectos',                        9, 'CPA-42', 4, 1),
    ('Propedéutica de Tesis',                                        9, 'CPA-43', 4, 1),
    ('Seminario de Auditoría',                                       9, 'CPA-44', 4, 1),
    ('Seminario de Contabilidad',                                    9, 'CPA-45', 4, 1);

-- ── PSI: Psicología (10 ciclos · 44 cursos) ──────────────────────────
INSERT INTO clase (nombre, ciclo, codigo, carrera_id, usuario_id) VALUES
    ('Biología Humana',                                              1, 'PSI-01', 5, 1),
    ('Desarrollo Humano y Profesional',                              1, 'PSI-02', 5, 1),
    ('Filosofía',                                                    1, 'PSI-03', 5, 1),
    ('Sociología General',                                           1, 'PSI-04', 5, 1),
    ('Antropología General',                                         2, 'PSI-05', 5, 1),
    ('Lógica Formal',                                                2, 'PSI-06', 5, 1),
    ('Metodología de la Investigación',                              2, 'PSI-07', 5, 1),
    ('Psicología General',                                           2, 'PSI-08', 5, 1),
    ('Anatomía y Fisiología del Sistema Nervioso',                   3, 'PSI-09', 5, 1),
    ('Estadística Fundamental',                                      3, 'PSI-10', 5, 1),
    ('Psicología Evolutiva del Niño y del Adolescente',              3, 'PSI-11', 5, 1),
    ('Semiología Psicológica',                                       3, 'PSI-12', 5, 1),
    ('Estadística Aplicada a la Psicología',                         4, 'PSI-13', 5, 1),
    ('Psicología Evolutiva del Adulto',                              4, 'PSI-14', 5, 1),
    ('Psicometría I',                                                4, 'PSI-15', 5, 1),
    ('Teorías de la Personalidad',                                   4, 'PSI-16', 5, 1),
    ('Neurofisiología',                                              5, 'PSI-17', 5, 1),
    ('Psicología del Deporte y la Recreación',                       5, 'PSI-18', 5, 1),
    ('Psicología Social',                                            5, 'PSI-19', 5, 1),
    ('Psicometría II',                                               5, 'PSI-20', 5, 1),
    ('Fundamentos de Informática',                                   6, 'PSI-21', 5, 1),
    ('Introducción a la Psicología Forense',                         6, 'PSI-22', 5, 1),
    ('Introducción a la Psicología Industrial Organizacional',       6, 'PSI-23', 5, 1),
    ('Psicología Clínica',                                           6, 'PSI-24', 5, 1),
    ('Fundamentos Teóricos de la Terapia Analítica',                 7, 'PSI-25', 5, 1),
    ('Psiconeuroendocrinología',                                     7, 'PSI-26', 5, 1),
    ('Psicopatología del Adulto I',                                  7, 'PSI-27', 5, 1),
    ('Psicopatología del Niño y del Adolescente',                    7, 'PSI-28', 5, 1),
    ('Sistemas de Psicoterapia',                                     7, 'PSI-29', 5, 1),
    ('Proceso Terapéutico Analítico',                                8, 'PSI-30', 5, 1),
    ('Psicometría III',                                              8, 'PSI-31', 5, 1),
    ('Psicopatología del Adulto II',                                 8, 'PSI-32', 5, 1),
    ('Psicoterapia del Niño y del Adolescente',                      8, 'PSI-33', 5, 1),
    ('Técnicas de Modificación de Conductas',                        8, 'PSI-34', 5, 1),
    ('Elaboración de Trabajo de Graduación I',                       9, 'PSI-35', 5, 1),
    ('Fundamentos de Psicofarmacología',                             9, 'PSI-36', 5, 1),
    ('Modelo Psicoterapéutico Cognitivo Conductual',                 9, 'PSI-37', 5, 1),
    ('Práctica I',                                                   9, 'PSI-38', 5, 1),
    ('Psicoeducación en Trastornos Mentales',                        9, 'PSI-39', 5, 1),
    ('Elaboración de Trabajo de Graduación II',                     10, 'PSI-40', 5, 1),
    ('Modelo Psicoterapéutico Humanístico Existencial',             10, 'PSI-41', 5, 1),
    ('Práctica II',                                                 10, 'PSI-42', 5, 1),
    ('Psicoterapia de Grupo',                                       10, 'PSI-43', 5, 1),
    ('Psicoterapia de Pareja Familiar',                             10, 'PSI-44', 5, 1);

-- ── TS: Trabajo Social (10 ciclos · 40 cursos) ───────────────────────
INSERT INTO clase (nombre, ciclo, codigo, carrera_id, usuario_id) VALUES
    ('Filosofía General',                                            1, 'TS-01', 6, 1),
    ('Herramientas de la Comunicación Social',                       1, 'TS-02', 6, 1),
    ('Historia Política y Social de Guatemala',                      1, 'TS-03', 6, 1),
    ('Origen y Desarrollo de Trabajo Social',                        1, 'TS-04', 6, 1),
    ('Estudio de la Realidad Social de Guatemala',                   2, 'TS-05', 6, 1),
    ('Introducción a las Ciencias Sociales',                         2, 'TS-06', 6, 1),
    ('Lógica',                                                       2, 'TS-07', 6, 1),
    ('Psicología Social',                                            2, 'TS-08', 6, 1),
    ('Estadística Aplicada al Trabajo Social',                       3, 'TS-09', 6, 1),
    ('Investigación Científica Enfocada a las Ciencias Sociales I',  3, 'TS-10', 6, 1),
    ('Metodología para la Intervención Individual y Familiar',       3, 'TS-11', 6, 1),
    ('Teoría del Estado Moderno',                                    3, 'TS-12', 6, 1),
    ('Desarrollo Humano y Profesional',                              4, 'TS-13', 6, 1),
    ('Investigación Científica Enfocada a las Ciencias Sociales II', 4, 'TS-14', 6, 1),
    ('Legislación Social y Derechos Humanos I',                      4, 'TS-15', 6, 1),
    ('Metodología para la Intervención Grupal',                      4, 'TS-16', 6, 1),
    ('Ejercicio Técnico Supervisado I',                              5, 'TS-17', 6, 1),
    ('Formulación de Proyectos',                                     5, 'TS-18', 6, 1),
    ('Legislación Social y Derechos Humanos II',                     5, 'TS-19', 6, 1),
    ('Metodología para la Intervención Comunitaria',                 5, 'TS-20', 6, 1),
    ('Economía Política',                                            6, 'TS-21', 6, 1),
    ('Ejercicio Técnico Supervisado II',                             6, 'TS-22', 6, 1),
    ('Interculturalidad de Guatemala',                               6, 'TS-23', 6, 1),
    ('Seminario de Trabajo Social',                                  6, 'TS-24', 6, 1),
    ('Gerencia Social I',                                            7, 'TS-25', 6, 1),
    ('Investigación y Trabajo Social',                               7, 'TS-26', 6, 1),
    ('Políticas Públicas y Sociales',                                7, 'TS-27', 6, 1),
    ('Principios Filosóficos del Trabajo Social',                    7, 'TS-28', 6, 1),
    ('Campos de Intervención de Trabajo Social',                     8, 'TS-29', 6, 1),
    ('Evaluación y Monitoreo de Proyectos',                          8, 'TS-30', 6, 1),
    ('Gerencia Social II',                                           8, 'TS-31', 6, 1),
    ('Taller: Sistematización de Experiencias de Trabajo Social',    8, 'TS-32', 6, 1),
    ('Antropología Social',                                          9, 'TS-33', 6, 1),
    ('Planificación del Desarrollo Social I',                        9, 'TS-34', 6, 1),
    ('Práctica Profesional I',                                       9, 'TS-35', 6, 1),
    ('Trabajo de Graduación I',                                      9, 'TS-36', 6, 1),
    ('Auditoría Social',                                            10, 'TS-37', 6, 1),
    ('Evaluación y Resolución de Conflictos',                       10, 'TS-38', 6, 1),
    ('Planificación del Desarrollo Social II',                      10, 'TS-39', 6, 1),
    ('Trabajo de Graduación II',                                    10, 'TS-40', 6, 1);


-- ─────────────────────────────────────────────────────────────────────
-- REGISTRO DE MIGRACIONES (para que Laravel no las vuelva a ejecutar)
-- ─────────────────────────────────────────────────────────────────────
INSERT INTO migrations (migration, batch) VALUES
    ('0001_01_01_000000_create_users_table',               1),
    ('0001_01_01_000001_create_cache_table',               1),
    ('0001_01_01_000002_create_jobs_table',                1),
    ('2025_01_01_000001_create_roles_table',               1),
    ('2025_01_01_000002_create_tipos_calificacion_table',  1),
    ('2025_01_01_000003_create_facultades_table',          1),
    ('2025_01_01_000004_create_sedes_table',               1),
    ('2025_01_01_000005_create_carreras_table',            1),
    ('2025_01_01_000006_create_usuario_rol_table',         1),
    ('2025_01_01_000007_create_sede_carrera_table',        1),
    ('2025_01_01_000008_create_clases_table',              1),
    ('2025_01_01_000009_create_estudiantes_table',         1),
    ('2025_01_01_000010_create_clase_estudiante_table',    1),
    ('2025_01_01_000011_create_sesiones_table',            1),
    ('2025_01_01_000012_create_asistencias_table',         1),
    ('2025_01_01_000013_create_participaciones_table',     1),
    ('2025_01_01_000014_create_grupos_table',              1),
    ('2025_01_01_000015_create_grupo_estudiante_table',    1),
    ('2025_01_01_000016_create_calificaciones_table',      1),
    ('2025_01_01_000017_create_actividades_table',         1),
    ('2025_01_01_000018_create_actividad_notas_table',     1),
    ('2025_01_01_000020_create_asignaciones_table',        1),
    ('2025_01_01_000021_create_clase_catedratico_table',   1);


SET FOREIGN_KEY_CHECKS = 1;

