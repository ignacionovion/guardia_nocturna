# GuardiAPP SaaS - Referencia Rápida

## Comandos Útiles

```bash
# Crear nuevo tenant
php artisan tenant:create {slug} "{nombre}" {subdominio} --numero=N --body=ID --plan=basico --seed

# Migrar todos los tenants
php artisan tenant:migrate-all

# Backup de todos los tenants
php artisan tenant:backup

# Ejecutar comando en todos los tenants
php artisan tenant:run "cache:clear"

# Limpiar cache
php artisan optimize:clear
```

## URLs

| Ambiente | Panel Central | Tenant |
|----------|---------------|--------|
| Local | localhost:8000/admin | {slug}.localhost:8000 |
| Staging | sas.dev-app.cl/admin | {slug}.sas.dev-app.cl |

## Credenciales por Defecto

**Panel Central:**
- Email: admin@guardianocturna.cl
- Password: password

## Feature Flags en Código

```php
// En controladores/servicios
if (feature('inventario')) {
    // código
}

// En rutas
Route::get('/ruta', ...)->middleware('feature:inventario');

// En vistas Blade
@if(feature('reportes'))
    <a href="/reportes">Ver Reportes</a>
@endif
```

## Estructura de Archivos SaaS

```
app/
├── Console/Commands/
│   ├── TenantCreateCommand.php      # Crear tenant
│   ├── TenantRunCommand.php         # Ejecutar comando en tenants
│   ├── TenantMigrateAllCommand.php  # Migraciones masivas
│   └── TenantBackupCommand.php      # Backups
├── Http/
│   ├── Controllers/Central/         # Controllers panel central
│   └── Middleware/
│       ├── EnsureTenantActive.php   # Bloquea tenants inactivos
│       └── EnforceFeatureFlag.php   # Middleware feature:xxx
├── Services/
│   ├── TenantMetricsService.php     # Métricas por tenant
│   └── FeatureFlagService.php       # Feature flags
├── TenancyBootstrappers/
│   └── TenantLogBootstrapper.php    # Logs por tenant
└── helpers.php                       # Helper feature()

config/
├── tenancy.php                       # Config stancl/tenancy
└── logging.php                       # Canal 'tenant'

routes/
├── central.php                       # Rutas panel central
├── tenant.php                        # Wrapper subdominios
└── app.php                           # App operativa (tenant)

resources/views/
├── central/                          # Vistas panel central
├── suspended.blade.php               # Página tenant suspendido
└── feature-disabled.blade.php        # Página feature deshabilitada

storage/
├── app/backups/                      # Backups SQL
└── logs/tenants/                     # Logs por tenant
```

## Planes y Límites

| Plan | Max Users | Inventario | Reportes | API |
|------|-----------|------------|----------|-----|
| Básico | 10 | ✗ | ✗ | ✗ |
| Profesional | 50 | ✓ | ✓ | ✗ |
| Enterprise | ∞ | ✓ | ✓ | ✓ |

## Health Checks

- **DB existe**: La base de datos tenant_{slug} existe
- **Dominio**: Tiene al menos un dominio configurado
- **Migraciones**: Tiene migraciones ejecutadas
- **Usuarios**: Tiene al menos un usuario registrado
- **Vencimiento**: fecha_vencimiento no ha pasado

## Troubleshooting

**419 PAGE EXPIRED:**
- Usar mismo dominio (no mezclar 127.0.0.1 con localhost)

**Tenant no carga:**
1. Verificar dominio en tabla `domains`
2. Verificar DB existe: `SHOW DATABASES LIKE 'tenant_%'`
3. Ver logs: `storage/logs/tenants/{slug}.log`

**Migraciones fallan:**
```bash
php artisan tenant:migrate-all --tenant=slug --force
```
