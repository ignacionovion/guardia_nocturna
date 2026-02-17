@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                    <i class="fas fa-file-import mr-3 text-red-700"></i> Importación Masiva
                </h1>
                <p class="text-slate-500 mt-1 font-medium">Carga de voluntarios desde planilla externa</p>
            </div>
            <a href="{{ route('admin.volunteers.index') }}" class="inline-flex items-center text-slate-600 hover:text-blue-600 font-medium transition-colors bg-white px-4 py-2 rounded-lg border border-slate-200 hover:border-blue-300 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Volver al listado
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200">
            <div class="bg-slate-50 px-8 py-6 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-700">Cargar Archivo de Datos</h2>
                <p class="text-sm text-slate-500 mt-1">Seleccione un archivo .CSV o .XLSX con la nómina de voluntarios.</p>
            </div>

            <div class="p-8">
                <!-- Advertencia -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb text-yellow-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-bold text-yellow-800 uppercase tracking-wide">Instrucciones Importantes</h3>
                            <div class="mt-2 text-sm text-yellow-700 space-y-1">
                                <p>• El archivo debe seguir estrictamente la estructura de columnas (A-N).</p>
                                <p>• Formatos permitidos: <strong>.CSV</strong>, <strong>.XLSX</strong>.</p>
                                <div class="pt-2 border-t border-yellow-200">
                                    <p class="font-bold">Columnas (en español):</p>
                                    <p>A: nombres</p>
                                    <p>B: apellido_paterno</p>
                                    <p>C: apellido_materno</p>
                                    <p>D: rut</p>
                                    <p>E: cargo (sugerido: director, secretario, tesorero, capitan, teniente 1, teniente2, teniente 3, teniente 4, ayudante, ayudante1, ayudante 2, ayudante 3, pro secretario, pro tesorero, Administrativo)</p>
                                    <p>F: portatil (texto. Ej: 364, 37-D)</p>
                                    <p>G: fecha_cumpleanos</p>
                                    <p>H: guardia_id</p>
                                    <p>I: fecha_ingreso</p>
                                    <p>J: conductor</p>
                                    <p>K: operador_rescate</p>
                                    <p>L: asistente_trauma</p>
                                    <p>M: email</p>
                                    <p>N: NUMERO_REGISTRO</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="importForm" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label class="block text-slate-700 text-sm font-bold mb-2 uppercase tracking-wide" for="file">
                            Seleccionar Archivo
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-lg hover:bg-slate-50 transition-colors cursor-pointer group relative">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-cloud-upload-alt text-4xl text-slate-400 group-hover:text-blue-500 transition-colors mb-3"></i>
                                <div class="flex text-sm text-slate-600 justify-center">
                                    <label for="file" class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                        <span>Subir un archivo</span>
                                        <input id="file" name="file" type="file" class="sr-only" accept=".csv, .txt, .xlsx" required>
                                    </label>
                                    <p class="pl-1">o arrastrar y soltar</p>
                                </div>
                                <p class="text-xs text-slate-500">Excel o CSV hasta 10MB</p>
                                <p id="fileName" class="text-sm font-bold text-slate-800 mt-2 hidden"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Barra de Progreso -->
                    <div id="progressContainer" class="hidden bg-slate-50 p-4 rounded-lg border border-slate-200">
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

                    <!-- Mensajes de Estado -->
                    <div id="statusMessage" class="hidden p-4 rounded-lg text-sm border"></div>

                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <button type="submit" id="submitBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center uppercase text-sm tracking-wide">
                            <i class="fas fa-file-import mr-2"></i> Procesar Importación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Mostrar nombre de archivo seleccionado
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

            // Reset UI
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            progressContainer.classList.remove('hidden');
            statusMessage.classList.add('hidden');
            statusMessage.className = 'hidden p-4 rounded-lg text-sm border'; // Reset clases
            progressBar.style.width = '0%';
            progressPercent.innerText = '0%';
            progressText.innerText = 'SUBIENDO Y ANALIZANDO ARCHIVO...';

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                // Paso 1: Subir archivo
                const uploadResponse = await fetch('{{ route("admin.volunteers.import.upload") }}', {
                    method: 'POST',
                    body: formData
                });

                if (!uploadResponse.ok) throw new Error((await uploadResponse.json()).error || 'Error en subida');

                const { batchId, totalRows } = await uploadResponse.json();
                
                // Paso 2: Procesar por lotes
                let processedCount = 0;
                let offset = 0;
                const limit = 50; 
                let errors = [];

                progressText.innerText = 'PROCESANDO REGISTROS...';

                while (true) {
                    const processResponse = await fetch('{{ route("admin.volunteers.import.process") }}', {
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
                    if(result.errors) errors = [...errors, ...result.errors];

                    // Actualizar barra
                    const percent = Math.min(100, Math.round(((offset + limit) / totalRows) * 100));
                    progressBar.style.width = percent + '%';
                    progressPercent.innerText = percent + '%';
                    progressText.innerText = `PROCESANDO: ${Math.min(processedCount, totalRows)} / ${totalRows} REGISTROS`;

                    if (result.finished) break;
                    offset += limit;
                }

                // Finalizado
                progressBar.style.width = '100%';
                progressPercent.innerText = '100%';
                progressText.innerText = 'COMPLETADO';
                
                statusMessage.className = 'mb-6 p-4 rounded-lg bg-green-50 text-green-800 border border-green-200 block';
                let msg = `<div class="flex items-center mb-2"><i class="fas fa-check-circle text-xl mr-2"></i><strong>¡Proceso completado con éxito!</strong></div>Se han importado ${processedCount} voluntarios al sistema.`;
                
                if (errors.length > 0) {
                    statusMessage.className = 'mb-6 p-4 rounded-lg bg-yellow-50 text-yellow-800 border border-yellow-200 block';
                    msg += `<div class="mt-4 pt-4 border-t border-yellow-200"><strong class="uppercase text-xs tracking-wide">Advertencias (${errors.length}):</strong><ul class="list-disc pl-5 text-xs mt-2 max-h-32 overflow-y-auto space-y-1">`;
                    errors.forEach(err => msg += `<li>${err}</li>`);
                    msg += '</ul></div>';
                }

                statusMessage.innerHTML = msg;
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                
                // Limpiar input file opcionalmente
                // fileInput.value = ''; 

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
