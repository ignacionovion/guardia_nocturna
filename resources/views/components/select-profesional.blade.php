{{-- 
    Componente Select Profesional Estandarizado - Admin/Capitan
    Uso: @include('components.select-profesional', [
        'name' => 'campo',
        'label' => 'Etiqueta',
        'icon' => 'fas fa-icon',
        'options' => [],
        'value' => null,
        'placeholder' => 'Seleccionar...'
    ])
--}}

@php
$icon = $icon ?? 'fas fa-chevron-down';
$placeholder = $placeholder ?? 'Seleccionar...';
$id = $id ?? $name;
@endphp

<div class="select-profesional-wrapper {{ $wrapperClass ?? '' }}">
    @if(isset($label))
    <label for="{{ $id }}" class="select-profesional-label">{{ $label }}</label>
    @endif
    
    <div class="select-profesional-container">
        @if(isset($icon))
        <div class="select-profesional-icon">
            <i class="{{ $icon }}"></i>
        </div>
        @endif
        
        <select 
            name="{{ $name }}" 
            id="{{ $id }}"
            class="select-profesional {{ $class ?? '' }}"
            {{ $attributes ?? '' }}
            @if(isset($required)) required @endif
            @if(isset($disabled)) disabled @endif
        >
            @if(isset($placeholder))
            <option value="">{{ $placeholder }}</option>
            @endif
            
            @foreach($options as $key => $option)
                @php
                    $optionValue = is_array($option) ? ($option['value'] ?? $key) : $key;
                    $optionLabel = is_array($option) ? ($option['label'] ?? $option) : $option;
                    $isSelected = ($value ?? old($name)) == $optionValue;
                @endphp
                <option value="{{ $optionValue }}" {{ $isSelected ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
        
        <div class="select-profesional-arrow">
            <div class="select-profesional-arrow-box">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </div>
    
    @if(isset($help))
    <small class="select-profesional-help">{{ $help }}</small>
    @endif
</div>

<style>
/* Select Profesional Estandarizado - Sistema Admin/Capitan */
.select-profesional-wrapper {
    margin-bottom: 1rem;
}

.select-profesional-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.select-profesional-container {
    position: relative;
    display: flex;
    align-items: center;
}

.select-profesional {
    width: 100%;
    padding: 0.75rem 2.75rem 0.75rem 2.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 0.75rem;
    appearance: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.select-profesional:hover {
    background: #ffffff;
    border-color: #cbd5e1;
}

.select-profesional:focus {
    outline: none;
    background: #ffffff;
    border-color: #0ea5e9;
    box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
}

.select-profesional:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #f1f5f9;
}

/* Icono izquierdo */
.select-profesional-icon {
    position: absolute;
    left: 0.875rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.875rem;
    pointer-events: none;
    transition: color 0.2s ease;
    z-index: 2;
}

.select-profesional-container:focus-within .select-profesional-icon {
    color: #0ea5e9;
}

/* Flecha derecha */
.select-profesional-arrow {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    z-index: 2;
}

.select-profesional-arrow-box {
    width: 1.75rem;
    height: 1.75rem;
    background: #f1f5f9;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.select-profesional-arrow-box i {
    font-size: 0.625rem;
    color: #64748b;
    transition: transform 0.2s ease;
}

.select-profesional-container:focus-within .select-profesional-arrow-box {
    background: #0ea5e9;
}

.select-profesional-container:focus-within .select-profesional-arrow-box i {
    color: #ffffff;
    transform: rotate(180deg);
}

/* Texto de ayuda */
.select-profesional-help {
    display: block;
    margin-top: 0.375rem;
    font-size: 0.75rem;
    color: #94a3b8;
}

/* Variantes de color */
.select-profesional-wrapper.variant-red .select-profesional:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}
.select-profesional-wrapper.variant-red .select-profesional-container:focus-within .select-profesional-icon,
.select-profesional-wrapper.variant-red .select-profesional-container:focus-within .select-profesional-arrow-box {
    color: #ef4444;
    background: #ef4444;
}

.select-profesional-wrapper.variant-emerald .select-profesional:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
}
.select-profesional-wrapper.variant-emerald .select-profesional-container:focus-within .select-profesional-icon,
.select-profesional-wrapper.variant-emerald .select-profesional-container:focus-within .select-profesional-arrow-box {
    color: #10b981;
    background: #10b981;
}

.select-profesional-wrapper.variant-violet .select-profesional:focus {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}
.select-profesional-wrapper.variant-violet .select-profesional-container:focus-within .select-profesional-icon,
.select-profesional-wrapper.variant-violet .select-profesional-container:focus-within .select-profesional-arrow-box {
    color: #8b5cf6;
    background: #8b5cf6;
}

/* Tamaños */
.select-profesional-wrapper.size-sm .select-profesional {
    padding: 0.5rem 2.5rem 0.5rem 2.25rem;
    font-size: 0.8125rem;
}
.select-profesional-wrapper.size-sm .select-profesional-icon {
    left: 0.75rem;
    font-size: 0.75rem;
}
.select-profesional-wrapper.size-sm .select-profesional-arrow-box {
    width: 1.5rem;
    height: 1.5rem;
}

.select-profesional-wrapper.size-lg .select-profesional {
    padding: 1rem 3rem 1rem 3rem;
    font-size: 1rem;
}
.select-profesional-wrapper.size-lg .select-profesional-icon {
    left: 1rem;
    font-size: 1rem;
}
.select-profesional-wrapper.size-lg .select-profesional-arrow-box {
    width: 2rem;
    height: 2rem;
}
</style>
