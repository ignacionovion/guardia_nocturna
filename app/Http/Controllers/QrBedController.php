<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use Illuminate\Http\Request;

class QrBedController extends Controller
{
    /**
     * Redirige el QR público al flujo operativo unificado
     * /qr/{token} → /camas/scan/{bedId}
     */
    public function show(Request $request, string $token)
    {
        $token = $request->route('token') ?? $token;

        $bed = Bed::where('qr_token', $token)->firstOrFail();

        // Redirigir al flujo operativo correcto
        return redirect()->route('camas.scan.form', ['bedId' => $bed->id]);
    }
}
