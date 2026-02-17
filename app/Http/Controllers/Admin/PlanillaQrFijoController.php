<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryQrLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanillaQrFijoController extends Controller
{
    public function show(Request $request)
    {
        $link = InventoryQrLink::query()
            ->where('tipo', 'planillas')
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$link) {
            $link = InventoryQrLink::create([
                'token' => Str::random(40),
                'tipo' => 'planillas',
                'bodega_id' => null,
                'activo' => true,
            ]);
        }

        $url = route('planillas.qr.show', ['token' => $link->token]);

        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(280)
            ->margin(1)
            ->generate($url);

        return view('admin.planillas.qr_fijo', [
            'link' => $link,
            'url' => $url,
            'qrSvg' => $qrSvg,
        ]);
    }

    public function print(Request $request)
    {
        $link = InventoryQrLink::query()
            ->where('tipo', 'planillas')
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$link) {
            $link = InventoryQrLink::create([
                'token' => Str::random(40),
                'tipo' => 'planillas',
                'bodega_id' => null,
                'activo' => true,
            ]);
        }

        $url = route('planillas.qr.show', ['token' => $link->token]);

        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(520)
            ->margin(1)
            ->generate($url);

        return view('admin.planillas.qr_fijo_print', [
            'link' => $link,
            'url' => $url,
            'qrSvg' => $qrSvg,
        ]);
    }

    public function regenerar(Request $request)
    {
        $link = InventoryQrLink::query()
            ->where('tipo', 'planillas')
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$link) {
            InventoryQrLink::create([
                'token' => Str::random(40),
                'tipo' => 'planillas',
                'bodega_id' => null,
                'activo' => true,
            ]);
        } else {
            $link->update([
                'token' => Str::random(40),
            ]);
        }

        return redirect()->route('admin.planillas.qr_fijo')->with('success', 'QR regenerado correctamente.');
    }
}
