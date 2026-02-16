<?php

namespace App\Http\Controllers;

use App\Models\Bombero;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\InventoryQrLink;
use App\Models\InventoryWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioQrController extends Controller
{
    public function show(Request $request, string $token)
    {
        if (!$request->session()->get('inventario_qr_bombero_id')) {
            return redirect()->route('inventario.qr.identificar.form', ['token' => $token]);
        }

        $link = InventoryQrLink::query()
            ->where('token', $token)
            ->where('activo', true)
            ->firstOrFail();

        if ($link->tipo !== 'inventario') {
            abort(404);
        }

        $bodega = null;
        if ($link->bodega_id) {
            $bodega = InventoryWarehouse::query()->where('id', $link->bodega_id)->first();
        }

        if (!$bodega) {
            $bodega = InventoryWarehouse::query()
                ->where('activo', true)
                ->orderBy('id')
                ->firstOrFail();
        }

        $items = InventoryItem::query()
            ->where('bodega_id', $bodega->id)
            ->where('activo', true)
            ->orderBy('categoria')
            ->orderBy('titulo')
            ->orderBy('variante')
            ->get();

        $bombero = null;
        $bomberoId = $request->session()->get('inventario_qr_bombero_id');
        if ($bomberoId) {
            $bombero = Bombero::query()->where('id', (int) $bomberoId)->first();
        }

        $movimientos = InventoryMovement::query()
            ->with(['item', 'firefighter'])
            ->where('bodega_id', $bodega->id)
            ->where('tipo', 'egreso')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('admin.inventario.retiro', [
            'bodega' => $bodega,
            'items' => $items,
            'token' => $token,
            'bombero' => $bombero,
            'movimientos' => $movimientos,
        ]);
    }

    public function identificarForm(Request $request, string $token)
    {
        return view('admin.inventario.identificar', [
            'token' => $token,
        ]);
    }

    public function identificarStore(Request $request, string $token)
    {
        $validated = $request->validate([
            'rut' => ['required', 'string', 'max:20', 'regex:/^\d{7,8}-[0-9kK]$/'],
        ], [
            'rut.regex' => 'Formato inválido. Debe ser como 18485962-9.',
        ]);

        $rut = mb_strtolower(trim((string) $validated['rut']));

        $bombero = Bombero::query()
            ->whereRaw('lower(rut) = ?', [$rut])
            ->first();

        if (!$bombero) {
            return back()->withInput()->withErrors([
                'rut' => 'Bombero no existe en nuestra base de datos.',
            ]);
        }

        $request->session()->put('inventario_qr_bombero_id', (int) $bombero->id);

        return redirect()->route('inventario.qr.show', ['token' => $token]);
    }

    public function store(Request $request, string $token)
    {
        $bomberoId = $request->session()->get('inventario_qr_bombero_id');
        if (!$bomberoId) {
            return redirect()->route('inventario.qr.identificar.form', ['token' => $token]);
        }

        $link = InventoryQrLink::query()
            ->where('token', $token)
            ->where('activo', true)
            ->firstOrFail();

        if ($link->tipo !== 'inventario') {
            abort(404);
        }

        $bodega = null;
        if ($link->bodega_id) {
            $bodega = InventoryWarehouse::query()->where('id', $link->bodega_id)->first();
        }

        if (!$bodega) {
            $bodega = InventoryWarehouse::query()
                ->where('activo', true)
                ->orderBy('id')
                ->firstOrFail();
        }

        $validated = $request->validate([
            'item_id' => ['required', 'integer'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'nota' => ['nullable', 'string', 'max:1000'],
        ]);

        $item = InventoryItem::query()
            ->where('id', (int) $validated['item_id'])
            ->where('bodega_id', $bodega->id)
            ->where('activo', true)
            ->firstOrFail();

        $cantidad = (int) $validated['cantidad'];

        try {
            DB::transaction(function () use ($item, $bodega, $cantidad, $validated, $bomberoId) {
                $item->refresh();

                if ((int) $item->stock < $cantidad) {
                    throw new \RuntimeException('Stock insuficiente.');
                }

                $item->stock = (int) $item->stock - $cantidad;
                $item->save();

                InventoryMovement::create([
                    'bodega_id' => $bodega->id,
                    'item_id' => $item->id,
                    'tipo' => 'egreso',
                    'cantidad' => $cantidad,
                    'nota' => $validated['nota'] ?? null,
                    'creado_por' => null,
                    'bombero_id' => (int) $bomberoId,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['cantidad' => $e->getMessage()]);
        }

        $request->session()->forget('inventario_qr_bombero_id');

        return back()->with('success', 'Retiro registrado correctamente.');
    }
}
