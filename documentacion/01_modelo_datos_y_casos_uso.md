# ClassAssist Pro — Modelo de Datos y Casos de Uso

> Documentación generada directamente del código fuente (migraciones, modelos y componentes Livewire).

---

## 5. Modelo de Datos

### 5.1 `users` — Usuarios del sistema

Almacena todos los usuarios con acceso al sistema (administradores y catedráticos).

| Campo              | Tipo            | Nullable | Descripción                              |
|--------------------|-----------------|----------|------------------------------------------|
| id                 | bigint (PK)     | No       | Identificador único                      |
| nombre             | varchar(100)    | No       | Nombre completo del usuario              |
| email              | varchar(100)    | No       | Correo electrónico (único)               |
| email_verified_at  | timestamp       | Sí       | Fecha de verificación de correo          |
| password           | varchar         | No       | Contraseña hasheada (bcrypt)             |
| estado             | boolean         | No       | Cuenta activa (true) o inactiva (false)  |
| remember_token     | varchar         | Sí       | Token de sesión persistente              |
| created_at         | timestamp       | Sí       | Fecha de creación                        |
| updated_at         | timestamp       | Sí       | Última actualización                     |
| deleted_at         | timestamp       | Sí       | Soft delete                              |

**Relaciones:**
- `roles` → N:M con `rol` a través de `usuario_rol`
- `clases` → 1:N con `clase` (clases creadas por este usuario)
- `clasesImpartidas` → N:M con `clase` a través de `clase_catedratico`
- `estudiante` → 1:1 con `estudiante` (si el usuario también es estudiante)

---

### 5.2 `rol` — Roles del sistema

Define los roles disponibles en el sistema.

| Campo       | Tipo         | Nullable | Descripción                            |
|-------------|--------------|----------|----------------------------------------|
| id          | bigint (PK)  | No       | Identificador único                    |
| nombre      | varchar(50)  | No       | Nombre del rol (único): admin, catedratico, estudiante |
| descripcion | varchar(255) | Sí       | Descripción del rol                    |
| created_at  | timestamp    | Sí       | Fecha de creación                      |

**Relaciones:**
- `usuarios` → N:M con `users` a través de `usuario_rol`

---

### 5.3 `usuario_rol` — Pivote Usuarios-Roles _(N:M)_

| Campo      | Tipo        | Nullable | Descripción                    |
|------------|-------------|----------|--------------------------------|
| id         | bigint (PK) | No       | Identificador único            |
| usuario_id | bigint (FK) | No       | Referencia a `users.id`        |
| rol_id     | bigint (FK) | No       | Referencia a `rol.id`          |
| created_at | timestamp   | Sí       | Fecha de asignación            |

> Ambas claves foráneas tienen `cascadeOnDelete`.

---

### 5.4 `sede` — Sedes / Campus

Representa las ubicaciones físicas de la institución.

| Campo     | Tipo          | Nullable | Descripción                     |
|-----------|---------------|----------|---------------------------------|
| id        | bigint (PK)   | No       | Identificador único             |
| nombre    | varchar(100)  | No       | Nombre de la sede               |
| codigo    | varchar(10)   | No       | Código único de la sede         |
| direccion | varchar(255)  | Sí       | Dirección física                |
| created_at | timestamp    | Sí       | Fecha de creación               |
| updated_at | timestamp    | Sí       | Última actualización            |

**Relaciones:**
- `carreras` → N:M con `carrera` a través de `sede_carrera`

---

### 5.5 `carrera` — Carreras universitarias

| Campo      | Tipo         | Nullable | Descripción                       |
|------------|--------------|----------|-----------------------------------|
| id         | bigint (PK)  | No       | Identificador único               |
| nombre     | varchar(100) | No       | Nombre de la carrera              |
| codigo     | varchar(10)  | Sí       | Código de la carrera              |
| created_at | timestamp    | Sí       | Fecha de creación                 |
| updated_at | timestamp    | Sí       | Última actualización              |

**Relaciones:**
- `sedes` → N:M con `sede` a través de `sede_carrera`
- `clases` → 1:N con `clase`

---

### 5.6 `sede_carrera` — Pivote Sede-Carrera _(N:M)_

| Campo      | Tipo        | Nullable | Descripción                 |
|------------|-------------|----------|-----------------------------|
| sede_id    | bigint (FK) | No       | Referencia a `sede.id`      |
| carrera_id | bigint (FK) | No       | Referencia a `carrera.id`   |

> Llave primaria compuesta: `(sede_id, carrera_id)`. Ambas con `cascadeOnDelete`.

---

### 5.7 `tipo_calificacion` — Tipos de calificación

Define las categorías de evaluación (parciales, actividades, participación, etc.).

| Campo       | Tipo           | Nullable | Descripción                              |
|-------------|----------------|----------|------------------------------------------|
| id          | bigint (PK)    | No       | Identificador único                      |
| nombre      | varchar(50)    | No       | Nombre único del tipo                    |
| descripcion | varchar(255)   | Sí       | Descripción del tipo                     |
| punteo_max  | decimal(5,2)   | No       | Puntaje máximo (default 100.00)          |
| orden       | tinyint        | No       | Orden de visualización (default 0)       |

> Sin timestamps. El tipo con nombre `actividades` recibe tratamiento especial en el cálculo de notas.

**Relaciones:**
- `calificaciones` → 1:N con `calificacion`

---

### 5.8 `clase` — Clases / Cursos

Entidad central del sistema académico.

| Campo                   | Tipo                         | Nullable | Descripción                                          |
|-------------------------|------------------------------|----------|------------------------------------------------------|
| id                      | bigint (PK)                  | No       | Identificador único                                  |
| nombre                  | varchar(100)                 | No       | Nombre del curso                                     |
| descripcion             | text                         | Sí       | Descripción del curso                                |
| usuario_id              | bigint (FK)                  | No       | Catedrático principal (→ `users.id`)                 |
| carrera_id              | bigint (FK)                  | Sí       | Carrera a la que pertenece (→ `carrera.id`)          |
| codigo                  | varchar(20)                  | Sí       | Código del curso                                     |
| ciclo                   | tinyint                      | Sí       | Ciclo académico (1-10); impares = Ene-Jun, pares = Jul-Dic |
| token_inscripcion       | varchar(255)                 | Sí       | Token QR para auto-inscripción de estudiantes        |
| expiracion_inscripcion  | timestamp                    | Sí       | Vencimiento del QR de inscripción (vigencia 24 h)    |
| created_at              | timestamp                    | Sí       |                                                      |
| updated_at              | timestamp                    | Sí       |                                                      |
| deleted_at              | timestamp                    | Sí       | Soft delete                                          |

**Relaciones:**
- `catedratico` → N:1 con `users` (catedrático principal)
- `carrera` → N:1 con `carrera`
- `catedraticos` → N:M con `users` a través de `clase_catedratico`
- `estudiantes` → N:M con `estudiante` a través de `clase_estudiante`
- `sesiones` → 1:N con `sesion`
- `calificaciones` → 1:N con `calificacion`
- `actividades` → 1:N con `actividad` (ordenadas por `orden`)
- `asignaciones` → 1:N con `asignacion`

---

### 5.9 `clase_catedratico` — Pivote Clase-Catedrático _(N:M)_

Permite asignar múltiples catedráticos a una misma clase.

| Campo      | Tipo        | Nullable | Descripción                   |
|------------|-------------|----------|-------------------------------|
| clase_id   | bigint (FK) | No       | Referencia a `clase.id`       |
| usuario_id | bigint (FK) | No       | Referencia a `users.id`       |

> Llave primaria compuesta: `(clase_id, usuario_id)`.

---

### 5.10 `estudiante` — Estudiantes

| Campo      | Tipo         | Nullable | Descripción                                       |
|------------|--------------|----------|---------------------------------------------------|
| id         | bigint (PK)  | No       | Identificador único                               |
| carnet     | varchar(50)  | No       | Carné del estudiante — formato `\d{4}-\d{2}-\d+` (ej. 8590-21-16653) |
| nombre     | varchar(100) | No       | Nombre completo                                   |
| correo     | varchar(100) | Sí       | Correo institucional — debe terminar en `@miumg.edu.gt` |
| usuario_id | bigint (FK)  | Sí       | Vinculación con cuenta de usuario (→ `users.id`)  |
| created_at | timestamp    | Sí       |                                                   |
| updated_at | timestamp    | Sí       |                                                   |
| deleted_at | timestamp    | Sí       | Soft delete                                       |

> **Reglas de negocio:** el carné tiene tres partes separadas por guiones: código de carrera (4 dígitos) + año de ingreso (2 dígitos) + número de estudiante (1 o más dígitos). El correo, si se proporciona, debe ser del dominio institucional `@miumg.edu.gt`. Ambas reglas se aplican en el modal individual, la importación Excel y el QR de inscripción.

**Relaciones:**
- `clases` → N:M con `clase` a través de `clase_estudiante`
- `asistencias` → 1:N con `asistencia`
- `participaciones` → 1:N con `participacion`
- `grupos` → N:M con `grupo` a través de `grupo_estudiante`
- `calificaciones` → 1:N con `calificacion`
- `asignaciones` → 1:N con `asignacion`
- `usuario` → N:1 con `users`

---

### 5.11 `clase_estudiante` — Pivote Clase-Estudiante _(N:M)_

Registra la inscripción de un estudiante en una clase.

| Campo        | Tipo        | Nullable | Descripción                     |
|--------------|-------------|----------|---------------------------------|
| id           | bigint (PK) | No       | Identificador único             |
| clase_id     | bigint (FK) | No       | Referencia a `clase.id`         |
| estudiante_id | bigint (FK) | No      | Referencia a `estudiante.id`    |
| created_at   | timestamp   | Sí       |                                 |
| updated_at   | timestamp   | Sí       |                                 |

---

### 5.12 `sesion` — Sesiones de clase

Cada sesión representa una clase impartida en un día determinado.

| Campo      | Tipo          | Nullable | Descripción                                        |
|------------|---------------|----------|----------------------------------------------------|
| id         | bigint (PK)   | No       | Identificador único                                |
| clase_id   | bigint (FK)   | No       | Referencia a `clase.id`                            |
| fecha      | date          | No       | Fecha de la sesión                                 |
| token      | varchar(255)  | Sí       | Token QR para registro de asistencia               |
| expiracion | datetime      | Sí       | Vencimiento del token QR                           |
| finalizada | boolean       | No       | Indica si la sesión está cerrada (default false)   |
| created_at | timestamp     | Sí       |                                                    |
| updated_at | timestamp     | Sí       |                                                    |
| deleted_at | timestamp     | Sí       | Soft delete                                        |

**Método especial:** `esOperativa(): bool` → retorna `true` solo si `finalizada = false` y `fecha = hoy`.

**Relaciones:**
- `clase` → N:1 con `clase`
- `asistencias` → 1:N con `asistencia`
- `participaciones` → 1:N con `participacion`
- `grupos` → 1:N con `grupo`
- `estadisticasRuido` → 1:N con `estadistica_ruido`

---

### 5.13 `asistencia` — Registro de asistencia

| Campo        | Tipo            | Nullable | Descripción                                  |
|--------------|-----------------|----------|----------------------------------------------|
| id           | bigint (PK)     | No       | Identificador único                          |
| sesion_id    | bigint (FK)     | No       | Referencia a `sesion.id`                     |
| estudiante_id | bigint (FK)    | No       | Referencia a `estudiante.id`                 |
| fecha_hora   | timestamp       | No       | Momento exacto de registro (useCurrent)      |
| selfie       | varchar(255)    | Sí       | Ruta al archivo de selfie en storage         |
| latitud      | decimal(10,7)   | Sí       | Coordenada GPS — latitud                     |
| longitud     | decimal(10,7)   | Sí       | Coordenada GPS — longitud                    |

> **Sin timestamps** (`created_at`/`updated_at`). Restricción única: `(sesion_id, estudiante_id)`.

---

### 5.14 `participacion` — Participaciones (Ruleta)

| Campo        | Tipo           | Nullable | Descripción                            |
|--------------|----------------|----------|----------------------------------------|
| id           | bigint (PK)    | No       | Identificador único                    |
| sesion_id    | bigint (FK)    | No       | Referencia a `sesion.id`               |
| estudiante_id | bigint (FK)   | No       | Referencia a `estudiante.id`           |
| calificacion | decimal(5,2)   | Sí       | Nota asignada (rango 0-10)             |
| comentario   | text           | Sí       | Observación del catedrático            |
| created_at   | timestamp      | Sí       |                                        |
| updated_at   | timestamp      | Sí       |                                        |

---

### 5.15 `grupo` — Grupos de trabajo

| Campo       | Tipo         | Nullable | Descripción                                              |
|-------------|--------------|----------|----------------------------------------------------------|
| id          | bigint (PK)  | No       | Identificador único                                      |
| sesion_id   | bigint (FK)  | No       | Referencia a `sesion.id`                                 |
| nombre      | varchar(50)  | Sí       | Nombre del grupo (ej. "Grupo 1")                         |
| descripcion | varchar(255) | Sí       | Descripción de la actividad realizada por el grupo       |
| created_at  | timestamp    | Sí       |                                                          |
| updated_at  | timestamp    | Sí       |                                                          |

> El campo `descripcion` es compartido por todos los grupos de una misma sesión (se ingresa una sola vez al guardar) y sirve para documentar qué realizaron en el historial.

**Relaciones:**
- `sesion` → N:1 con `sesion`
- `estudiantes` → N:M con `estudiante` a través de `grupo_estudiante`

---

### 5.16 `grupo_estudiante` — Pivote Grupo-Estudiante _(N:M)_

| Campo        | Tipo        | Nullable | Descripción                    |
|--------------|-------------|----------|--------------------------------|
| id           | bigint (PK) | No       | Identificador único            |
| grupo_id     | bigint (FK) | No       | Referencia a `grupo.id`        |
| estudiante_id | bigint (FK) | No      | Referencia a `estudiante.id`   |
| created_at   | timestamp   | Sí       |                                |
| updated_at   | timestamp   | Sí       |                                |

---

### 5.17 `calificacion` — Calificaciones por tipo

| Campo               | Tipo          | Nullable | Descripción                              |
|---------------------|---------------|----------|------------------------------------------|
| id                  | bigint (PK)   | No       | Identificador único                      |
| estudiante_id       | bigint (FK)   | No       | Referencia a `estudiante.id`             |
| clase_id            | bigint (FK)   | No       | Referencia a `clase.id`                  |
| tipo_calificacion_id | bigint (FK)  | No       | Referencia a `tipo_calificacion.id`      |
| nota                | decimal(5,2)  | Sí       | Calificación obtenida                    |
| created_at          | timestamp     | Sí       | Fecha de registro                        |

> **Sin `updated_at`**; las calificaciones se registran como snapshots.

---

### 5.18 `actividad` — Actividades evaluadas

| Campo          | Tipo           | Nullable | Descripción                                       |
|----------------|----------------|----------|---------------------------------------------------|
| id             | bigint (PK)    | No       | Identificador único                               |
| clase_id       | bigint (FK)    | No       | Referencia a `clase.id`                           |
| grupo_sesion_id | bigint (FK)   | Sí       | Si no es null, la actividad es grupal (→ `sesion.id`) |
| nombre         | varchar(100)   | No       | Nombre de la actividad                            |
| punteo_max     | decimal(5,2)   | No       | Puntaje máximo (default 100.00)                   |
| orden          | tinyint        | No       | Orden de aparición en la lista                    |

> **Sin timestamps**. Método `esGrupal(): bool` → retorna `true` si `grupo_sesion_id != null`.

---

### 5.19 `actividad_nota` — Notas por actividad

| Campo        | Tipo          | Nullable | Descripción                               |
|--------------|---------------|----------|-------------------------------------------|
| id           | bigint (PK)   | No       | Identificador único                       |
| actividad_id | bigint (FK)   | No       | Referencia a `actividad.id`               |
| estudiante_id | bigint (FK)  | No       | Referencia a `estudiante.id`              |
| grupo_id     | bigint (FK)   | Sí       | Si la actividad es grupal (→ `grupo.id`)  |
| nota         | decimal(5,2)  | Sí       | Nota obtenida                             |

> **Sin timestamps**. Restricción única: `(actividad_id, estudiante_id)`.

---

### 5.20 `asignacion` — Inscripciones formales

Registro histórico de inscripciones de estudiantes por año académico.

| Campo        | Tipo          | Nullable | Descripción                                        |
|--------------|---------------|----------|----------------------------------------------------|
| id           | bigint (PK)   | No       | Identificador único                                |
| estudiante_id | bigint (FK)  | No       | Referencia a `estudiante.id`                       |
| clase_id     | bigint (FK)   | No       | Referencia a `clase.id`                            |
| anio         | smallint      | No       | Año académico de inscripción                       |
| created_at   | timestamp     | Sí       |                                                    |
| updated_at   | timestamp     | Sí       |                                                    |

> Restricción única: `(estudiante_id, clase_id, anio)` — permite reinscripción en años distintos.

---

### 5.21 `bitacora` — Auditoría del sistema

Registro completo de todas las acciones realizadas en el sistema.

| Campo            | Tipo                                | Nullable | Descripción                              |
|------------------|-------------------------------------|----------|------------------------------------------|
| id               | bigint (PK)                         | No       | Identificador único                      |
| usuario_id       | bigint (FK)                         | Sí       | Usuario que realizó la acción            |
| accion           | varchar(50)                         | No       | Tipo: login, logout, crear, editar, eliminar, exportar |
| modulo           | varchar(100)                        | No       | Módulo afectado: Clase, Estudiante, etc. |
| entidad_id       | bigint                              | Sí       | ID del registro afectado                 |
| descripcion      | text                                | No       | Descripción legible de la acción         |
| datos_anteriores | json                                | Sí       | Estado previo del registro               |
| datos_nuevos     | json                                | Sí       | Nuevo estado del registro                |
| ip               | varchar(45)                         | Sí       | Dirección IP del solicitante             |
| user_agent       | text                                | Sí       | Navegador/cliente usado                  |
| nivel            | enum('info','advertencia','error')  | No       | Severidad del evento (default 'info')    |
| created_at       | timestamp                           | Sí       |                                          |
| updated_at       | timestamp                           | Sí       |                                          |

> Índices en: `(modulo, entidad_id)`, `accion`, `usuario_id`, `created_at`.

---

### 5.22 `estadistica_ruido` — Estadísticas del medidor de ruido

| Campo               | Tipo               | Nullable | Descripción                              |
|---------------------|--------------------|----------|------------------------------------------|
| id                  | bigint (PK)        | No       | Identificador único                      |
| sesion_id           | bigint (FK)        | No       | Referencia a `sesion.id`                 |
| usuario_id          | bigint (FK)        | Sí       | Usuario que activó el medidor            |
| db_minimo           | decimal(5,1)       | No       | Decibelios mínimos registrados           |
| db_maximo           | decimal(5,1)       | No       | Decibelios máximos registrados           |
| db_promedio         | decimal(5,1)       | No       | Promedio de decibelios                   |
| total_alertas       | smallint unsigned  | No       | Número de alertas disparadas             |
| umbral_db           | smallint unsigned  | No       | Umbral configurado (default 65 dB)       |
| duracion_segundos   | int unsigned       | No       | Duración total de la medición            |
| nivel_predominante  | varchar(20)        | Sí       | silencio, bajo, moderado, alto, muy_alto |
| iniciado_en         | timestamp          | Sí       | Inicio de la medición                    |
| finalizado_en       | timestamp          | Sí       | Fin de la medición                       |
| created_at          | timestamp          | Sí       |                                          |
| updated_at          | timestamp          | Sí       |                                          |

---

### 5.23 Diagrama de relaciones resumido

```
users ─────────── usuario_rol ─────────── rol
  │
  ├── clase (usuario_id) ─── clase_catedratico ─── users
  │     │
  │     ├── clase_estudiante ─── estudiante
  │     │         └── grupo_estudiante ─── grupo ─── sesion
  │     │
  │     ├── sesion ─── asistencia
  │     │         ├── participacion
  │     │         ├── grupo
  │     │         └── estadistica_ruido
  │     │
  │     ├── calificacion ─── tipo_calificacion
  │     ├── actividad ─── actividad_nota
  │     └── asignacion
  │
sede ─── sede_carrera ─── carrera ─── clase
```

---

## 6. Casos de Uso

### CU-01: Iniciar sesión en el sistema

**Actor:** Administrador / Catedrático  
**Descripción:** El usuario accede al sistema con sus credenciales.

**Flujo principal:**
1. El usuario navega a `/login`.
2. Ingresa correo electrónico y contraseña.
3. El sistema valida las credenciales y el estado de la cuenta (`estado = true`).
4. El sistema verifica el correo electrónico (`email_verified_at`).
5. Se registra el evento en la **Bitácora** (`accion: login`).
6. El usuario es redirigido al dashboard según su rol.

**Flujo alterno:**
- Credenciales incorrectas → mensaje de error, no se registra en bitácora.
- Cuenta inactiva (`estado = false`) → acceso denegado.
- Correo no verificado → redirección a pantalla de verificación.

**Resultado esperado:** Usuario autenticado con acceso a su panel correspondiente.

---

### CU-02: Gestionar usuarios del sistema

**Actor:** Administrador  
**Descripción:** El administrador crea, edita, activa/desactiva y asigna roles a los usuarios.

**Flujo principal:**
1. El admin accede a `Admin > Gestión de Usuarios`.
2. Visualiza la lista de usuarios con filtro de búsqueda.
3. Hace clic en **Nuevo Usuario**.
4. Completa: nombre, correo, contraseña, rol.
5. Si el rol es `catedratico`, puede asignar clases mediante filtros cascada (Sede → Carrera → Clase).
6. El sistema valida unicidad de correo y máximo 6 clases por catedrático.
7. Se guarda el usuario y se registra en bitácora.

**Flujo alterno:**
- Editar usuario existente: mismo modal con datos precargados.
- Eliminar: soft delete; no puede eliminarse a sí mismo.

**Resultado esperado:** Usuario creado/modificado, acceso al sistema configurado correctamente.

---

### CU-03: Gestionar sedes y carreras

**Actor:** Administrador  
**Descripción:** El administrador registra sedes, carreras y sus asociaciones.

**Flujo principal:**
1. Accede a `Admin > Sedes`.
2. Crea una sede con nombre, código y dirección.
3. Asocia carreras a la sede mediante checkboxes.
4. Guarda los cambios (sincronización N:M en `sede_carrera`).

**Resultado esperado:** Sede registrada con sus carreras asociadas, disponible para asignar clases.

---

### CU-04: Crear y gestionar clases

**Actor:** Administrador / Catedrático  
**Descripción:** Crear una clase con su información académica y configuración de evaluación.

**Flujo principal:**
1. El usuario accede a `Clases`.
2. Hace clic en **Nueva Clase**.
3. Ingresa nombre, carrera, código, ciclo académico.
4. El sistema crea la clase con `usuario_id = auth()->id()`.

**Flujo alterno:**
- Admin ve todas las clases; catedrático solo ve las propias.
- Edición: modal con datos precargados.
- Eliminación: soft delete, se registra en bitácora.

**Resultado esperado:** Clase creada y disponible para inscripción de estudiantes y sesiones.

---

### CU-05: Gestionar estudiantes

**Actor:** Administrador / Catedrático  
**Descripción:** Registrar estudiantes en el sistema e inscribirlos en clases.

**Flujo principal (Catedrático):**
1. Accede a `Estudiantes`, selecciona una clase.
2. Agrega un estudiante individualmente (carné, nombre, correo), importa desde Excel, o genera un **QR de inscripción** para que los estudiantes se registren solos.
3. El sistema valida formato de carné (`\d{4}-\d{2}-\d+`) y dominio de correo (`@miumg.edu.gt`), además de duplicados dentro de la clase.
4. El estudiante queda inscrito en la clase (`clase_estudiante`).

**Flujo principal (Administrador):**
1. Accede a `Estudiantes` → vista global.
2. Busca por nombre, carné o correo.
3. Visualiza todas las clases a las que pertenece cada estudiante (badges).
4. Puede editar o eliminar estudiantes globalmente.

**Flujo alterno:**
- Importación Excel: formato con columnas Carné, Estudiante, Correo Electrónico. Carné debe tener formato `0000-00-0000` y correo debe ser `@miumg.edu.gt`.
- Errores de importación se muestran fila por fila.
- QR de inscripción: genera un token de 40 caracteres con vigencia de 24 horas almacenado en `clase.token_inscripcion`. Ver CU-14.

**Resultado esperado:** Estudiantes registrados e inscritos correctamente.

---

### CU-06: Abrir una sesión de clase

**Actor:** Administrador / Catedrático  
**Descripción:** Iniciar una sesión de clase para el día actual.

**Flujo principal:**
1. El usuario accede a `Sesiones` y hace clic en **Nueva Sesión**.
2. **Catedrático:** selecciona la clase desde un modal simple.
3. **Administrador:** selecciona Sede → Carrera → Clase en cascada.
4. El sistema verifica que no exista ya una sesión abierta para esa clase hoy.
5. Si el catedrático ya tiene una sesión abierta en otra clase, se bloquea.
6. Se crea la sesión con `fecha = hoy`, `finalizada = false`.

**Flujo alterno:**
- Ya existe sesión abierta hoy → mensaje de advertencia.
- Intentar abrir segunda sesión → bloqueado para catedráticos.

**Resultado esperado:** Sesión activa del día disponible para registrar asistencia y actividades.

---

### CU-07: Registro de asistencia por QR

**Actor:** Catedrático (genera QR) / Estudiante (escanea)  
**Descripción:** El catedrático genera un código QR; el estudiante lo escanea para registrar su asistencia.

**Flujo principal:**
1. El catedrático accede a `Pantalla de Clase > QR`.
2. Hace clic en **Generar QR** → se crea un token de 40 caracteres con vencimiento de 5 minutos.
3. El QR muestra la URL `/asistir/{token}`.
4. El estudiante accede a la URL desde su dispositivo.
5. Ingresa su número de carné.
6. La cámara captura una **selfie** (opcional).
7. El dispositivo captura coordenadas GPS.
8. El sistema registra la asistencia en la tabla `asistencia`.

**Flujo alterno:**
- Token expirado → pantalla de error "QR vencido, solicita uno nuevo".
- Carné no inscrito → error "No estás inscrito en esta clase".
- Ya registrado → error "Ya registraste tu asistencia hoy".
- Sesión finalizada → error "La sesión ya cerró".

**Resultado esperado:** Asistencia registrada con timestamp, selfie y coordenadas GPS.

---

### CU-08: Registro manual de asistencia

**Actor:** Catedrático  
**Descripción:** El catedrático marca manualmente la asistencia de un estudiante.

**Flujo principal:**
1. En la pantalla de asistencia, visualiza la lista de estudiantes de la clase.
2. Hace clic en el botón de marcar junto al nombre del estudiante.
3. El sistema crea el registro de asistencia sin selfie ni GPS.

**Flujo alterno:**
- Quitar asistencia: disponible si la sesión no está finalizada.

**Resultado esperado:** Asistencia registrada manualmente en el sistema.

---

### CU-09: Participación aleatoria (Ruleta)

**Actor:** Catedrático  
**Descripción:** Seleccionar aleatoriamente a un estudiante presente para participar.

**Flujo principal:**
1. El catedrático accede a `Pantalla de Clase > Ruleta`.
2. Hace clic en **Girar** → el sistema selecciona aleatoriamente un estudiante con asistencia registrada.
3. Se despliega el nombre del ganador con animación.
4. El catedrático puede asignar una calificación (0-10) y un comentario.
5. Guarda la participación → registro en tabla `participacion`.
6. Puede omitir sin guardar calificación.

**Flujo alterno:**
- Sesión no operativa → las acciones están bloqueadas.
- Sin asistentes registrados → no se puede girar.

**Resultado esperado:** Participación registrada; los puntos extras acumulados contribuyen a la calificación del estudiante.

---

### CU-10: Generar grupos de trabajo

**Actor:** Catedrático  
**Descripción:** Generar grupos aleatorios optimizados para minimizar repetición de pares, opcionalmente ligados a una actividad grupal.

**Flujo principal:**
1. El catedrático accede a `Pantalla de Clase > Grupos`.
2. Elige el modo: por número de grupos o por tamaño de grupo.
3. Ingresa la cantidad deseada y una **descripción opcional** de la actividad (se almacena en `grupo.descripcion` y aparece en el historial).
4. Hace clic en **Generar** → el algoritmo:
   - Construye una matriz de co-ocurrencia (cuántas veces han estado juntos cada par).
   - Ejecuta 30 intentos aleatorios con búsqueda local.
   - Selecciona la distribución con menor puntuación de repetición.
5. Revisa el preview de grupos generados.
6. Hace clic en **Guardar** para confirmar.
7. Al guardar, aparece un modal **¿Crear actividad grupal?** donde puede:
   - Ingresar nombre y punteo máximo de la actividad → se crea en `actividad` con `grupo_sesion_id = sesion.id`.
   - Hacer clic en **Omitir** para crear la actividad más tarde desde el panel de Desempeño.

**Flujo alterno:**
- Regenerar: repite el proceso con nueva aleatoriedad.
- Sesión no operativa → bloqueado.

**Resultado esperado:** Grupos guardados en `grupo` y `grupo_estudiante`, con descripción visible en el historial. Si se creó actividad, queda disponible para calificar en Desempeño.

---

### CU-11: Registrar calificaciones

**Actor:** Catedrático  
**Descripción:** Ingresar notas por tipo de calificación (parciales, actividades, etc.).

**Flujo principal (calificaciones fijas):**
1. Accede a `Calificaciones`, selecciona una clase.
2. Elige la pestaña del tipo de calificación (ej. Examen Parcial).
3. Ingresa las notas de cada estudiante.
4. Hace clic en **Guardar** → el sistema valida rango (0 - punteo_max).

**Flujo principal (actividades):**
1. En la pestaña **Actividades**, crea actividades individuales o grupales.
2. Ingresa notas por estudiante o por grupo (se propagan automáticamente a todos los miembros).
3. Puede importar notas desde Excel o descargar plantilla.

**Cálculo de actividades:**
- Todas las actividades se califican sobre 100 puntos.
- La nota del tipo "Actividades" = promedio de todas las actividades × punteo del tipo.
- Puntos extra por participación de ruleta: hasta 5 pts adicionales al total.

**Resultado esperado:** Calificaciones guardadas; el resumen muestra la nota final calculada y estado (aprobado/reprobado ≥61).

> **Importante:** Una vez guardada una nota por el catedrático, solo el administrador puede modificarla.

---

### CU-12: Finalizar sesión

**Actor:** Catedrático / Sistema (automático)  
**Descripción:** Cerrar una sesión de clase para impedir más registros.

**Flujo principal:**
1. El catedrático hace clic en **Finalizar** en la lista de sesiones.
2. El sistema marca `finalizada = true` y limpia el token QR.
3. Todas las funcionalidades de la sesión quedan bloqueadas.

**Flujo alterno (automático):**
- El scheduler de Laravel ejecuta a las 23:59 y finaliza automáticamente todas las sesiones del día anterior que no fueron cerradas manualmente.

**Resultado esperado:** Sesión cerrada; no se pueden registrar más asistencias, participaciones ni grupos.

---

### CU-13: Consultar estadísticas de sesión

**Actor:** Administrador / Catedrático  
**Descripción:** Ver el resumen completo de una sesión finalizada.

**Flujo principal:**
1. En la lista de sesiones, hace clic en **Estadísticas** de una sesión finalizada.
2. El sistema muestra:
   - KPIs: inscritos, presentes (%), ausentes (%), participaciones, grupos, ruido.
   - Lista de presentes con selfie (click para ampliar) y hora de registro.
   - Lista de ausentes.
   - Tabla de participaciones con calificaciones.
   - Grupos generados con sus integrantes.
   - Estadísticas de ruido (si se usó el medidor).
   - Mapa de Google Maps con marcadores GPS de cada asistencia.

**Resultado esperado:** Vista completa del comportamiento de la sesión para análisis del catedrático.

---

### CU-14: Inscripción de estudiantes por QR

**Actor:** Catedrático (genera QR) / Estudiante (escanea)
**Descripción:** El catedrático genera un QR de inscripción para que los estudiantes se den de alta en la clase sin intervención del catedrático.

**Flujo principal:**
1. El catedrático selecciona una clase en `Estudiantes`.
2. Hace clic en **QR Inscripción** → el sistema genera un token de 40 caracteres con vencimiento de 24 horas, almacenado en `clase.token_inscripcion` / `clase.expiracion_inscripcion`.
3. Se muestra el código QR en SVG y la URL directa `/inscribirse/{token}`.
4. El estudiante escanea el QR o visita la URL desde cualquier dispositivo.
5. En el formulario público completa:
   - **Carné** — formato `\d{4}-\d{2}-\d+` (ej. 8590-21-16653)
   - **Nombre completo**
   - **Correo institucional** — debe terminar en `@miumg.edu.gt`
6. El sistema valida el formato de carné, el dominio del correo, que el token no haya expirado y que el estudiante no esté ya inscrito en la clase.
7. Se ejecuta `Estudiante::firstOrCreate` y `clase->estudiantes()->syncWithoutDetaching`.
8. Confirmación de inscripción exitosa en pantalla.

**Flujo alterno:**
- Token expirado → pantalla de error "QR ha expirado, solicita uno nuevo".
- Token inválido → pantalla de error "QR no válido".
- Carné con formato incorrecto → error de validación con mensaje descriptivo.
- Correo fuera del dominio `@miumg.edu.gt` → error "debe ser correo institucional".
- Carné ya inscrito en la clase → error "ya estás registrado".
- Correo ya inscrito en la clase → error en campo correo.
- El catedrático puede **Regenerar** el QR en cualquier momento (invalida el token anterior).

**Notas técnicas:**
- Ruta pública (sin autenticación): `GET /inscribirse/{token}`
- Componente Livewire: `App\Livewire\Estudiantes\Inscribirse`
- Vista pública: `resources/views/estudiantes/inscribirse.blade.php`
- El QR de inscripción es independiente del QR de asistencia (tabla `clase` vs tabla `sesion`).

**Resultado esperado:** Estudiante inscrito en la clase sin que el catedrático tenga que agregar el registro manualmente.

---

### CU-15: Analizar desempeño de estudiantes

**Actor:** Administrador / Catedrático  
**Descripción:** Visualizar un ranking de desempeño basado en asistencia, participación y notas.

**Flujo principal (Catedrático):**
1. Accede a `Desempeño`, selecciona una clase.
2. Ve el ranking de estudiantes con:
   - Asistencias y porcentaje.
   - Participaciones y promedio de calificación.
   - Promedio de notas.
   - Índice de rendimiento compuesto (50% asistencia + 20% calificación participación + 30% notas).

**Flujo principal (Administrador):**
1. Accede a `Desempeño` con filtros: Sede → Carrera → Clase.
2. Sin clase seleccionada: vista agregada por clase (asistencia promedio, participaciones, notas).
3. Con clase seleccionada: mismo ranking individual que el catedrático.
4. Puede hacer clic en **Ver ranking** desde la vista agregada.

**Resultado esperado:** Identificación de estudiantes con bajo rendimiento para toma de decisiones.

---

### CU-15: Exportar datos a Excel

**Actor:** Catedrático (solo sus clases)  
**Descripción:** Descargar un archivo Excel con los datos de asistencia de una clase.

**Flujo principal:**
1. Accede a `Exportar Excel`, selecciona una clase.
2. Visualiza estadísticas preliminares (estudiantes, sesiones, asistencias, participaciones).
3. Hace clic en **Exportar**.
4. El sistema genera y descarga el archivo Excel con los datos de asistencia.

**Resultado esperado:** Archivo `.xlsx` descargado con datos de la clase.

---

### CU-16: Consultar bitácora del sistema

**Actor:** Administrador  
**Descripción:** Revisar el registro completo de acciones realizadas en el sistema.

**Flujo principal:**
1. Accede a `Admin > Bitácora del Sistema`.
2. Aplica filtros: módulo, acción, nivel de severidad, rango de fechas, texto libre.
3. Visualiza registros paginados (30 por página) con:
   - Usuario, acción, módulo, descripción, IP, fecha.
   - Estado anterior y nuevo (JSON).
   - Nivel de severidad con código de color.

**Resultado esperado:** Trazabilidad completa de todas las operaciones del sistema.

---

### CU-17: Medir nivel de ruido en clase

**Actor:** Catedrático  
**Descripción:** Activar el medidor de ruido del dispositivo durante una sesión activa.

**Flujo principal:**
1. El catedrático accede al módulo de medición de ruido durante una sesión operativa.
2. Activa el micrófono del dispositivo.
3. El sistema registra niveles en dB en tiempo real.
4. Si el nivel supera el umbral configurado (default 65 dB), se genera una alerta.
5. Al finalizar, se guarda un registro en `estadistica_ruido` con min, max, promedio, alertas y duración.

**Resultado esperado:** Estadísticas de ruido vinculadas a la sesión, visibles en las estadísticas de sesión.

---

### CU-18: Ver historial de grupos

**Actor:** Administrador / Catedrático  
**Descripción:** Revisar los grupos generados en sesiones anteriores, con descripción de la actividad realizada.

**Flujo principal:**
1. Accede a `Historial Grupos`, selecciona una clase.
   - **Catedrático:** el selector muestra únicamente sus clases (propietario `usuario_id` + pivot `clase_catedratico`).
   - **Administrador:** el selector muestra todas las clases del sistema.
2. El sistema muestra todas las sesiones que tuvieron grupos, ordenadas por fecha descendente.
3. Para cada sesión visualiza:
   - Fecha y estado (hoy / finalizada).
   - Número de grupos y total de estudiantes.
   - **Descripción de la actividad** (si fue ingresada al guardar los grupos).
   - Cada grupo con su lista de integrantes.

**Resultado esperado:** Historial completo de distribuciones de grupos con descripción de la actividad, accesible según el rol del usuario.

---

### CU-19: Registrar asistencia pública (estudiante)

**Actor:** Estudiante (sin cuenta de sistema)  
**Descripción:** El estudiante usa el QR generado por el catedrático para registrar su asistencia.

**Flujo principal:**
1. El estudiante escanea el QR con su dispositivo móvil.
2. Accede a la URL `/asistir/{token}`.
3. El sistema valida que el token sea vigente y la sesión operativa.
4. Ingresa su número de carné.
5. Permite acceso a la cámara para capturar selfie.
6. El navegador solicita permiso de ubicación GPS.
7. Confirma el registro.

**Flujo alterno:**
- Token expirado o inválido → pantalla de error descriptiva.
- Ya registrado → mensaje de confirmación sin duplicar registro.

**Resultado esperado:** Asistencia registrada con evidencia (selfie + GPS) vinculada a su carné.

---

### CU-20: Consultar portal del estudiante

**Actor:** Estudiante (sin cuenta de sistema)  
**Descripción:** El estudiante consulta su información académica (asistencia, calificaciones, actividades y grupos) ingresando solo su carné y correo.

**Flujo principal:**
1. El estudiante accede a `/portal-estudiante` (página pública).
2. Ingresa su **carné** y **correo electrónico**.
3. El sistema busca al estudiante y carga todos sus datos.
4. Para cada clase en la que está inscrito visualiza:
   - **Asistencia:** barra de progreso con cantidad de sesiones asistidas y porcentaje.
   - **Calificaciones:** notas por tipo de calificación.
   - **Actividades:** lista de actividades con su nota obtenida y punteo máximo; las actividades grupales están marcadas con etiqueta "Grupal".
   - **Grupos:** grupos en los que participó, con la descripción de la actividad realizada y la fecha de la sesión.
5. Puede hacer clic en **Nueva búsqueda** para limpiar el formulario.

**Restricciones:**
- Sin entregables ni subida de archivos (solo consulta).
- No requiere autenticación ni cuenta en el sistema.

**Flujo alterno:**
- Carné o correo incorrecto → mensaje de error, no se muestra información.

**Resultado esperado:** El estudiante visualiza su situación académica actualizada sin necesidad de credenciales de acceso al sistema.
