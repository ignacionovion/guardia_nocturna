{{--
    GUARDIAPP DESIGN SYSTEM
    Sistema de diseño unificado para toda la aplicación
    
    USO: @include('components.design-system') en layouts
    
    JERARQUÍA TIPOGRÁFICA:
    - .text-title-xl     → Títulos principales de página
    - .text-title-lg     → Títulos de sección/cards grandes
    - .text-title-md     → Títulos de cards/bloques
    - .text-title-sm     → Subtítulos y encabezados menores
    - .text-body         → Texto normal de contenido
    - .text-body-sm      → Texto secundario
    - .text-muted        → Texto terciario/auxiliar
    - .text-label        → Labels de formularios
    - .text-caption      → Captions y texto muy pequeño
    
    COLORES DE ESTADO:
    - .text-success      → Verde para éxito/activo
    - .text-warning      → Ámbar para advertencias
    - .text-danger       → Rojo para errores/peligro
    - .text-info         → Azul para información
    
    CARDS:
    - .card-base         → Card estándar
    - .card-elevated     → Card con sombra
    - .card-interactive  → Card con hover
--}}

<style>
    /* ========================================
       SISTEMA TIPOGRÁFICO GLOBAL
       ======================================== */
    
    /* Títulos */
    .text-title-xl {
        @apply text-2xl sm:text-3xl font-bold text-[#1e293b] tracking-tight;
    }
    .text-title-lg {
        @apply text-xl sm:text-2xl font-bold text-[#1e293b];
    }
    .text-title-md {
        @apply text-lg font-semibold text-[#1e293b];
    }
    .text-title-sm {
        @apply text-base font-semibold text-[#1e293b];
    }
    
    /* Texto de contenido */
    .text-body {
        @apply text-sm text-[#1e293b];
    }
    .text-body-sm {
        @apply text-sm text-[#475569];
    }
    .text-muted {
        @apply text-xs text-[#475569];
    }
    
    /* Labels y captions */
    .text-label {
        @apply text-xs font-semibold text-[#475569] uppercase tracking-wider;
    }
    .text-caption {
        @apply text-[11px] text-[#475569];
    }
    
    /* Estados */
    .text-success {
        @apply text-emerald-600 dark:text-emerald-400;
    }
    .text-warning {
        @apply text-amber-600 dark:text-amber-400;
    }
    .text-danger {
        @apply text-red-600 dark:text-red-400;
    }
    .text-info {
        @apply text-blue-600 dark:text-blue-400;
    }
    
    /* ========================================
       SISTEMA DE CARDS
       ======================================== */
    
    .card-base {
        @apply bg-[#dde6ef] rounded-2xl border border-[#9fb0c3];
    }
    .card-elevated {
        @apply card-base shadow-sm;
    }
    .card-interactive {
        @apply card-base transition-all duration-200 hover:shadow-lg hover:border-[#7d94ab];
    }
    
    /* Padding estándar para cards */
    .card-padding {
        @apply p-5 sm:p-6;
    }
    .card-padding-sm {
        @apply p-4;
    }
    .card-padding-lg {
        @apply p-6 sm:p-8;
    }
    
    /* Headers de cards */
    .card-header {
        @apply px-5 sm:px-6 py-4 border-b border-[#9fb0c3];
    }
    .card-body {
        @apply p-5 sm:p-6;
    }
    .card-footer {
        @apply px-5 sm:px-6 py-4 border-t border-[#9fb0c3] bg-[#d9e2ec];
    }
    
    /* ========================================
       SISTEMA DE TABLAS
       ======================================== */
    
    .table-container {
        @apply overflow-x-auto rounded-2xl border border-[#9fb0c3];
    }
    .table-base {
        @apply w-full text-sm;
    }
    .table-header {
        @apply bg-[#c3cfdb];
    }
    .table-header th {
        @apply px-4 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider;
    }
    .table-body {
        @apply bg-[#e7eef5] divide-y divide-[#9fb0c3];
    }
    .table-body td {
        @apply px-4 py-3 text-sm text-[#1e293b];
    }
    .table-row-hover {
        @apply hover:bg-[#c3cfdb] transition-colors;
    }
    
    /* ========================================
       SISTEMA DE FORMULARIOS
       ======================================== */
    
    .form-label {
        @apply block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-2;
    }
    .form-input {
        @apply w-full px-4 py-3 min-h-[44px] bg-[#e7eef5] border border-[#9fb0c3] rounded-xl text-sm text-[#1e293b] placeholder-[#475569] focus:outline-none focus:ring-2 focus:ring-[#1e293b]/10 focus:border-[#1e293b] transition-all;
    }
    .form-select {
        @apply form-input appearance-none cursor-pointer pr-10;
    }
    
    /* ========================================
       SISTEMA DE BADGES/ESTADOS
       ======================================== */
    
    .badge-base {
        @apply inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg;
    }
    .badge-default {
        @apply badge-base bg-[#c3cfdb] text-[#1e293b];
    }
    .badge-success {
        @apply badge-base bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400;
    }
    .badge-warning {
        @apply badge-base bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400;
    }
    .badge-danger {
        @apply badge-base bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400;
    }
    .badge-info {
        @apply badge-base bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400;
    }
    .badge-purple {
        @apply badge-base bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400;
    }
    
    /* ========================================
       SISTEMA DE BOTONES
       ======================================== */
    
    .btn-base {
        @apply inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2;
    }
    .btn-sm {
        @apply px-3 py-1.5 text-xs;
    }
    .btn-md {
        @apply px-4 py-2.5 text-sm;
    }
    .btn-lg {
        @apply px-6 py-3 text-base;
    }
    .btn-primary {
        @apply btn-base bg-[#0f172a] text-white hover:bg-[#1e293b] focus:ring-[#1e293b]/20;
    }
    .btn-secondary {
        @apply btn-base bg-[#e7eef5] text-[#1e293b] border border-[#9fb0c3] hover:bg-[#dde6ef] focus:ring-[#9fb0c3]/20;
    }
    .btn-danger {
        @apply btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500;
    }
    .btn-success {
        @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500;
    }
    
    /* ========================================
       ICONOS DE ESTADO
       ======================================== */
    
    .icon-box {
        @apply flex items-center justify-center rounded-xl;
    }
    .icon-box-sm {
        @apply w-8 h-8;
    }
    .icon-box-md {
        @apply w-10 h-10;
    }
    .icon-box-lg {
        @apply w-12 h-12;
    }
    
    .icon-box-slate {
        @apply bg-[#c3cfdb] text-[#1e293b];
    }
    .icon-box-blue {
        @apply bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400;
    }
    .icon-box-emerald {
        @apply bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400;
    }
    .icon-box-amber {
        @apply bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400;
    }
    .icon-box-red {
        @apply bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400;
    }
    .icon-box-violet {
        @apply bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400;
    }
    .icon-box-cyan {
        @apply bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400;
    }
    .icon-box-sky {
        @apply bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400;
    }
    .icon-box-rose {
        @apply bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400;
    }
    .icon-box-gradient-red {
        @apply bg-gradient-to-br from-red-500 to-red-700 text-white shadow-lg shadow-red-500/25;
    }
    .icon-box-gradient-blue {
        @apply bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-lg shadow-blue-500/25;
    }
    .icon-box-gradient-emerald {
        @apply bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-lg shadow-emerald-500/25;
    }
    
    /* ========================================
       ESTADOS VACÍOS
       ======================================== */
    
    .empty-state {
        @apply text-center py-12 px-6;
    }
    .empty-state-icon {
        @apply w-16 h-16 mx-auto rounded-2xl bg-[#c3cfdb] flex items-center justify-center mb-4;
    }
    .empty-state-title {
        @apply text-lg font-semibold text-[#1e293b] mb-2;
    }
    .empty-state-text {
        @apply text-sm text-[#475569] max-w-sm mx-auto;
    }
    
    /* ========================================
       MÉTRICAS / KPIs
       ======================================== */
    
    .metric-value {
        @apply text-2xl sm:text-3xl font-bold text-[#1e293b];
    }
    .metric-value-lg {
        @apply text-3xl sm:text-4xl font-bold text-[#1e293b];
    }
    .metric-label {
        @apply text-xs font-semibold text-[#475569] uppercase tracking-wider;
    }
    .metric-trend-up {
        @apply text-xs font-medium text-emerald-600 dark:text-emerald-400;
    }
    .metric-trend-down {
        @apply text-xs font-medium text-red-600 dark:text-red-400;
    }
    
    /* ========================================
       UTILIDADES ADICIONALES
       ======================================== */
    
    .divider {
        @apply border-t border-[#9fb0c3];
    }
    .divider-light {
        @apply border-t border-[#9fb0c3]/50;
    }
    
    .section-gap {
        @apply space-y-6;
    }
    .content-gap {
        @apply space-y-4;
    }
    
    /* Scrollbar unificado */
    .scrollbar-thin::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .scrollbar-thin::-webkit-scrollbar-track {
        background: transparent;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb {
        @apply bg-[#9fb0c3] rounded-full;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        @apply bg-[#7d94ab];
    }
    
    /* ========================================
       PAGINACIÓN TEMA OSCURO
       ======================================== */
    
    .dark-pagination nav {
        background: transparent;
    }
    .dark-pagination nav a,
    .dark-pagination nav span {
        background-color: #1e293b !important;
        color: #94a3b8 !important;
        border-color: #334155 !important;
    }
    .dark-pagination nav a:hover {
        background-color: #334155 !important;
        color: #e2e8f0 !important;
    }
    .dark-pagination nav span.relative.z-10 {
        background-color: #3b82f6 !important;
        color: white !important;
        border-color: #3b82f6 !important;
    }
</style>
