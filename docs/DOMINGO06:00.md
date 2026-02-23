No, el sistema hace mucho más que solo las camas.

Hay dos comandos diferentes que usan el mismo horario (guardia_week_cleanup_time = 18:00):

1. guardia:reset-beds (líneas 375-401)
Solo hace:

Libera camas ocupadas
Resetea estado a "disponible"
2. guardia:weekly-archive-clean (líneas 523-706) - El más completo
Este hace todo lo demás:

Acción	Descripción
Archivar Novedades	Guarda todas las novedades/academias en GuardiaArchiveItem
Archivar Emergencias	Guarda todas las emergencias de la semana
Archivar Aseo	Guarda las asignaciones de limpieza
Archivar Camas	Snapshot de asignaciones de camas activas
Devolver Refuerzos	Los refuerzos vuelven a sus guardias originales
Borrar Novedades	Elimina novedades de la guardia saliente
Borrar Aseo	Elimina asignaciones de limpieza
Borrar Emergencias	Elimina emergencias de la guardia saliente
Resetear Camas	Libera todas las camas (lo mismo que el comando 1)
Resumen: El guardia:weekly-archive-clean es el proceso completo de cierre semanal que archiva todo el historial de la guardia saliente, devuelve refuerzos, limpia datos operativos y resetea camas.

