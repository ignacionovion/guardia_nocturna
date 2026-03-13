@extends('layouts.modern')

@section('content')
<div class="w-full py-4">
    <x-ui.page-header title="Importación" subtitle="Carga masiva de ítems para {{ $bodega->nombre }}." icon="fas fa-file-import" iconVariant="amber">
        <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('inventario.config.form') }}">Volver</x-ui.button>
    </x-ui.page-header>

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <div class="text-sm font-bold text-slate-900 dark:text-white">Formato</div>
            <div class="text-sm text-slate-700 dark:text-slate-300 mt-2">
                Columnas esperadas:
                <div class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Categoría | Título | Variante | Unidad | Stock
                </div>
            </div>
        </div>

        <div class="p-6">
            <form id="importForm" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="form-label">Archivo</label>
                    <input type="file" name="file" accept=".csv,.txt,.xlsx" class="form-input" required />
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-2">Formatos permitidos: .CSV, .XLSX</div>
                </div>

                <div class="pt-2">
                    <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-file-import">
                        Iniciar importación
                    </x-ui.button>
                </div>
            </form>

            <div id="progressBox" class="hidden mt-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-4">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Progreso</div>
                <div class="mt-2 text-sm text-slate-700 dark:text-slate-300" id="progressText">Preparando...</div>
                <div class="mt-3 h-3 rounded-full bg-slate-200 overflow-hidden">
                    <div id="progressBar" class="h-full bg-emerald-600" style="width: 0%"></div>
                </div>
                <div id="errorsBox" class="hidden mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-rose-900 text-sm"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('importForm');
    const progressBox = document.getElementById('progressBox');
    const progressText = document.getElementById('progressText');
    const progressBar = document.getElementById('progressBar');
    const errorsBox = document.getElementById('errorsBox');

    if (!form) return;

    const uploadUrl = @json(route('inventario.import.upload'));
    const processUrl = @json(route('inventario.import.process'));

    const setProgress = (current, total) => {
        const pct = total > 0 ? Math.round((current / total) * 100) : 0;
        progressBar.style.width = pct + '%';
        progressText.textContent = `Procesando ${current} / ${total} (${pct}%)`;
    };

    const showErrors = (errs) => {
        if (!errs || errs.length === 0) {
            errorsBox.classList.add('hidden');
            errorsBox.textContent = '';
            return;
        }
        errorsBox.classList.remove('hidden');
        errorsBox.textContent = errs.slice(0, 8).join('\n');
    };

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        progressBox.classList.remove('hidden');
        showErrors([]);
        setProgress(0, 1);
        progressText.textContent = 'Subiendo archivo...';

        const formData = new FormData(form);

        const uploadResp = await fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData,
        });

        const uploadJson = await uploadResp.json();
        if (!uploadResp.ok) {
            showErrors([uploadJson.error || 'Error subiendo archivo']);
            progressText.textContent = 'Error';
            return;
        }

        const batchId = uploadJson.batchId;
        const totalRows = uploadJson.totalRows || 0;
        let offset = 0;
        const limit = 50;
        let processed = 0;
        let allErrors = [];

        while (true) {
            const resp = await fetch(processUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ batchId, offset, limit }),
            });

            const json = await resp.json();
            if (!resp.ok) {
                showErrors([json.error || 'Error procesando lote']);
                progressText.textContent = 'Error';
                return;
            }

            processed += (json.procesados || 0);
            offset += limit;
            allErrors = allErrors.concat(json.errores || []);

            setProgress(Math.min(processed, totalRows), totalRows);
            showErrors(allErrors);

            if (json.finished) {
                progressText.textContent = `Importación finalizada. Procesados: ${processed}.`;
                break;
            }
        }
    });
})();
</script>
@endsection
