# Documentación del Panel de Administración de Tenant

## Resumen

Esta documentación explica cada sección visible en el panel de administración central de una compañía (tenant), detallando qué hace cada elemento, si está funcionando actualmente o es solo visual, y cómo se comporta.

---

## 1. Uso del Plan

### ¿Qué es?
La sección **"Uso del Plan"** muestra en tiempo real cuántos recursos está utilizando una compañía vs. los límites permitidos por su plan SaaS contratado.

### ¿Funciona actualmente?
**SÍ - Funcional completo.** Esta sección fue implementada recientemente y muestra datos reales.

### ¿Qué información muestra?

| Métrica | Descripción | Ejemplo en imagen |
|---------|-------------|-------------------|
| **Usuarios** | Cantidad de usuarios registrados en el tenant | 2 / 15 (usados / límite) |
| **Guardias** | Cantidad de usuarios con rol "guardia" | 0 / 50 |
| **Camas** | Cantidad de camas registradas en el sistema | 10 / 30 |
| **Almacenamiento** | Espacio de archivos usado en MB | 0 / 500 |

### Barra de progreso
- **Verde claro** (< 70%): Uso normal
- **Naranja** (70-90%): Se acerca al límite
- **Rojo** (> 90%): Cerca de exceder el límite

### ¿Es solo texto o tiene impacto real?
**Tiene impacto real.** El sistema valida estos límites cuando:
- Se crea un nuevo usuario (bloquea si se excede el límite)
- Se agrega una cama
- Se suben archivos

Los planes disponibles son:
- **Básico**: 5 usuarios, 20 guardias, 10 camas, 100MB (Gratis)
- **Profesional**: 15 usuarios, 50 guardias, 30 camas, 500MB ($29.99/mes)
- **Enterprise**: Ilimitado (excepto storage 5GB), $45.000/mes

### Cambio de Plan
En el panel central (vista show del tenant) hay un formulario para cambiar el plan de una compañía. Esto:
1. Actualiza inmediatamente los límites
2. Registra el cambio en auditoría
3. No afecta datos existentes, solo habilita/deshabilita crear más

---

## 2. Feature Flags

### ¿Qué son?
Los **Feature Flags** (banderas de características) son toggles que permiten activar o desactivar funcionalidades específicas para una compañía, independientemente de su plan.

### ¿Funciona actualmente?
**SÍ - Funcional.** Al guardar, los cambios se aplican inmediatamente.

### ¿Qué pasa si desactivo una feature?
**La funcionalidad se deshabilita en el sistema del tenant.** Por ejemplo:
- Si quitas "Emergencias", el módulo de emergencias desaparece del menú
- Si quitas "Inventario", no se podrá acceder a la gestión de inventario
- Si quitas "Reportes Avanzados", solo quedarán disponibles los reportes básicos

### Features disponibles

| Feature | Qué controla | Comportamiento al desactivar |
|---------|--------------|------------------------------|
| **Emergencias** | Módulo de gestión de emergencias | Desaparece del menú lateral |
| **Guardias Nocturnas** | Registro de guardias nocturnas | No se pueden crear turnos |
| **Gestión de Camas** | Módulo de camas/cuarteles | Desaparece del menú |
| **Inventario** | Sistema de inventario | No accesible |
| **Dotaciones** | Gestión de equipamiento | Deshabilitado |
| **Reportes Avanzados** | Reportes detallados vs. básicos | Solo reportes simples disponibles |
| **Acceso API** | Endpoints REST API | Devuelve 403 Forbidden |
| **Marca Personalizada** | Logo/colores personalizados | Vuelve a branding genérico |
| **Backups Automáticos** | Backups programados | Solo backups manuales |
| **Máx. Usuarios** | Límite numérico de usuarios | Campo editable (-1 = ilimitado) |

### ¿De dónde salen los valores por defecto?
El texto "Los valores por defecto provienen del plan Profesional" indica que cada plan tiene configuradas sus features base. Los Feature Flags permiten **sobrescribir** estas configuraciones para un tenant específico.

### Personalización por tenant
Si modificas un Feature Flag manualmente, aparecerá una etiqueta **"CUSTOM"** indicando que ese valor no viene del plan por defecto, sino que fue personalizado para esa compañía.

---

## 3. Acciones Manuales

### ¿Qué son?
Botones que permiten ejecutar operaciones técnicas sobre la base de datos de un tenant específico. Son acciones de mantenimiento y administración.

### ¿Funcionan?
**SÍ - Todas funcionan.** Son operaciones reales sobre la base de datos.

### Descripción de cada acción:

#### 3.1 Correr Migraciones
- **¿Qué son migraciones?**
  En Laravel, las migraciones son archivos PHP que definen la estructura de la base de datos (tablas, columnas, índices). Son como "versiones" del esquema de BD.

- **¿Cuándo usarlo?**
  - Después de actualizar el código y agregar nuevas tablas
  - Si una compañía tiene la BD vacía o incompleta
  - Para aplicar cambios de estructura sin afectar datos

- **¿Qué hace exactamente?**
  Ejecuta `php artisan migrate` específicamente en la base de datos de este tenant, creando las tablas que falten.

#### 3.2 Correr Seeders
- **¿Qué son seeders?**
  Los seeders son scripts que insertan datos iniciales (ej: usuario admin, tipos de camas, configuraciones por defecto).

- **¿Cuándo usarlo?**
  - Cuando se crea una compañía nueva y necesita datos de ejemplo
  - Para restaurar datos por defecto que se borraron
  - **⚠️ Precaución:** Si ya hay datos, puede crear duplicados

- **¿Qué hace exactamente?**
  Ejecuta `php artisan db:seed` en la BD del tenant, insertando datos iniciales.

#### 3.3 Abrir App del Tenant
- **¿Qué hace?**
  Abre la aplicación del tenant en una nueva pestaña, usando su subdominio.
  - Ejemplo: `cuarta-temuco.tudominio.com`

- **¿Para qué sirve?**
  Para acceder rápidamente al sistema como si fueras un usuario de esa compañía, sin necesidad de buscar la URL.

#### 3.4 Ver Timeline
- **¿Qué es el Timeline?**
  Un registro cronológico de todas las acciones realizadas en esta compañía:
  - Cuándo se creó
  - Cambios de plan
  - Ejecución de migraciones
  - Modificaciones de features
  - Quién hizo qué y cuándo

- **¿Para qué sirve?**
  Auditoría y troubleshooting. Si algo dejó de funcionar, se puede revisar qué cambió recientemente.

#### 3.5 Administración Técnica
- **¿Qué contiene?**
  Una sección avanzada con:
  - **Explorador de datos**: Ver tablas y registros de la BD del tenant
  - **Reset de BD**: Elimina todas las tablas y reconstruye desde cero
  - **Eliminación completa**: Borra tenant + BD + backups

- **¿Para qué sirve?**
  - Diagnóstico de problemas (ver qué hay en las tablas)
  - Recuperación de desastres (reset completo)
  - Soporte técnico avanzado

### Sección "Peligro"

#### Eliminar Compañía
- **¿Qué hace?**
  Elimina permanentemente:
  1. El registro de la compañía en la tabla `tenants`
  2. Todos sus dominios asociados
  3. La base de datos completa del tenant
  4. Archivos de backup asociados

- **¿Es reversible?**
  **NO.** Una vez eliminada, solo se puede recuperar desde un backup externo.

- **¿Cuándo usarlo?**
  - Cuando una compañía cancela su servicio definitivamente
  - Para limpiar tenants de prueba
  - Cuando se creó incorrectamente y hay que empezar de cero

---

## 4. Datos Técnicos

### Información mostrada

| Campo | Valor de ejemplo | Significado |
|-------|------------------|-------------|
| **Base de datos** | `tenant_cuarta-temuco` | Nombre de la BD MySQL |
| **DB existe** | Sí/No | Verifica si la BD física existe en el servidor |
| **Dominios** | `cuarta-temuco` | Subdominios configurados para acceder |

### Auto-provisioning
Estos 4 pasos muestran el estado del aprovisionamiento automático cuando se crea una compañía:

1. **Crear DB** - Se creó la base de datos física
2. **Correr migraciones** - Se crearon las tablas necesarias
3. **Correr seed** - Se insertaron datos iniciales
4. **Crear admin** - Se creó el usuario administrador

- **Números verdes** = Paso completado exitosamente
- **Signos de exclamación** = Paso falló o está pendiente
- **Rayas** = Paso omitido (ej: no se solicitó seed)

---

## Resumen de Funcionalidad

| Sección | Estado | Impacto Real |
|---------|--------|--------------|
| Uso del Plan | ✅ Funciona | Limita creación de usuarios/camas |
| Feature Flags | ✅ Funciona | Habilita/deshabilita módulos |
| Acciones Manuales | ✅ Funciona | Operaciones reales sobre BD |
| Cambio de Plan | ✅ Funciona | Actualiza límites inmediatamente |
| Auto-provisioning | ✅ Funciona | Crea la infraestructura del tenant |

---

## Notas Técnicas

- Todo el sistema usa la **conexión 'central'** para estas operaciones
- Los cambios se registran automáticamente en la tabla `central_audit_logs`
- El middleware `EnforcePlanLimits` protege las rutas sensibles
- Los helpers globales `feature()`, `plan_limit()`, `plan_usage()` están disponibles en todas las vistas
