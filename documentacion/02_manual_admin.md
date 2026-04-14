# Manual de Usuario — Administrador

**Sistema:** ClassAssist Pro  
**Versión:** 1.0  
**Rol:** Administrador (`admin`)

---

## Introducción

ClassAssist Pro es una plataforma web de gestión académica universitaria. El **Administrador** es el rol con mayor nivel de acceso: puede configurar la estructura completa del sistema (sedes, carreras, usuarios, tipos de calificación), ver todos los datos de cualquier catedrático y clase, y auditar cada acción realizada en la plataforma.

El acceso administrativo se gestiona a través del módulo **Panel Admin**, accesible únicamente para usuarios con el rol `admin`.

---

## Funcionalidades disponibles

| Módulo                   | Descripción                                                    |
|--------------------------|----------------------------------------------------------------|
| Panel Admin              | Vista general con KPIs globales y accesos rápidos             |
| Gestión de Usuarios      | CRUD completo de usuarios y asignación de roles/clases        |
| Tipos de Calificación    | Configurar categorías de evaluación del sistema               |
| Sedes                    | Gestionar campus universitarios y sus carreras                |
| Bitácora del Sistema     | Auditoría completa de todas las acciones                      |
| Dashboard                | Estadísticas y gráficas con filtros globales                  |
| Estudiantes (global)     | Ver todos los estudiantes del sistema con sus clases          |
| Sesiones (global)        | Ver y filtrar sesiones de todos los catedráticos              |
| Desempeño (global)       | Rankings y estadísticas filtradas por sede/carrera/clase      |
| Calificaciones           | Acceso a calificaciones de clases propias                     |
| Historial de Grupos      | Consultar grupos generados por sesión — el admin ve **todas** las clases del sistema |
| Exportar Excel           | Exportar datos de asistencia de clases propias                |

---

## Guía paso a paso

### Panel Administrativo

El Panel Admin es la pantalla principal del administrador. Muestra 5 indicadores clave (KPIs) en una fila y accesos directos a los módulos principales.

**KPIs mostrados:**
- Usuarios totales registrados
- Total de catedráticos activos
- Clases registradas en el sistema
- Tipos de calificación configurados
- Sedes registradas

**Cómo acceder:**
1. Inicia sesión con tu cuenta de administrador.
2. En el menú lateral, haz clic en **Panel Admin**.
3. Verás los contadores actualizados y los accesos rápidos a todos los módulos administrativos.

_[Insertar imagen aquí]_

---

### Gestión de Usuarios

#### Crear un nuevo usuario

1. Desde el Panel Admin, haz clic en **Gestión de Usuarios** o accede desde el menú.
2. Haz clic en el botón **Nuevo Usuario**.
3. Completa el formulario:
   - **Nombre completo** (requerido)
   - **Correo electrónico** (requerido, debe ser único)
   - **Contraseña** (mínimo 6 caracteres)
   - **Rol:** selecciona `admin` o `catedratico`
   - **Estado:** activo o inactivo
4. Si el rol es `catedratico`, aparecerá una sección para asignar clases:
   - Usa el buscador para filtrar por nombre de clase.
   - Selecciona las clases que impartirá (máximo 6 clases).
   - Las clases seleccionadas aparecen como etiquetas al pie del panel.
5. Haz clic en **Crear usuario**.

_[Insertar imagen aquí]_

> **Nota:** El sistema registrará automáticamente esta acción en la Bitácora.

#### Editar un usuario existente

1. Localiza el usuario en la tabla (usa el buscador si es necesario).
2. Haz clic en el ícono de editar (lápiz).
3. Modifica los campos necesarios. La contraseña solo se actualiza si se ingresa una nueva.
4. Guarda los cambios.

#### Desactivar / Eliminar un usuario

- **Desactivar:** edita el usuario y cambia el estado a "Inactivo". El usuario no podrá iniciar sesión.
- **Eliminar:** haz clic en el ícono de eliminar. Se aplica soft delete (no se borra definitivamente).
- No es posible eliminarse a uno mismo.

_[Insertar imagen aquí]_

---

### Tipos de Calificación

Los tipos de calificación definen las categorías de evaluación disponibles en el sistema (ej. Examen Parcial, Actividades, Participación).

#### Crear un tipo de calificación

1. Desde el Panel Admin, haz clic en **Tipos de Calificación**.
2. Haz clic en **Nuevo Tipo**.
3. Ingresa:
   - **Nombre** (único en el sistema, ej. "Examen Final")
   - **Descripción** (opcional)
   - **Punteo máximo** (ej. 100.00)
   - **Orden** de visualización
4. Haz clic en **Guardar**.

> **Importante:** El tipo con nombre `actividades` (insensible a mayúsculas) tiene comportamiento especial en el cálculo de notas finales. No se recomienda eliminarlo si hay calificaciones registradas.

_[Insertar imagen aquí]_

---

### Sedes

#### Crear una sede

1. Desde el Panel Admin, haz clic en **Sedes**.
2. Haz clic en **Nueva Sede**.
3. Ingresa nombre, código (único) y dirección.
4. Selecciona las **carreras** que estarán disponibles en esta sede.
5. Guarda.

#### Editar o eliminar una sede

- Usa los botones de acción en la tabla de sedes.
- La eliminación es lógica (soft delete).

_[Insertar imagen aquí]_

---

### Bitácora del Sistema

La bitácora registra automáticamente todas las acciones relevantes: inicios de sesión, creaciones, ediciones, eliminaciones y exportaciones.

#### Consultar la bitácora

1. Desde el Panel Admin, haz clic en **Bitácora del Sistema**.
2. Usa los filtros disponibles:
   - **Módulo:** Clase, Estudiante, Asistencia, Calificacion, etc.
   - **Acción:** login, logout, crear, editar, eliminar, exportar.
   - **Nivel:** info, advertencia, error.
   - **Rango de fechas:** desde / hasta.
   - **Búsqueda libre:** busca en descripción o dirección IP.
3. Los registros se muestran paginados (30 por página).
4. Haz clic en **Limpiar filtros** para restablecer la vista.

_[Insertar imagen aquí]_

**Información visible por registro:**
- Usuario que realizó la acción (o "Sistema" si fue automático)
- Acción y módulo afectado
- Descripción legible
- Dirección IP y user agent
- Estado anterior y nuevo (en formato JSON)
- Nivel de severidad con código de color

---

### Dashboard (Vista global)

El dashboard muestra estadísticas con gráficas interactivas. El administrador puede filtrar por Sede → Carrera → Clase.

#### Usar el dashboard

1. Accede a **Dashboard** desde el menú lateral.
2. Selecciona filtros opcionales:
   - **Sede** → carga las carreras disponibles.
   - **Carrera** → carga las clases.
   - **Clase** → filtra todas las gráficas.
   - **Rango de fechas** o atajos rápidos (última semana, mes actual, etc.).
3. Las gráficas se actualizan automáticamente:
   - Asistencia por sesión (presente vs. ausente)
   - Asistencia por estudiante (top 20)
   - Participaciones por sesión
   - Ranking de participación (top 15)

_[Insertar imagen aquí]_

---

### Gestión de Estudiantes (Vista global)

Como administrador, puedes ver **todos** los estudiantes del sistema sin restricción de clase.

1. Accede a **Estudiantes** desde el menú.
2. Usa el buscador para filtrar por nombre, carné o correo.
3. La tabla muestra, para cada estudiante, los **badges de todas las clases** en las que está inscrito.
4. Puedes **editar** datos del estudiante (carné, nombre, correo) globalmente.
5. Puedes **eliminar** un estudiante del sistema (soft delete).

_[Insertar imagen aquí]_

---

### Sesiones (Vista global)

El administrador puede ver las sesiones de **todos los catedráticos** con una barra de filtros.

1. Accede a **Sesiones**.
2. Filtra por Sede, Carrera, Clase o nombre de catedrático.
3. Visualiza el estado de cada sesión (activa / finalizada / vencida).
4. Acciones disponibles:
   - **Estadísticas:** para sesiones finalizadas.
   - **Finalizar:** cierra una sesión activa.
   - **Reabrir:** permite reabrir una sesión del día (solo admin).
   - **Forzar cierre:** para sesiones vencidas sin finalizar.

_[Insertar imagen aquí]_

---

### Desempeño (Vista global)

El administrador puede analizar el desempeño con filtros cascada globales.

**Vista agregada por clase:**
1. Accede a **Desempeño**.
2. Filtra por Sede y/o Carrera (sin seleccionar clase específica).
3. La tabla muestra un resumen por clase: estudiantes, sesiones, % asistencia promedio, participaciones, promedio de notas.
4. Paginación de 5 clases por página.
5. Haz clic en **Ver ranking** para profundizar en una clase específica.

**Vista de ranking por estudiante:**
1. Selecciona una clase específica en el filtro.
2. El sistema muestra el ranking individual con asistencia, participaciones, calificaciones y un índice de rendimiento compuesto.
3. Ordena por: Asistencia, Participaciones, Calif. participación o Notas.

_[Insertar imagen aquí]_

---

## Restricciones del rol Administrador

- No puede registrar asistencia vía QR (ese flujo es público y para estudiantes).
- No puede eliminar su propia cuenta de usuario.
- La Bitácora es de solo lectura; no se pueden borrar registros.
- Las sesiones de días anteriores no pueden reabrirse (solo las del día actual).
- Los tipos de calificación con calificaciones asociadas no deben eliminarse sin previamente reasignar o limpiar las notas.

---

## Buenas prácticas

- **Mantén la bitácora revisada** periódicamente para detectar acciones inusuales (niveles `advertencia` o `error`).
- **Define los tipos de calificación antes** de que los catedráticos comiencen a registrar notas, ya que modificarlos puede afectar los cálculos existentes.
- **Asigna correctamente los roles** al crear usuarios; un usuario sin rol no podrá acceder a ningún módulo.
- **Verifica el correo electrónico** de los usuarios nuevos; sin verificación no pueden iniciar sesión.
- **Usa los filtros de búsqueda** en la bitácora para investigar incidentes específicos por módulo, acción o rango de fechas.
- **Desactiva** usuarios en lugar de eliminarlos cuando dejen de usar el sistema, para mantener la trazabilidad histórica.
