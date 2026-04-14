# Manual de Usuario — Estudiante

**Sistema:** ClassAssist Pro  
**Versión:** 1.0  
**Rol:** Estudiante

---

## Introducción

ClassAssist Pro es una plataforma web de gestión académica universitaria. El **Estudiante** interactúa principalmente con el sistema a través del **registro de asistencia por código QR**, el cual no requiere cuenta de usuario ni instalación de ninguna aplicación.

Adicionalmente, el sistema cuenta con un **Portal del Estudiante** (acceso público) donde puede consultar información relevante sobre su desempeño académico.

El proceso de registro de asistencia está diseñado para ser rápido, seguro y ejecutarse directamente desde el navegador web del dispositivo móvil del estudiante.

---

## Funcionalidades disponibles

| Funcionalidad                   | Descripción                                                                  | Requiere cuenta |
|---------------------------------|------------------------------------------------------------------------------|-----------------|
| Inscripción a clase por QR      | Escanear el QR del catedrático para registrarse en la clase                  | No              |
| Registro de asistencia por QR  | Escanear el QR del catedrático para marcar asistencia en una sesión          | No              |
| Captura de selfie               | Evidencia fotográfica del registro de asistencia                             | No              |
| Captura de ubicación GPS        | Coordenadas geográficas del momento del registro de asistencia               | No              |
| Portal del estudiante           | Consulta de información académica básica                                     | No (acceso público) |

---

## Guía paso a paso

### Inscripción a Clase por Código QR

Antes de poder registrar asistencia, debes estar inscrito en la clase. Si tu catedrático comparte un **QR de Inscripción**, puedes darte de alta tú mismo sin necesidad de que él te agregue manualmente.

#### Paso 1 — Escanear el código QR de inscripción

1. El catedrático compartirá un **código QR de inscripción** al inicio del ciclo (puede proyectarlo, enviarlo por mensaje o publicarlo en el grupo de la clase).
2. Abre la cámara de tu dispositivo y apunta al QR.
3. Se abrirá automáticamente el formulario de inscripción en tu navegador.

> El QR de inscripción tiene una vigencia de **24 horas**. Si venció, solicita al catedrático que genere uno nuevo.

_[Insertar imagen aquí]_

---

#### Paso 2 — Completar el formulario de inscripción

1. En el formulario ingresa los siguientes datos:
   - **Carné** — con el formato exacto: `0000-00-0000` (ej. `8590-21-16653`)
   - **Nombre completo**
   - **Correo institucional** — debe terminar en `@miumg.edu.gt`
2. Verifica que los datos sean correctos.
3. Haz clic en **Inscribirme**.

_[Insertar imagen aquí]_

> Si ves un mensaje de error en el carné, verifica que tenga el formato correcto (código carrera - año - número). Si el error es en el correo, asegúrate de usar tu correo institucional de la UMG.

---

#### Paso 3 — Confirmación

Si todos los datos son válidos, verás un mensaje de confirmación con tu nombre y la clase a la que te inscribiste. Puedes cerrar la página.

> Solo es necesario inscribirse **una vez por clase**. A partir de ese momento podrás registrar asistencia en cada sesión.

---

### Registro de Asistencia por Código QR

Este es el proceso principal del estudiante. Se realiza desde cualquier navegador web moderno (Chrome, Safari, Firefox) en dispositivo móvil o computadora.

#### Paso 1 — Escanear el código QR

1. El catedrático proyectará un **código QR** en la pantalla del aula al inicio de la clase.
2. Abre la cámara de tu dispositivo móvil o una aplicación de lectura de QR.
3. Apunta la cámara hacia el código QR.
4. El dispositivo detectará automáticamente el enlace y lo abrirá en el navegador.

> **Importante:** El código QR tiene una **vigencia de 5 minutos**. Si el tiempo expira, solicita al catedrático que genere uno nuevo.

_[Insertar imagen aquí]_

---

#### Paso 2 — Ingresar el número de carné

1. En la pantalla que se abre, ingresa tu **número de carné** exactamente como está registrado en el sistema.
2. Verifica que esté correcto antes de continuar.

_[Insertar imagen aquí]_

> Si el sistema indica que tu carné no está registrado en la clase, comunícate con tu catedrático para que verifique tu inscripción.

---

#### Paso 3 — Capturar selfie (evidencia fotográfica)

1. El sistema activará la cámara frontal de tu dispositivo.
2. Toma una **selfie** que muestre claramente tu rostro.
3. La imagen se enviará automáticamente como evidencia del registro.

_[Insertar imagen aquí]_

> La selfie es capturada y almacenada de forma segura. Solo el catedrático y el administrador del sistema pueden visualizarla.

---

#### Paso 4 — Permitir acceso a la ubicación GPS

1. El navegador solicitará permiso para acceder a tu **ubicación geográfica**.
2. Haz clic en **Permitir** o **Allow** para compartir tus coordenadas.
3. Las coordenadas se registran automáticamente junto con tu asistencia.

_[Insertar imagen aquí]_

> La ubicación GPS confirma que el estudiante se encontraba físicamente en el aula. Si denias el permiso, es posible que no puedas completar el registro dependiendo de la configuración de la clase.

---

#### Paso 5 — Confirmar el registro

1. Haz clic en el botón **Registrar asistencia**.
2. Si todo es correcto, verás un mensaje de confirmación en pantalla.
3. Tu asistencia queda registrada con la hora exacta, la selfie y las coordenadas GPS.

_[Insertar imagen aquí]_

---

### Posibles mensajes de error y cómo resolverlos

#### Inscripción (QR de inscripción)

| Mensaje                                              | Causa                                                  | Solución                                                         |
|------------------------------------------------------|--------------------------------------------------------|------------------------------------------------------------------|
| "El código QR no es válido"                          | El token no existe en el sistema                       | Verifica el enlace o solicita el QR nuevamente al catedrático    |
| "El código QR ha expirado"                           | Pasaron más de 24 horas desde que se generó            | Solicita al catedrático que genere un nuevo QR de inscripción    |
| "El carné debe tener el formato: 0000-00-0000"       | El carné no sigue el formato institucional             | Usa el formato correcto, ej. `8590-21-16653`                     |
| "El correo debe ser institucional (@miumg.edu.gt)"   | El correo no pertenece al dominio UMG                  | Usa tu correo `@miumg.edu.gt`                                    |
| "Ya estás registrado en esta clase con este carné"   | El carné ya está inscrito en la clase                  | Ya tienes acceso a la clase, no es necesario inscribirte de nuevo |

#### Asistencia (QR de sesión)

| Mensaje                                   | Causa                                              | Solución                                                    |
|-------------------------------------------|----------------------------------------------------|-------------------------------------------------------------|
| "Código QR inválido o expirado"           | El token ha vencido (más de 5 minutos)             | Solicita al catedrático que genere un nuevo código QR       |
| "La sesión ya fue finalizada"             | El catedrático cerró la sesión                     | No es posible registrarse; consulta al catedrático           |
| "Tu carné no está inscrito en esta clase" | El carné ingresado no coincide con el registrado   | Verifica que ya te hayas inscrito en la clase mediante el QR de inscripción |
| "Ya registraste tu asistencia"            | Intentas registrarte por segunda vez               | Tu asistencia ya fue contabilizada, no es necesario repetir |
| "Error de ubicación"                      | El navegador no pudo obtener coordenadas GPS        | Verifica que el GPS esté activado y que hayas dado permiso   |

---

### Consejos para un registro exitoso

- **Activa el GPS** de tu dispositivo antes de intentar registrarte.
- **Usa buena iluminación** al capturar la selfie para que tu rostro sea claramente visible.
- **No compartas el enlace QR** con compañeros que no estén en el aula; el registro incluye evidencia de ubicación.
- **Regístrate al inicio de la clase**, ya que el QR puede cambiar o vencer durante la sesión.
- **Usa un navegador actualizado** (Chrome o Safari recomendados) para garantizar el funcionamiento de la cámara y el GPS.

---

### Portal del Estudiante

El Portal del Estudiante es una sección pública donde puedes consultar tu situación académica completa: asistencia, calificaciones, actividades y grupos en los que has participado.

**Cómo acceder:**
1. Abre tu navegador y accede a `/portal-estudiante` (o al enlace que te proporcione la institución).
2. No es necesario iniciar sesión.

**Pasos:**
1. Ingresa tu **carné** y **correo electrónico**.
2. Haz clic en **Consultar mi información**.
3. Para cada clase inscrita verás:

| Sección         | Qué muestra                                                                              |
|-----------------|------------------------------------------------------------------------------------------|
| **Asistencia**  | Barra de progreso con sesiones asistidas vs. total y porcentaje                          |
| **Calificaciones** | Nota por tipo de calificación (parciales, etc.)                                       |
| **Actividades** | Lista de actividades con tu nota y el punteo máximo; las actividades grupales están marcadas con etiqueta "Grupal" |
| **Grupos**      | Grupos en los que participaste, con fecha de sesión y descripción de la actividad        |

4. Haz clic en **Nueva búsqueda** para limpiar el formulario y consultar otro estudiante.

> **Restricción:** El portal es solo de consulta. No permite entregar archivos, modificar calificaciones ni realizar ninguna otra acción.

_[Insertar imagen aquí]_

---

## Restricciones del rol Estudiante

- **No tiene acceso al panel administrativo** ni a ningún módulo de gestión.
- **No puede ver ni modificar** registros de otros estudiantes.
- **No puede modificar su registro de asistencia** una vez realizado.
- **No puede acceder a las calificaciones de otros estudiantes.**
- **No puede crear sesiones, grupos ni participaciones** de forma directa.
- **El QR de inscripción tiene vigencia de 24 horas** desde su generación.
- **El QR de asistencia tiene ventana de tiempo limitada** (5 minutos por token).
- Solo puede registrarse **una vez por sesión** (restricción de la base de datos).
- El **carné debe tener el formato institucional** `0000-00-0000` (ej. `8590-21-16653`).
- El **correo debe ser institucional** `@miumg.edu.gt`; no se aceptan correos de otros dominios.

---

## Preguntas frecuentes

**¿Necesito instalar una aplicación?**  
No. El registro de asistencia funciona completamente desde el navegador web de tu dispositivo móvil o computadora. No requiere instalar ninguna app.

**¿Qué pasa si no tengo acceso a internet en el momento de la clase?**  
El registro de asistencia requiere conexión a internet. Si no tienes conectividad, solicita a tu catedrático que registre tu asistencia manualmente.

**¿La selfie y mi ubicación son almacenadas de forma segura?**  
Sí. Los datos son almacenados en los servidores de la institución y solo son accesibles para el catedrático de la clase y el administrador del sistema con fines de verificación de asistencia.

**¿Puedo registrarme en múltiples clases el mismo día?**  
Sí. Cada clase tiene su propio código QR y sesión independiente. Puedes registrarte en tantas clases como corresponda en el día.

**¿Cuál es la diferencia entre el QR de inscripción y el QR de asistencia?**
El **QR de inscripción** se usa una sola vez al inicio del ciclo para darte de alta en la clase (válido 24 horas). El **QR de asistencia** se usa cada día de clase para marcar tu presencia en una sesión específica (válido 5 minutos).

**¿Qué hago si el catedrático olvidó generar el QR?**  
Solicita al catedrático que genere el código QR desde la Pantalla de Clase. Si la sesión fue olvidada, el catedrático puede registrar tu asistencia manualmente.

**¿Qué formato debe tener mi carné?**  
El carné debe tener tres partes separadas por guiones: código de carrera (4 dígitos), año de ingreso (2 dígitos) y número de estudiante (1 o más dígitos). Ejemplo: `8590-21-16653`.

**¿Por qué no acepta mi correo electrónico?**  
El sistema solo acepta correos del dominio institucional de la UMG: `@miumg.edu.gt`. Si usas un correo personal (Gmail, Hotmail, etc.) será rechazado.

**¿Puedo registrar mi asistencia desde una computadora de escritorio?**  
Sí, pero necesitarás una cámara web para la selfie y que el navegador tenga acceso a la ubicación. Se recomienda el uso desde dispositivo móvil para mayor comodidad.
