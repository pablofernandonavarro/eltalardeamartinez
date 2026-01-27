# Instructivo: Asignación de Unidades Funcionales a Usuarios

## Introducción

Este instructivo explica cómo asignar unidades funcionales a usuarios del sistema, ya sean **Propietarios** o **Inquilinos**. El sistema permite gestionar las relaciones entre usuarios y unidades, especificando roles y responsabilidades.

---

## Conceptos Importantes

### Roles de Usuario
- **Propietario**: Usuario dueño de una unidad funcional
- **Inquilino**: Usuario que alquila/habita una unidad funcional
- **Admin**: Administrador del sistema (gestiona todo)
- **Bañero**: Personal encargado de las piletas
- **Residente**: Usuario general del complejo

### Tipos de Relación Usuario-Unidad
- **Es Propietario**: Marca al usuario como dueño de la unidad
- **Es Responsable del Pago**: Indica quién debe pagar las expensas/servicios (puede ser propietario o inquilino)

### Reglas del Sistema
1. Una unidad solo puede tener **UN propietario activo** a la vez
2. Una unidad solo puede tener **UN inquilino activo** a la vez
3. Solo puede haber **UN responsable de pago activo** por unidad
4. Para marcar a un usuario como propietario de una unidad, el usuario **DEBE** tener rol "Propietario"
5. Las relaciones pueden tener fecha de inicio y fin (para gestionar históricos)

---

## Proceso de Asignación

### Paso 1: Verificar/Asignar Rol al Usuario

**Antes de asignar una unidad, el usuario debe tener su rol configurado.**

1. Ir a **Admin → Usuarios** (menú lateral izquierdo)
2. Buscar al usuario en la lista
3. Si el usuario no tiene rol o tiene un rol incorrecto:
   - Hacer clic en **"Editar"** (ícono de lápiz)
   - En el campo **"Rol"**, seleccionar:
     - **"Propietario"** si el usuario es dueño de una unidad
     - **"Inquilino"** si el usuario alquila/habita una unidad
   - Guardar cambios

**Importante**:
- Los usuarios con rol "Propietario" pueden ser marcados como propietarios de unidades
- Los usuarios con rol "Inquilino" son inquilinos de unidades
- Un usuario sin rol asignado NO puede ser asignado a una unidad

---

### Paso 2: Crear la Relación Usuario-Unidad

1. Ir a **Admin → Unidades Funcionales** en el menú lateral
2. Hacer clic en **"Asignar Usuario a Unidad"**
3. Completar el formulario:

#### Campos Obligatorios

**Usuario** *
- Seleccionar el usuario de la lista desplegable
- Se mostrará: Nombre (email) - Rol
- Si aparece "Sin rol (requiere asignación)", volver al Paso 1

**Unidad Funcional** *
- Seleccionar la unidad de la lista
- Formato: Torre/Piso/Número - Nombre del Complejo
- Ejemplo: "Torre A / Piso 2 / Depto 5 - El Talar de Martínez"

**Fecha de Inicio** *
- Fecha en que comienza la relación
- Por defecto: fecha actual

#### Campos Opcionales

**Es Propietario**
- Marcar SOLO si el usuario es dueño de la unidad
- Validación: El usuario DEBE tener rol "Propietario"
- Solo puede haber un propietario activo por unidad

**Es Responsable del Pago**
- Marcar si este usuario debe pagar las expensas
- Puede ser el propietario o el inquilino
- Solo puede haber un responsable activo por unidad

**Fecha de Fin**
- Dejar vacío si la relación está activa
- Completar solo cuando la relación termina (ej: inquilino se muda)

**Notas**
- Campo de texto libre para comentarios adicionales
- Ej: "Contrato hasta diciembre 2026"

4. Hacer clic en **"Guardar"**

---

## Ejemplos Prácticos

### Ejemplo 1: Asignar Propietario a su Unidad

**Escenario**: Juan Pérez es dueño del Depto 5 en Torre A y es quien paga las expensas.

1. Verificar que Juan Pérez tenga rol **"Propietario"**
2. Crear relación:
   - Usuario: Juan Pérez
   - Unidad: Torre A / Piso 2 / Depto 5
   - ✅ Es Propietario
   - ✅ Es Responsable del Pago
   - Fecha de Inicio: 01/01/2024
   - Fecha de Fin: (vacío)

### Ejemplo 2: Asignar Inquilino a una Unidad

**Escenario**: María López alquila el Depto 8 desde marzo 2025 y paga el alquiler directamente.

1. Verificar que María López tenga rol **"Inquilino"**
2. Crear relación:
   - Usuario: María López
   - Unidad: Torre B / Piso 3 / Depto 8
   - ❌ Es Propietario (no marcar)
   - ✅ Es Responsable del Pago
   - Fecha de Inicio: 01/03/2025
   - Fecha de Fin: (vacío)
   - Notas: "Contrato por 2 años hasta marzo 2027"

### Ejemplo 3: Unidad con Propietario e Inquilino

**Escenario**: Carlos Gómez es dueño del Depto 3, pero está alquilado a Ana Martínez quien paga las expensas.

**Paso A - Asignar Propietario:**
1. Verificar que Carlos Gómez tenga rol **"Propietario"**
2. Crear relación:
   - Usuario: Carlos Gómez
   - Unidad: Torre A / Piso 1 / Depto 3
   - ✅ Es Propietario
   - ❌ Es Responsable del Pago (la inquilina paga)
   - Fecha de Inicio: 01/01/2023

**Paso B - Asignar Inquilino:**
1. Verificar que Ana Martínez tenga rol **"Inquilino"**
2. Crear relación:
   - Usuario: Ana Martínez
   - Unidad: Torre A / Piso 1 / Depto 3 (misma unidad)
   - ❌ Es Propietario
   - ✅ Es Responsable del Pago
   - Fecha de Inicio: 01/06/2025
   - Notas: "Alquiler por 18 meses"

---

## Gestión de Relaciones

### Ver Todas las Asignaciones

1. Ir a **Admin → Unidades Funcionales**
2. Ver listado completo con filtros:
   - Filtrar por usuario
   - Filtrar por unidad
   - Filtrar por estado (Activa/Inactiva)

### Editar una Asignación

1. En el listado, hacer clic en **"Editar"** (ícono de lápiz)
2. Modificar los datos necesarios
3. Guardar cambios

**Casos comunes:**
- Cambiar responsable de pago
- Agregar fecha de fin cuando termina la relación
- Actualizar notas

### Finalizar una Relación

Cuando un inquilino se muda o un propietario vende:

1. Editar la relación
2. Completar el campo **"Fecha de Fin"** con la fecha de término
3. Guardar
4. La relación pasa a estado "Inactiva" automáticamente

### Eliminar una Asignación

Solo si fue creada por error:

1. En el listado, hacer clic en **"Eliminar"** (ícono de basura)
2. Confirmar la eliminación

**Nota**: Se recomienda usar "Fecha de Fin" en lugar de eliminar, para mantener el historial.

---

## Mensajes de Error Comunes

### "El usuario seleccionado no tiene un rol asignado"
**Solución**: Ir a Admin → Usuarios y asignar rol "Propietario" o "Inquilino" al usuario primero.

### "Solo los usuarios con rol Propietario pueden ser propietarios de una unidad"
**Solución**: Si marcó "Es Propietario", el usuario debe tener rol "Propietario". Cambiar el rol del usuario o desmarcar la opción.

### "Ya existe una relación activa entre este usuario y esta unidad funcional"
**Solución**: Ya hay una asignación activa. Editar la existente o finalizarla primero (agregar fecha de fin).

### "Esta unidad funcional ya tiene un propietario activo"
**Solución**: Solo puede haber un propietario activo. Finalizar la relación del propietario anterior antes de asignar uno nuevo.

### "Esta unidad funcional ya tiene un inquilino activo"
**Solución**: Solo puede haber un inquilino activo. Finalizar la relación del inquilino anterior antes de asignar uno nuevo.

### "Esta unidad funcional ya tiene un responsable de pago activo"
**Solución**: Solo puede haber un responsable de pago. Editar la relación existente o cambiar el responsable.

---

## Preguntas Frecuentes

**¿Un usuario puede tener varias unidades?**
Sí, un propietario puede ser dueño de múltiples unidades. Crear una relación separada para cada unidad.

**¿Puedo asignar un inquilino sin que haya un propietario?**
Sí, el sistema permite asignar inquilinos independientemente. Sin embargo, se recomienda tener siempre un propietario registrado.

**¿Cómo cambio el responsable de pago de propietario a inquilino?**
1. Editar la relación del propietario: desmarcar "Es Responsable del Pago"
2. Editar la relación del inquilino: marcar "Es Responsable del Pago"

**¿Qué pasa con las relaciones inactivas?**
Se mantienen en el sistema como historial. Pueden verse filtrando por estado "Inactiva" en el listado.

**¿Un usuario puede ser propietario e inquilino a la vez?**
Sí, en diferentes unidades. Un usuario puede ser propietario de una unidad e inquilino de otra.

---

## Flujo Completo Recomendado

1. **Crear usuario** (si no existe) en Admin → Usuarios
2. **Asignar rol** al usuario: Propietario o Inquilino
3. **Verificar unidad** existe en Admin → Edificios → Unidades
4. **Crear relación** en Admin → Unidades Funcionales → Asignar Usuario
5. **Especificar rol** en la unidad: Propietario/Inquilino y Responsable de Pago
6. **Documentar** en notas cualquier información relevante
7. **Actualizar** la relación cuando cambie (ej: nuevo inquilino)

---

## Soporte

Para problemas o consultas adicionales, contactar al administrador del sistema.
