<?php

namespace App\Http\Controllers;

use App\Models\InventoryQrLink;
use Illuminate\Http\Request;

class InventarioQrController extends Controller
{
    public function show(Request $request, string $token)
    {
        $link = InventoryQrLink::query()
            ->where('token', $token)
            ->where('activo', true)
            ->firstOrFail();

        if ($link->tipo !== 'inventario') {
            abort(404);
        }

        $request->session()->put('inventario_retiro_acceso', true);

        return redirect()->route('inventario.retiro.form');
    }
}
