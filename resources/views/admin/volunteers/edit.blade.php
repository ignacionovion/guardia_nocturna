@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto py-8">
        <!-- Header con navegación -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Editar Voluntario</h1>
                <p class="text-gray-500 mt-1 text-sm">Actualizando información de: <span class="font-semibold text-blue-600">{{ $volunteer->name }} {{ $volunteer->last_name_paternal }}</span></p>
            </div>
            <a href="{{ route('admin.volunteers.index') }}" class="inline-flex items-center text-gray-600 hover:text-blue-600 font-medium transition-colors bg-white px-4 py-2 rounded-lg border border-gray-200 hover:border-blue-300 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Volver al listado
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200">
            <!-- Barra superior decorativa -->
            <div class="h-2 bg-red-700"></div>

            <form action="{{ route('admin.volunteers.update', $volunteer->id) }}" method="POST" class="p-8">
                @csrf
                @method('PUT')

                <!-- Sección 1: Identificación Personal -->
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-3">
                        <div class="bg-blue-100 p-2 rounded-lg text-blue-700">
                            <i class="fas fa-id-card text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Identificación Personal</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">RUT</label>
                            <input type="text" name="rut" value="{{ old('rut', $volunteer->rut) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all text-slate-700 font-medium">
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Nombres <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $volunteer->name) }}" required 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Apellido Paterno</label>
                            <input type="text" name="last_name_paternal" value="{{ old('last_name_paternal', $volunteer->last_name_paternal) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Apellido Materno</label>
                            <input type="text" name="last_name_maternal" value="{{ old('last_name_maternal', $volunteer->last_name_maternal) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Fecha Nacimiento</label>
                            <input type="date" name="birthdate" value="{{ old('birthdate', optional($volunteer->birthdate)->format('Y-m-d')) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all text-slate-600">
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Profesión / Oficio</label>
                            <div class="relative">
                                <i class="fas fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="profession" value="{{ old('profession', $volunteer->profession) }}" 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Datos Institucionales -->
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-3">
                        <div class="bg-red-100 p-2 rounded-lg text-red-700">
                            <i class="fas fa-helmet-safety text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Datos Institucionales</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Compañía</label>
                            <div class="relative">
                                <i class="fas fa-building absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="company" value="{{ old('company', $volunteer->company) }}" 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all bg-slate-50">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">N° Registro General</label>
                            <input type="text" name="registration_number" value="{{ old('registration_number', $volunteer->registration_number) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all font-mono">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">N° Registro Cía</label>
                            <input type="text" name="company_registration_number" value="{{ old('company_registration_number', $volunteer->company_registration_number) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all font-mono">
                        </div>

                        <!-- Nueva Lista de Cargos -->
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Cargo Oficial</label>
                            <div class="relative">
                                <i class="fas fa-star absolute left-3 top-1/2 -translate-y-1/2 text-yellow-500"></i>
                                <select name="position_text" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all appearance-none bg-white">
                                    <option value="">Seleccione Cargo...</option>
                                    @php
                                        $cargos = [
                                            'Oficiales Generales' => ['Director', 'Secretario', 'Tesorero', 'Protesorero', 'Prosecretario'],
                                            'Oficiales de Mando' => ['Capitan', 'Teniente 1', 'Teniente 2', 'Teniente 3', 'Teniente 4'],
                                            'Ayudantía' => ['Ayudante 1', 'Ayudante 2', 'Ayudante 3'],
                                            'Voluntarios' => ['Honorario', 'Bombero', 'Canje']
                                        ];
                                    @endphp
                                    
                                    @foreach($cargos as $grupo => $lista)
                                        <optgroup label="{{ $grupo }}">
                                            @foreach($lista as $cargo)
                                                <option value="{{ $cargo }}" {{ old('position_text', $volunteer->position_text) == $cargo ? 'selected' : '' }}>{{ $cargo }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Código Radial</label>
                            <input type="text" name="call_code" value="{{ old('call_code', $volunteer->call_code) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all font-bold text-slate-700 uppercase">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">N° Portátil</label>
                            <div class="relative">
                                <i class="fas fa-walkie-talkie absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="portable_number" value="{{ old('portable_number', $volunteer->portable_number) }}" 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Fecha Ingreso</label>
                            <input type="date" name="admission_date" value="{{ old('admission_date', optional($volunteer->admission_date)->format('Y-m-d')) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all text-slate-600">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Guardia Asignada</label>
                            <select name="guardia_id" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all bg-white">
                                <option value="">Sin Asignar</option>
                                @foreach($guardias as $guardia)
                                    <option value="{{ $guardia->id }}" {{ old('guardia_id', $volunteer->guardia_id) == $guardia->id ? 'selected' : '' }}>
                                        {{ $guardia->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Permisos y Roles Técnicos -->
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-3">
                        <div class="bg-yellow-100 p-2 rounded-lg text-yellow-700">
                            <i class="fas fa-user-shield text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Cualificaciones y Permisos</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
                            <h4 class="font-semibold text-slate-700 mb-4 flex items-center">
                                <i class="fas fa-tools mr-2 text-slate-400"></i> Especialidades Técnicas
                            </h4>
                            <div class="space-y-3">
                                <label class="flex items-center p-3 bg-white border border-slate-200 rounded-lg hover:border-blue-400 cursor-pointer transition-all shadow-sm group">
                                    <input type="checkbox" name="is_driver" value="1" {{ $volunteer->is_driver ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500 h-5 w-5 border-slate-300">
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-slate-800 group-hover:text-blue-700">Conductor</span>
                                        <span class="block text-xs text-slate-500">Autorizado para conducir máquinas</span>
                                    </div>
                                    <i class="fas fa-truck ml-auto text-blue-500"></i>
                                </label>
                                <label class="flex items-center p-3 bg-white border border-slate-200 rounded-lg hover:border-orange-400 cursor-pointer transition-all shadow-sm group">
                                    <input type="checkbox" name="is_rescue_operator" value="1" {{ $volunteer->is_rescue_operator ? 'checked' : '' }} class="rounded text-orange-600 focus:ring-orange-500 h-5 w-5 border-slate-300">
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-slate-800 group-hover:text-orange-700">Operador Rescate</span>
                                        <span class="block text-xs text-slate-500">Especialista en rescate vehicular</span>
                                    </div>
                                    <i class="fas fa-car-crash ml-auto text-orange-500"></i>
                                </label>
                                <label class="flex items-center p-3 bg-white border border-slate-200 rounded-lg hover:border-red-400 cursor-pointer transition-all shadow-sm group">
                                    <input type="checkbox" name="is_trauma_assistant" value="1" {{ $volunteer->is_trauma_assistant ? 'checked' : '' }} class="rounded text-red-600 focus:ring-red-500 h-5 w-5 border-slate-300">
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-slate-800 group-hover:text-red-700">Asistente Trauma</span>
                                        <span class="block text-xs text-slate-500">Capacitación prehospitalaria</span>
                                    </div>
                                    <i class="fas fa-medkit ml-auto text-red-500"></i>
                                </label>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
                            <h4 class="font-semibold text-slate-700 mb-4 flex items-center">
                                <i class="fas fa-laptop-code mr-2 text-slate-400"></i> Roles de Sistema
                            </h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-bold text-slate-700 mb-2 block uppercase tracking-wide">Nivel de Acceso</label>
                                    <select name="role" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all bg-white">
                                        <option value="bombero" {{ old('role', $volunteer->role) == 'bombero' ? 'selected' : '' }}>Usuario Estándar (Bombero)</option>
                                        <option value="jefe_guardia" {{ old('role', $volunteer->role) == 'jefe_guardia' ? 'selected' : '' }}>Jefe de Guardia</option>
                                        <option value="capitania" {{ old('role', $volunteer->role) == 'capitania' ? 'selected' : '' }}>Oficial de Mando (Capitanía)</option>
                                        <option value="super_admin" {{ old('role', $volunteer->role) == 'super_admin' ? 'selected' : '' }}>Administrador del Sistema</option>
                                    </select>
                                    <p class="text-xs text-slate-500 mt-2">Determina qué secciones puede ver y editar en la plataforma.</p>
                                </div>
                                
                                <label class="flex items-center p-3 bg-white border border-slate-200 rounded-lg hover:border-yellow-400 cursor-pointer transition-all shadow-sm mt-4 group">
                                    <input type="checkbox" name="is_shift_leader" value="1" {{ $volunteer->is_shift_leader ? 'checked' : '' }} class="rounded text-yellow-600 focus:ring-yellow-500 h-5 w-5 border-slate-300">
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-slate-800 group-hover:text-yellow-700">Oficial de Guardia</span>
                                        <span class="block text-xs text-slate-500">Habilitado para estar a cargo de la guardia</span>
                                    </div>
                                    <i class="fas fa-star ml-auto text-yellow-500"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 4: Contacto -->
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-3">
                        <div class="bg-green-100 p-2 rounded-lg text-green-700">
                            <i class="fas fa-map-marked-alt text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Información de Contacto</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Email <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="email" name="email" value="{{ old('email', $volunteer->email) }}" required 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Teléfono</label>
                            <div class="relative">
                                <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="phone" value="{{ old('phone', $volunteer->phone) }}" 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Comuna</label>
                            <input type="text" name="address_commune" value="{{ old('address_commune', $volunteer->address_commune) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Calle / Dirección</label>
                            <input type="text" name="address_street" value="{{ old('address_street', $volunteer->address_street) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Número</label>
                            <input type="text" name="address_number" value="{{ old('address_number', $volunteer->address_number) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Footer Acciones -->
                <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-slate-100">
                    <a href="{{ route('admin.volunteers.index') }}" class="px-6 py-2.5 rounded-lg border border-slate-300 text-slate-600 font-medium hover:bg-slate-50 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 px-8 rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center uppercase tracking-wide text-sm">
                        <i class="fas fa-save mr-2"></i> Actualizar Voluntario
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
