<?php

namespace App\Http\Controllers;

use App\Models\InventoryQrLink;
use Illuminate\Http\Request;

class PlanillasQrController extends Controller
{
    public function show(Request $request, string $token)
    {
        $link = InventoryQrLink::query()
            ->where('token', $token)
            ->where('activo', true)
            ->firstOrFail();

        if ($link->tipo !== 'planillas') {
            abort(404);
        }

        return redirect()->route('admin.planillas.create');
    }
}
