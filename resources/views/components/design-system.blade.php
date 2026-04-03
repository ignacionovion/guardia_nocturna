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
        @apply text-2xl font-black text-slate-900 tracking-tight;
    }
    .text-title-lg {
        @apply text-xl font-bold text-slate-900;
    }
    .text-title-md {
        @apply text-lg font-bold text-slate-900;
    }
    .text-title-sm {
        @apply text-base font-bold text-slate-900;
    }
    
    /* Texto de contenido */
    .text-body {
        @apply text-sm text-slate-500;
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
        @apply text-emerald-600;
    }
    .text-warning {
        @apply text-amber-600;
    }
    .text-danger {
        @apply text-red-600;
    }
    .text-info {
        @apply text-blue-600;
    }
    
    /* ========================================
       SISTEMA DE CARDS
       ======================================== */
    
    .card-base {
        @apply bg-white rounded-2xl border border-slate-200 shadow-sm;
    }
    .card-elevated {
        @apply card-base shadow-md;
    }
    .card-interactive {
        @apply card-base transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5;
    }
    
    /* Padding estándar para cards */
    .card-padding {
        @apply p-6 sm:p-8;
    }
    .card-padding-sm {
        @apply p-5;
    }
    .card-padding-lg {
        @apply p-8 sm:p-10;
    }
    
    /* Headers de cards */
    .card-header {
        @apply px-6 py-4 border-b border-slate-200;
    }
    .card-body {
        @apply p-6;
    }
    .card-footer {
        @apply px-6 py-4 border-t border-slate-200 bg-white;
    }
    
    /* ========================================
       SISTEMA DE TABLAS
       ======================================== */
    
    .table-container {
        @apply overflow-x-auto rounded-2xl border border-slate-200 shadow-sm;
    }
    .table-base {
        @apply w-full text-sm;
    }
    .table-header {
        @apply bg-white;
    }
    .table-header th {
        @apply px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider;
    }
    .table-body {
        @apply bg-white divide-y divide-slate-100;
    }
    .table-body td {
        @apply px-4 py-3 text-sm text-slate-900;
    }
    .table-row-hover {
        @apply hover:bg-[#f8fafc] transition-colors;
    }
    
    /* ========================================
       SISTEMA DE FORMULARIOS
       ======================================== */
    
    .form-label {
        @apply block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2;
    }
    .form-input {
        @apply w-full px-4 py-3 min-h-[44px] bg-white border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition-all;
    }
    .form-select {
        @apply form-input appearance-none cursor-pointer pr-10;
    }
    
    /* ========================================
       SISTEMA DE BADGES/ESTADOS
       ======================================== */
    
    .badge-base {
        @apply inline-flex items-center gap-1.5 px-2 py-1 text-[10px] font-bold rounded-lg uppercase;
    }
    .badge-default {
        @apply badge-base bg-slate-100 text-slate-600 border border-slate-200;
    }
    .badge-success {
        @apply badge-base bg-emerald-50 text-emerald-700 border border-emerald-200;
    }
    .badge-warning {
        @apply badge-base bg-amber-50 text-amber-700 border border-amber-200;
    }
    .badge-danger {
        @apply badge-base bg-red-50 text-red-700 border border-red-200;
    }
    .badge-info {
        @apply badge-base bg-blue-50 text-blue-700 border border-blue-200;
    }
    .badge-purple {
        @apply badge-base bg-violet-50 text-violet-700 border border-violet-200;
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
        @apply px-6 py-3 text-sm;
    }
    .btn-primary {
        @apply btn-base bg-slate-900 text-white hover:bg-slate-800 focus:ring-slate-900/20 shadow-sm;
    }
    .btn-secondary {
        @apply btn-base bg-white text-slate-700 border border-slate-200 hover:bg-[#f8fafc] hover:border-slate-300 focus:ring-slate-900/10 shadow-sm;
    }
    .btn-danger {
        @apply btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm;
    }
    .btn-success {
        @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 shadow-sm;
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
        @apply bg-[#f9fbfd] text-[#0f172a];
    }
    .icon-box-blue {
        @apply bg-blue-100 text-blue-600;
    }
    .icon-box-emerald {
        @apply bg-emerald-100 text-emerald-600;
    }
    .icon-box-amber {
        @apply bg-amber-100 text-amber-600;
    }
    .icon-box-red {
        @apply bg-red-100 text-red-600;
    }
    .icon-box-violet {
        @apply bg-violet-100 text-violet-600;
    }
    .icon-box-cyan {
        @apply bg-cyan-100 text-cyan-600;
    }
    .icon-box-sky {
        @apply bg-sky-100 text-sky-600;
    }
    .icon-box-rose {
        @apply bg-rose-100 text-rose-600;
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
        @apply w-16 h-16 mx-auto rounded-2xl bg-[#f9fbfd] flex items-center justify-center mb-4;
    }
    .empty-state-title {
        @apply text-lg font-semibold text-[#0f172a] mb-2;
    }
    .empty-state-text {
        @apply text-sm text-[#475569] max-w-sm mx-auto;
    }
    
    /* ========================================
       MÉTRICAS / KPIs
       ======================================== */
    
    .kpi-card {
        @apply bg-white rounded-2xl border border-slate-200 shadow-sm p-5 transition-all duration-200;
    }
    .kpi-icon-wrapper {
        @apply w-11 h-11 rounded-xl flex items-center justify-center;
    }
    .kpi-value {
        @apply text-xl font-black text-slate-900;
    }
    .kpi-label {
        @apply text-xs font-bold text-slate-500 uppercase tracking-wider;
    }
    
    .metric-value {
        @apply text-2xl sm:text-3xl font-bold text-[#0f172a];
    }
    .metric-value-lg {
        @apply text-3xl sm:text-4xl font-bold text-[#0f172a];
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
        @apply border-t border-[#e5e7eb];
    }
    .divider-light {
        @apply border-t border-[#e5e7eb]/50;
    }
    
    .section-gap {
        @apply space-y-8;
    }
    .content-gap {
        @apply space-y-6;
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
        @apply bg-[#e5e7eb] rounded-full;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        @apply bg-[#cbd5e1];
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
