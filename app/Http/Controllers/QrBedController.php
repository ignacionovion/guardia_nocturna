<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QrBedController extends Controller
{
    public function show($token)
    {
        // Diagnóstico temporal para identificar causa del 404
        $diagnostico = [
            'timestamp' => now()->toDateTimeString(),
            'token_recibido' => $token,
            'tenant_initialized' => tenant() !== null,
            'tenant_id' => tenant() ? tenant()->id : null,
            'db_connection' => config('database.default'),
            'token_exists' => Bed::where('qr_token', $token)->exists(),
            'total_beds' => Bed::count(),
            'beds_with_token' => Bed::whereNotNull('qr_token')->count(),
        ];

        Log::channel('single')->info('QR Bed Show - Diagnóstico', $diagnostico);

        // Si el token no existe, loguear las primeras 5 camas para comparar
        if (!$diagnostico['token_exists']) {
            $sampleBeds = Bed::select('id', 'name', 'qr_token')
                ->limit(5)
                ->get()
                ->toArray();
            Log::channel('single')->warning('QR Token no encontrado - Muestra de camas', [
                'token_buscado' => $token,
                'muestra_camas' => $sampleBeds,
            ]);
        }

        $bed = Bed::where('qr_token', $token)->firstOrFail();

        return view('qr.bed', compact('bed'));
    }
}
