# EstacionAPP UI Kit

Sistema de diseño base para SaaS moderno. Solo aplica al **perfil CAPITAN (admin)**.

## 🎨 Filosofía de Diseño

- **Fondo suave**: Gris muy suave (#f8fafc) en lugar de blanco puro
- **Cards elevadas**: Blancas con sombra leve y bordes suaves
- **Espaciado consistente**: Escala de 4px, 8px, 12px, 16px, 24px, 32px
- **Tipografía jerárquica**: Títulos claros, texto legible
- **Look profesional**: Estilo SaaS moderno (Linear, Stripe Dashboard)

## 🎨 Colores

```css
/* Rojo principal (branding) */
--color-brand-red: #dc2626
--color-brand-red-dark: #b91c1c
--color-brand-red-light: #ef4444

/* Grises */
--color-bg-page: #f8fafc       /* Fondo de página */
--color-bg-card: #ffffff       /* Fondo de cards */
--color-border: #e2e8f0        /* Bordes */
--color-text-primary: #1e293b  /* Texto principal */
--color-text-secondary: #64748b /* Texto secundario */
```

## 📦 Componentes Blade

### 1. Page Container

Contenedor principal de páginas con header opcional.

```blade
<x-ui.page-container title="Dashboard" subtitle="Vista general del sistema">
    <x-slot:actions>
        <x-ui.button variant="primary" icon="fas fa-plus">
            Nuevo
        </x-ui.button>
    </x-slot:actions>
    
    <!-- Contenido aquí -->
</x-ui.page-container>
```

### 2. Input

Input profesional con label, hint y error.

```blade
<x-ui.input 
    label="Nombre Completo" 
    placeholder="Ingrese nombre"
    hint="Nombre y apellido del bombero"
    icon="fas fa-user"
/>
```

**Props:**
- `type` (default: 'text')
- `label` (opcional)
- `error` (opcional)
- `hint` (opcional)
- `icon` (opcional, FontAwesome)

### 3. Select

Select consistente con label y error.

```blade
<x-ui.select label="Guardia" icon="fas fa-shield-halved">
    <option value="">Seleccione...</option>
    <option value="1">Guardia 1</option>
    <option value="2">Guardia 2</option>
</x-ui.select>
```

**Props:**
- `label` (opcional)
- `error` (opcional)
- `hint` (opcional)
- `icon` (opcional)

### 4. Card

Card profesional con header y footer opcionales.

```blade
<x-ui.card padding="default" hover elevated>
    <x-slot:header>
        <h3 class="font-semibold text-slate-900">Título de Card</h3>
    </x-slot:header>
    
    Contenido de la card
    
    <x-slot:footer>
        <x-ui.button size="sm">Acción</x-ui.button>
    </x-slot:footer>
</x-ui.card>
```

**Props:**
- `padding`: 'none', 'sm', 'default', 'lg'
- `hover`: true/false (efecto hover)
- `elevated`: true/false (sombra)

### 5. Button

Botón con variantes y tamaños.

```blade
<x-ui.button variant="primary" size="md" icon="fas fa-save">
    Guardar
</x-ui.button>
```

**Variantes:**
- `primary` (rojo)
- `secondary` (blanco con borde)
- `danger` (rojo)
- `success` (verde)
- `warning` (amarillo)
- `ghost` (transparente)
- `outline` (borde)

**Tamaños:**
- `xs`, `sm`, `md`, `lg`, `xl`

## 🧱 Clases CSS Reutilizables

### Inputs y Selects

```html
<input class="input-base" type="text" placeholder="Nombre">
<select class="select-base">
    <option>Opción 1</option>
</select>
```

### Cards

```html
<div class="card-base">
    Contenido de card
</div>

<!-- Card con header -->
<div class="card-base">
    <div class="card-header">
        <h3>Título</h3>
    </div>
    <div class="card-body">
        Contenido
    </div>
    <div class="card-footer">
        Acciones
    </div>
</div>
```

### Métricas

```html
<div class="metric-card">
    <div class="metric-value">24</div>
    <div class="metric-label">Turnos Activos</div>
</div>
```

### Formularios

```html
<div class="form-group">
    <label class="form-label">Nombre</label>
    <input class="input-base" type="text">
    <p class="form-hint">Ingrese el nombre completo</p>
</div>
```

### Botones

```html
<button class="btn-primary">Guardar</button>
<button class="btn-secondary">Cancelar</button>
<button class="btn-danger">Eliminar</button>
<button class="btn-success">Aprobar</button>
<button class="btn-ghost">Ver más</button>
```

### Tablas

```html
<div class="table-container">
    <table class="table-base">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Guardia</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Juan Pérez</td>
                <td>Guardia 1</td>
                <td><span class="badge-success">Activo</span></td>
            </tr>
        </tbody>
    </table>
</div>
```

### Badges

```html
<span class="badge-success">Activo</span>
<span class="badge-warning">Pendiente</span>
<span class="badge-danger">Inactivo</span>
<span class="badge-info">Nuevo</span>
<span class="badge-neutral">Normal</span>
```

### Alertas

```html
<div class="alert-success">
    <i class="fas fa-check-circle"></i>
    <div>
        <strong>Éxito!</strong> La operación se completó correctamente.
    </div>
</div>

<div class="alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
        <strong>Advertencia!</strong> Revise los datos antes de continuar.
    </div>
</div>

<div class="alert-danger">
    <i class="fas fa-times-circle"></i>
    <div>
        <strong>Error!</strong> No se pudo completar la operación.
    </div>
</div>
```

### Estados Vacíos

```html
<div class="empty-state">
    <i class="fas fa-inbox empty-state-icon"></i>
    <h3 class="empty-state-title">No hay datos</h3>
    <p class="empty-state-description">No se encontraron registros para mostrar.</p>
    <x-ui.button variant="primary" size="sm">Agregar Nuevo</x-ui.button>
</div>
```

## 📐 Layouts de Grid

```html
<!-- 2 columnas -->
<div class="grid-2">
    <div class="card-base">Card 1</div>
    <div class="card-base">Card 2</div>
</div>

<!-- 3 columnas -->
<div class="grid-3">
    <div class="card-base">Card 1</div>
    <div class="card-base">Card 2</div>
    <div class="card-base">Card 3</div>
</div>

<!-- 4 columnas -->
<div class="grid-4">
    <div class="metric-card">Métrica 1</div>
    <div class="metric-card">Métrica 2</div>
    <div class="metric-card">Métrica 3</div>
    <div class="metric-card">Métrica 4</div>
</div>
```

## 📏 Espaciado Consistente

```html
<div class="spacing-xs">Gap de 4px</div>
<div class="spacing-sm">Gap de 8px</div>
<div class="spacing-md">Gap de 16px</div>
<div class="spacing-lg">Gap de 24px</div>
<div class="spacing-xl">Gap de 32px</div>
```

## 🎯 Ejemplo Completo: Formulario

```blade
<x-ui.page-container title="Nuevo Bombero" subtitle="Registro de personal">
    <x-slot:actions>
        <x-ui.button variant="secondary" href="{{ route('admin.bomberos') }}">
            Cancelar
        </x-ui.button>
        <x-ui.button variant="primary" type="submit" icon="fas fa-save">
            Guardar
        </x-ui.button>
    </x-slot:actions>
    
    <form method="POST" action="{{ route('admin.bomberos.store') }}">
        @csrf
        
        <x-ui.card>
            <x-slot:header>
                <h3 class="font-semibold text-slate-900">Información Personal</h3>
            </x-slot:header>
            
            <div class="grid-2">
                <x-ui.input 
                    label="Nombres" 
                    name="nombres"
                    placeholder="Ej: Juan Carlos"
                    icon="fas fa-user"
                    :error="$errors->first('nombres')"
                />
                
                <x-ui.input 
                    label="Apellido Paterno" 
                    name="apellido_paterno"
                    placeholder="Ej: González"
                    icon="fas fa-user"
                    :error="$errors->first('apellido_paterno')"
                />
            </div>
            
            <div class="grid-2 mt-4">
                <x-ui.select 
                    label="Guardia" 
                    name="guardia_id"
                    icon="fas fa-shield-halved"
                    :error="$errors->first('guardia_id')"
                >
                    <option value="">Seleccione...</option>
                    @foreach($guardias as $guardia)
                        <option value="{{ $guardia->id }}">{{ $guardia->name }}</option>
                    @endforeach
                </x-ui.select>
                
                <x-ui.input 
                    label="Número de Registro" 
                    name="numero_registro"
                    type="number"
                    placeholder="Ej: 1234"
                    icon="fas fa-hashtag"
                    :error="$errors->first('numero_registro')"
                />
            </div>
        </x-ui.card>
    </form>
</x-ui.page-container>
```

## 🎯 Ejemplo Completo: Dashboard

```blade
<x-ui.page-container title="Dashboard" subtitle="Vista general del sistema">
    <!-- Métricas -->
    <div class="grid-4 mb-6">
        <div class="metric-card">
            <div class="metric-value">24</div>
            <div class="metric-label">Turnos Activos</div>
        </div>
        <div class="metric-card">
            <div class="metric-value">12</div>
            <div class="metric-label">Camas Ocupadas</div>
        </div>
        <div class="metric-card">
            <div class="metric-value">8</div>
            <div class="metric-label">Emergencias Hoy</div>
        </div>
        <div class="metric-card">
            <div class="metric-value">156</div>
            <div class="metric-label">Bomberos Activos</div>
        </div>
    </div>
    
    <!-- Tabla de Guardias -->
    <x-ui.card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-slate-900">Guardias Activas</h3>
                <x-ui.button variant="ghost" size="sm" icon="fas fa-filter">
                    Filtrar
                </x-ui.button>
            </div>
        </x-slot:header>
        
        <div class="table-container">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>Guardia</th>
                        <th>Dotación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Guardia 1</td>
                        <td>12 bomberos</td>
                        <td><span class="badge-success">Operativa</span></td>
                        <td>
                            <x-ui.button variant="ghost" size="xs" icon="fas fa-eye">
                                Ver
                            </x-ui.button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-ui.page-container>
```

## ⚠️ Importante

- **NO tocar** vistas del perfil GUARDIA
- **NO modificar** rutas ni controladores
- **Solo aplicar** al perfil CAPITAN (admin)
- **Mantener** compatibilidad con Blade + Tailwind

## 🚀 Próximos Pasos

1. Compilar assets: `npm run build`
2. Aplicar componentes en vistas existentes
3. Reemplazar inputs/selects antiguos por clases base
4. Mejorar dashboard con nuevas métricas
5. Escalar a módulos: camas, QR, planillas

## 📚 Recursos

- Tailwind CSS: https://tailwindcss.com
- Laravel Blade: https://laravel.com/docs/blade
- FontAwesome Icons: https://fontawesome.com
