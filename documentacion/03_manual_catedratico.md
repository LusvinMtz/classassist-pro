# Manual de Usuario — Catedrático

**Sistema:** ClassAssist Pro  
**Versión:** 1.0  
**Rol:** Catedrático (`catedratico`)

---

## Introducción

ClassAssist Pro es una plataforma web de gestión académica diseñada para facilitar el trabajo del catedrático universitario. Permite llevar control de asistencia, participación, grupos de trabajo, calificaciones y desempeño de estudiantes de manera digital, en tiempo real y desde cualquier dispositivo.

El **Catedrático** tiene acceso a todas las herramientas académicas del sistema, pero únicamente sobre las clases que le han sido asignadas.

---

## Funcionalidades disponibles

| Módulo              | Descripción                                                                        |
|---------------------|------------------------------------------------------------------------------------|
| Dashboard           | KPIs y gráficas de asistencia y participación de sus clases                       |
| Estudiantes         | Gestionar estudiantes: agregar, importar Excel y generar QR de inscripción        |
| Sesiones            | Crear y administrar sesiones de clase diarias                                     |
| Pantalla de Clase   | QR de asistencia, ruleta de participación y grupos (todo en uno)                  |
| Calificaciones      | Registrar notas por tipo, actividades individuales y grupales                     |
| Desempeño           | Ranking de estudiantes por asistencia, participación y notas                      |
| Historial de Grupos | Consultar grupos generados en sesiones anteriores                                 |
| Exportar Excel      | Descargar datos de asistencia en formato Excel                                    |

---

## Guía paso a paso

### Dashboard

El dashboard muestra un resumen visual del rendimiento de tus clases.

1. Accede a **Dashboard** desde el menú lateral.
2. Selecciona una **clase** en el filtro superior.
3. Define el rango de fechas o usa los atajos (última semana, mes actual, etc.).
4. Las gráficas se actualizan automáticamente:
   - Asistencia por sesión (cuántos presentes vs. ausentes por sesión)
   - Porcentaje de asistencia por estudiante (top 20)
   - Participaciones por sesión
   - Ranking de participación (top 15 estudiantes)

_[Insertar imagen aquí]_

---

### Gestión de Estudiantes

#### Ver estudiantes de una clase

1. Accede a **Estudiantes** desde el menú.
2. Selecciona una clase en el selector desplegable.
3. Se muestra la lista de estudiantes inscritos con carné, nombre y correo.

_[Insertar imagen aquí]_

#### Agregar un estudiante

1. Selecciona la clase y haz clic en **Agregar Estudiante**.
2. Completa:
   - **Carné** — formato obligatorio: `0000-00-0000` (ej. `8590-21-16653`)
   - **Nombre completo**
   - **Correo electrónico** — debe ser institucional `@miumg.edu.gt` (opcional, único dentro de la clase)
3. Haz clic en **Agregar**.

> **Reglas de validación:** el carné debe tener exactamente tres secciones separadas por guión: 4 dígitos (código de carrera) + 2 dígitos (año) + 1 o más dígitos (número de estudiante). El correo, si se ingresa, debe terminar en `@miumg.edu.gt`.

_[Insertar imagen aquí]_

#### Importar estudiantes desde Excel

1. Haz clic en **Importar Excel**.
2. Descarga la **plantilla** con el formato requerido (Carné, Estudiante, Correo Electrónico).
3. Completa el archivo respetando las reglas:
   - **Carné:** formato `0000-00-0000` (ej. `8590-21-16653`)
   - **Correo:** debe terminar en `@miumg.edu.gt`
4. Carga el archivo.
5. El sistema importará los registros válidos y mostrará los errores encontrados (formato incorrecto, duplicados, datos faltantes, etc.) con el número de fila correspondiente.

_[Insertar imagen aquí]_

#### Generar QR de Inscripción

El QR de inscripción permite que los estudiantes se den de alta en la clase escaneando un código desde su dispositivo, sin que el catedrático tenga que registrarlos uno a uno.

1. Selecciona la clase y haz clic en **QR Inscripción**.
2. El sistema genera un código QR con vigencia de **24 horas**.
3. Comparte el QR proyectándolo o enviando el enlace directo que aparece bajo el código.
4. Los estudiantes escanan el QR y completan el formulario con su carné, nombre y correo institucional.
5. Si necesitas invalidar el QR anterior (por seguridad o por error), haz clic en **Regenerar**.

> **Importante:** el QR de inscripción es distinto al QR de asistencia. El primero inscribe al estudiante en la clase (una sola vez); el segundo registra su asistencia en una sesión específica (cada día de clase).

_[Insertar imagen aquí]_

#### Editar o quitar un estudiante

- **Editar:** haz clic en el ícono de lápiz para modificar carné, nombre o correo.
- **Quitar de clase:** haz clic en el ícono de persona con tachado. El estudiante se elimina de la clase pero no del sistema.

---

### Sesiones de Clase

Una sesión representa un día de clases. Debes crear una sesión cada día que impartas clase para habilitar las herramientas de asistencia, participación y grupos.

#### Crear una sesión

1. Accede a **Sesiones** y haz clic en **Nueva Sesión**.
2. En el modal, selecciona la clase que impartirás.
3. Haz clic en **Crear Sesión**.

> **Restricciones importantes:**
> - Solo puedes tener **una sesión abierta a la vez** en todo el sistema.
> - No puedes crear dos sesiones el mismo día para la misma clase.
> - Las sesiones no finalizadas se cierran automáticamente a las 23:59.

_[Insertar imagen aquí]_

#### Ver y administrar sesiones

La tabla de sesiones muestra todas las creadas por ti, con su estado y acciones disponibles:

| Estado    | Acciones disponibles                    |
|-----------|-----------------------------------------|
| Activa    | Finalizar, Ver Pantalla de Clase        |
| Finalizada | Estadísticas                           |
| Vencida   | (Sin acción disponible para catedrático) |

#### Finalizar una sesión

1. Localiza la sesión activa en la tabla.
2. Haz clic en **Finalizar**.
3. La sesión se marca como cerrada y se invalida el QR activo.

> Una vez finalizada, no podrá registrarse más asistencia ni participación en esa sesión.

---

### Pantalla de Clase

La Pantalla de Clase es el módulo principal durante una sesión activa. Integra QR de asistencia, ruleta de participación y generador de grupos.

> **Prerequisito:** Debes tener una sesión activa del día para usar estas herramientas.

#### Acceder a la Pantalla de Clase

1. Desde **Sesiones**, haz clic en el botón de acción de tu sesión activa.
2. O accede directamente desde el menú a **Pantalla de Clase**.

---

#### QR de Asistencia

1. En la Pantalla de Clase, selecciona la pestaña **QR / Asistencia**.
2. Haz clic en **Generar QR**.
3. El sistema genera un código QR con vigencia de **5 minutos**.
4. Proyecta el QR en pantalla para que los estudiantes lo escaneen.
5. Puedes **regenerar** el QR antes de que expire si es necesario.
6. Visualiza en tiempo real quién ha registrado asistencia.

_[Insertar imagen aquí]_

**Marcar asistencia manual:**
- Si un estudiante tiene problema con el QR, puedes marcarle asistencia manualmente desde la lista de estudiantes.

**Quitar asistencia:**
- Selecciona el registro y elimínalo mientras la sesión esté activa.

---

#### Ruleta de Participación

La ruleta selecciona aleatoriamente a un estudiante que haya registrado asistencia en la sesión actual.

1. Accede a la pestaña **Ruleta**.
2. Haz clic en **Girar**.
3. El sistema selecciona un estudiante al azar y lo muestra.
4. Puedes:
   - **Asignar calificación** (0.0 a 10.0) y un comentario, luego hacer clic en **Guardar participación**.
   - **Omitir** si no deseas registrar calificación esta vez.

_[Insertar imagen aquí]_

> Los puntos de participación se acumulan como puntos extra en la calificación final, sujetos al límite configurado en la clase.

---

#### Generador de Grupos

El sistema genera grupos optimizados minimizando la repetición de pares de estudiantes entre sesiones.

1. Accede a la pestaña **Grupos**.
2. Elige el modo:
   - **Por número de grupos:** define cuántos grupos quieres.
   - **Por tamaño de grupo:** define cuántos integrantes por grupo.
3. Ingresa la cantidad.
4. (Opcional) Escribe una **Descripción** de la actividad que realizarán (ej. "Debate U3"). Esto quedará registrado en el historial de grupos para que puedas recordar qué hicieron en cada sesión.
5. Haz clic en **Generar grupos**.
6. El sistema muestra un preview de los grupos con sus integrantes.
7. Si el resultado es satisfactorio, haz clic en **Guardar grupos**.
8. Tras guardar, aparece el modal **¿Crear actividad grupal?**:
   - Ingresa el **nombre** y el **punteo máximo** de la actividad y haz clic en **Crear actividad**.
   - Si prefieres crear la actividad más tarde (o no la necesitas), haz clic en **Omitir**.
9. Si deseas otra distribución antes de guardar, haz clic en **Regenerar**.

_[Insertar imagen aquí]_

**Eliminar grupos:**
- Si deseas borrar los grupos generados para esta sesión, usa el botón **Eliminar grupos**.

---

### Calificaciones

El módulo de calificaciones permite registrar notas por distintas categorías de evaluación.

#### Acceder al módulo

1. Desde el menú, accede a **Calificaciones**.
2. Selecciona una clase.

El módulo se organiza en pestañas:

| Pestaña         | Contenido                                         |
|-----------------|---------------------------------------------------|
| Resumen         | Nota final calculada por estudiante               |
| [Tipo fijo]     | Pestaña por cada tipo de calificación (ej. Parcial) |
| Actividades     | Actividades individuales y grupales               |

---

#### Registrar calificaciones fijas (parciales, etc.)

1. Selecciona la pestaña del tipo de calificación deseado.
2. Aparece una tabla con todos los estudiantes de la clase.
3. Ingresa la nota de cada estudiante (respetando el punteo máximo del tipo).
4. Haz clic en **Guardar**.

> **Bloqueo de notas:** una vez guardada la nota de un estudiante, queda bloqueada con un ícono de candado. El botón **Guardar** desaparece automáticamente cuando todos los estudiantes tienen nota registrada.
>
> **¿Error en una nota?** Reporta el caso al administrador del sistema. Solo el administrador puede modificar notas ya guardadas.

_[Insertar imagen aquí]_

---

#### Gestionar actividades

> **Restricción de ciclo cerrado:** una vez guardadas las notas de todos los tipos fijos (Parcial 1 hasta Examen Final) para todos los estudiantes, el ciclo de calificaciones se considera cerrado y no se pueden agregar nuevas actividades.

**Crear una actividad:**
1. En la pestaña **Actividades**, haz clic en **Agregar**.
2. Ingresa el nombre de la actividad (se califica sobre 100 pts).
3. Guarda la actividad.

**Actividades desde grupos:**
- Al guardar grupos en la Pantalla de Clase, el sistema ofrece crear una actividad grupal automáticamente.

**Actividades desde ruleta:**
- Antes de girar la ruleta, crea una actividad de sesión. Cada estudiante seleccionado acumulará su nota en esa misma actividad.

**Registrar notas de actividad individual:**
1. Ingresa la nota de cada estudiante (0–100).
2. Haz clic en **Guardar notas**.
3. Una vez guardadas, las notas se bloquean. El botón desaparece.

**Registrar notas de actividad grupal:**
1. Ingresa una nota por grupo (0–100).
2. Haz clic en **Guardar y propagar**.
3. El sistema distribuye la misma nota a todos los integrantes del grupo.

_[Insertar imagen aquí]_

**Importar notas desde Excel:**
1. Descarga la plantilla de notas con el botón correspondiente.
2. Completa las notas y carga el archivo.

---

#### Ver resumen de notas

La pestaña **Resumen** muestra:
- Nota de cada tipo de calificación.
- Puntos extra de participación (máximo 5 pts).
- Nota total calculada.
- Estado: **Aprobado** (≥ 61) o **Reprobado** (< 61).

_[Insertar imagen aquí]_

---

### Desempeño de Estudiantes

1. Accede a **Desempeño** y selecciona una clase.
2. El sistema muestra el ranking de estudiantes con:
   - Asistencias registradas vs. total de sesiones.
   - Porcentaje de asistencia con barra de progreso.
   - Número de participaciones y promedio de calificación.
   - Promedio de notas.
   - **Índice de rendimiento** compuesto: 50% asistencia + 20% calif. de participación + 30% notas.
3. Filtra el ordenamiento: Asistencia, Participaciones, Calif. participación o Notas.

_[Insertar imagen aquí]_

**Paginación:** Se muestran 5 estudiantes por página.

---

### Estadísticas de Sesión

Cada sesión finalizada tiene disponible una vista detallada de estadísticas.

1. Desde **Sesiones**, localiza una sesión finalizada.
2. Haz clic en **Estadísticas**.
3. La vista muestra:
   - **KPIs:** inscritos, presentes, ausentes, participaciones, grupos, ruido.
   - **Presentes:** lista con selfie (clic para ampliar) y hora de registro.
   - **Ausentes:** lista de estudiantes que no asistieron.
   - **Participaciones:** tabla con nombre, calificación y comentario.
   - **Grupos:** tarjetas con los integrantes de cada grupo.
   - **Mapa:** ubicaciones GPS de cada registro de asistencia en Google Maps.
   - **Ruido:** estadísticas de decibelios si se usó el medidor.

_[Insertar imagen aquí]_

---

### Historial de Grupos

Consulta los grupos formados en sesiones anteriores de tu clase.

1. Accede a **Historial Grupos** desde el menú.
2. Selecciona una de **tus clases** (el selector muestra únicamente las clases que administras o en las que participas).
3. Se muestran todas las sesiones que tuvieron grupos generados, ordenadas de más reciente a más antigua.
4. Para cada sesión se muestra:
   - Fecha y estado (activa hoy / finalizada).
   - Número de grupos y total de estudiantes.
   - **Descripción de la actividad** (si se ingresó al guardar).
   - Los grupos con sus integrantes.

_[Insertar imagen aquí]_

---

### Exportar a Excel

1. Accede a **Exportar Excel**.
2. Selecciona una clase de las asignadas a ti.
3. El sistema muestra un resumen (estudiantes, sesiones, asistencias, participaciones).
4. Haz clic en **Exportar**.
5. Se descarga un archivo `.xlsx` con los datos de asistencia.

_[Insertar imagen aquí]_

---

## Restricciones del rol Catedrático

- **Solo puede acceder a sus propias clases:** tanto las que creó como las que le asignaron por pivote.
- **No puede ver las sesiones de otros catedráticos.**
- **Solo puede tener una sesión abierta a la vez** en todo el sistema.
- **Las herramientas de sesión (QR, ruleta, grupos) se bloquean** cuando la sesión no es operativa (día diferente o ya finalizada).
- **No puede reabrir una sesión finalizada** (solo el administrador puede hacerlo).
- **No puede exportar datos de clases de otros catedráticos.**
- **No puede acceder al Panel Admin**, bitácora, gestión de usuarios ni tipos de calificación.

---

## Buenas prácticas

- **Crea la sesión al inicio de cada clase** para que los estudiantes puedan registrar asistencia de inmediato.
- **Genera el QR justo cuando vayas a usarlo**, ya que tiene una vigencia de solo 5 minutos. Puedes regenerarlo cuantas veces necesites.
- **Finaliza la sesión al terminar la clase** para evitar que la sesión quede abierta innecesariamente (el sistema la cerrará automáticamente a las 23:59, pero es buena práctica hacerlo manualmente).
- **Usa la ruleta con frecuencia** para que la participación se distribuya equitativamente entre estudiantes y contribuya a su calificación.
- **Genera grupos en cada sesión** para que el algoritmo de co-ocurrencia tenga más datos y optimice mejor la distribución en sesiones futuras.
- **Importa estudiantes masivamente desde Excel** al inicio del ciclo para ahorrar tiempo de captura, o usa el **QR de Inscripción** para que los propios estudiantes se registren.
- **Verifica el formato de carné y correo** antes de importar: el carné debe seguir el patrón `0000-00-0000` y el correo debe ser `@miumg.edu.gt`. Los registros que no cumplan estas reglas serán rechazados con el número de fila correspondiente.
- **Configura el método de evaluación de actividades** antes de comenzar a registrar notas, para evitar recalcular manualmente.
- **Revisa el resumen de notas** periódicamente para identificar estudiantes en riesgo académico antes del cierre del ciclo.
