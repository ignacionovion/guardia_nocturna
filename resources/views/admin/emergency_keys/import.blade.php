@extends('layouts.modern')

@section('content')
    <div class="max-w-2xl mx-auto">
        <x-ui.page-header title="Importación Claves Radiales" subtitle="Carga masiva de claves (catálogo)" icon="fas fa-file-import" iconVariant="red">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.emergency-keys.index') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <div class="card-base overflow-hidden">
            <div class="card-header">
                <h2 class="text-title-md">Cargar Archivo</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Seleccione un archivo .CSV o .XLSX con columnas A-B.</p>
            </div>

            <div class="p-8">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb text-yellow-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-bold text-yellow-800 uppercase tracking-wide">Estructura requerida</h3>
                            <div class="mt-2 text-sm text-yellow-700 space-y-1">
                                <p>• A: <strong>clave</strong> (obligatorio)</p>
                                <p>• B: <strong>descripccion</strong> (opcional)</p>
                                <p>• La primera fila se toma como cabecera (se ignora).</p>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="importForm" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <label class="form-label" for="file">Seleccionar Archivo</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer group relative">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-cloud-upload-alt text-4xl text-slate-400 group-hover:text-blue-500 transition-colors mb-3"></i>
                                <div class="flex text-sm text-slate-600 dark:text-slate-400 justify-center">
                                    <label for="file" class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                        <span>Subir un archivo</span>
                                        <input id="file" name="file" type="file" class="sr-only" accept=".csv, .txt, .xlsx" required>
                                    </label>
                                    <p class="pl-1">o arrastrar y soltar</p>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Excel o CSV hasta 10MB</p>
                                <p id="fileName" class="text-sm font-bold text-slate-800 dark:text-white mt-2 hidden"></p>
                            </div>
                        </div>
                    </div>

                    <div id="progressContainer" class="hidden bg-slate-50 dark:bg-slate-800 p-4 rounded-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex justify-between mb-2">
                            <span id="progressText" class="text-xs font-bold text-blue-700 uppercase tracking-wide">Iniciando carga...</span>
                            <span id="progressPercent" class="text-xs font-bold text-blue-700">0%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                            <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300 relative" style="width: 0%">
                                <div class="absolute inset-0 bg-white/20 w-full h-full animate-[shimmer_2s_infinite]"></div>
                            </div>
                        </div>
                    </div>

                    <div id="statusMessage" class="hidden p-4 rounded-lg text-sm border"></div>

                    <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                        <x-ui.button type="submit" id="submitBtn" variant="success" size="md" icon="fas fa-file-import">
                            Procesar Importación
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const display = document.getElementById('fileName');
            if (fileName) {
                display.textContent = fileName;
                display.classList.remove('hidden');
            } else {
                display.classList.add('hidden');
            }
        });

        document.getElementById('importForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const fileInput = document.getElementById('file');
            const submitBtn = document.getElementById('submitBtn');
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const progressPercent = document.getElementById('progressPercent');
            const statusMessage = document.getElementById('statusMessage');

            if (!fileInput.files.length) return;

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            progressContainer.classList.remove('hidden');
            statusMessage.classList.add('hidden');
            statusMessage.className = 'hidden p-4 rounded-lg text-sm border';
            progressBar.style.width = '0%';
            progressPercent.innerText = '0%';
            progressText.innerText = 'SUBIENDO Y ANALIZANDO ARCHIVO...';

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const uploadResponse = await fetch('{{ route("admin.emergency-keys.import.upload") }}', {
                    method: 'POST',
                    body: formData
                });

                if (!uploadResponse.ok) throw new Error((await uploadResponse.json()).error || 'Error en subida');

                const { batchId, totalRows } = await uploadResponse.json();

                let processedCount = 0;
                let offset = 0;
                const limit = 50;
                let errors = [];

                progressText.innerText = 'PROCESANDO REGISTROS...';

                while (true) {
                    const processResponse = await fetch('{{ route("admin.emergency-keys.import.process") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ batchId, offset, limit })
                    });

                    if (!processResponse.ok) throw new Error('Error procesando lote');

                    const result = await processResponse.json();

                    processedCount += result.processed;
                    if (result.errors) errors = [...errors, ...result.errors];

                    const percent = Math.min(100, Math.round(((offset + limit) / totalRows) * 100));
                    progressBar.style.width = percent + '%';
                    progressPercent.innerText = percent + '%';
                    progressText.innerText = `PROCESANDO: ${Math.min(processedCount, totalRows)} / ${totalRows} REGISTROS`;

                    if (result.finished) break;
                    offset += limit;
                }

                progressBar.style.width = '100%';
                progressPercent.innerText = '100%';
                progressText.innerText = 'COMPLETADO';

                statusMessage.className = 'mb-6 p-4 rounded-lg bg-green-50 text-green-800 border border-green-200 block';
                let msg = `<div class="flex items-center mb-2"><i class="fas fa-check-circle text-xl mr-2"></i><strong>¡Proceso completado con éxito!</strong></div>Se han importado ${processedCount} claves.`;

                if (errors.length > 0) {
                    statusMessage.className = 'mb-6 p-4 rounded-lg bg-yellow-50 text-yellow-800 border border-yellow-200 block';
                    msg += `<div class="mt-4 pt-4 border-t border-yellow-200"><strong class="uppercase text-xs tracking-wide">Advertencias (${errors.length}):</strong><ul class="list-disc pl-5 text-xs mt-2 max-h-32 overflow-y-auto space-y-1">`;
                    errors.forEach(err => msg += `<li>${err}</li>`);
                    msg += '</ul></div>';
                }

                statusMessage.innerHTML = msg;
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } catch (error) {
                console.error(error);
                progressContainer.classList.add('hidden');
                statusMessage.className = 'mb-6 p-4 rounded-lg bg-red-50 text-red-800 border border-red-200 block';
                statusMessage.innerHTML = `<div class="flex items-center"><i class="fas fa-times-circle text-xl mr-2"></i><strong>Error:</strong> ${error.message}</div>`;
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });
    </script>
@endsection
