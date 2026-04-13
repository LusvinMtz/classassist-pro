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

| Funcionalidad                   | Descripción                                                    | Requiere cuenta |
|---------------------------------|----------------------------------------------------------------|-----------------|
| Registro de asistencia por QR  | Escanear el QR del catedrático para marcar asistencia          | No              |
| Captura de selfie               | Evidencia fotográfica del registro de asistencia               | No              |
| Captura de ubicación GPS        | Coordenadas geográficas del momento del registro               | No              |
| Portal del estudiante           | Consulta de información académica básica                       | No (acceso público) |

---

## Guía paso a paso

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

| Mensaje                                   | Causa                                              | Solución                                                    |
|-------------------------------------------|----------------------------------------------------|-------------------------------------------------------------|
| "Código QR inválido o expirado"           | El token ha vencido (más de 5 minutos)             | Solicita al catedrático que genere un nuevo código QR       |
| "La sesión ya fue finalizada"             | El catedrático cerró la sesión                     | No es posible registrarse; consulta al catedrático           |
| "Tu carné no está inscrito en esta clase" | El carné ingresado no coincide con el registrado   | Verifica tu carné o comunícate con el catedrático            |
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

El Portal del Estudiante es una sección pública del sistema donde puedes consultar información básica sobre tu situación académica.

**Cómo acceder:**
1. Abre tu navegador y navega a la dirección del sistema.
2. En la página principal, selecciona la opción **Portal Estudiante** o accede directamente a `/portal`.
3. No es necesario iniciar sesión.

_[Insertar imagen aquí]_

> Las funcionalidades específicas del portal dependen de la configuración del sistema establecida por la institución.

---

## Restricciones del rol Estudiante

- **No tiene acceso al panel administrativo** ni a ningún módulo de gestión.
- **No puede ver ni modificar** registros de otros estudiantes.
- **No puede modificar su registro de asistencia** una vez realizado.
- **No puede acceder a las calificaciones de otros estudiantes.**
- **No puede crear sesiones, grupos ni participaciones** de forma directa.
- **El acceso por QR tiene ventana de tiempo limitada** (5 minutos por token).
- Solo puede registrarse **una vez por sesión** (restricción de la base de datos).

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

**¿Qué hago si el catedrático olvidó generar el QR?**  
Solicita al catedrático que genere el código QR desde la Pantalla de Clase. Si la sesión fue olvidada, el catedrático puede registrar tu asistencia manualmente.

**¿Puedo registrar mi asistencia desde una computadora de escritorio?**  
Sí, pero necesitarás una cámara web para la selfie y que el navegador tenga acceso a la ubicación. Se recomienda el uso desde dispositivo móvil para mayor comodidad.
