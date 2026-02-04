# Importación Dotación (Voluntarios)

## Formato de archivo
- CSV o XLSX
- Primera fila: cabecera (se ignora)
- Columnas obligatorias: `nombres`, `apellido_paterno`, `rut`

## Columnas (A-M)
A: nombres
B: apellido_paterno
C: apellido_materno (opcional)
D: rut
E: cargo (opcional, sugerido usar uno de: director, secretario, tesorero, capitan, teniente 1, teniente2, teniente 3, teniente 4, ayudante, ayudante1, ayudante 2, ayudante 3, pro secretario, pro tesorero, Administrativo)
F: portatil (opcional, texto. Ej: 364, 37-D)
G: fecha_cumpleanos (opcional)
H: guardia_id (opcional)
I: fecha_ingreso (opcional)
J: conductor (opcional, valores aceptados: 1 / si / sí / true / x / yes) (opcional)
K: operador_rescate (opcional, mismos valores) (opcional)
L: asistente_trauma (opcional, mismos valores) (opcional)
M: email (opcional)

## Notas
- Estos registros corresponden a personal (bomberos) y no a cuentas de inicio de sesión.
- El email se genera automáticamente con dominio `@system.local` solo para cumplir con el requisito de unicidad en la base de datos.
