<?php

namespace App\Http\Controllers;

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

        return view('admin.inventario.retiro', [
            'bodega' => $bodega,
            'items' => $items,
            'token' => $token,
        ]);
    }

    public function store(Request $request, string $token)
    {
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
            DB::transaction(function () use ($item, $bodega, $cantidad, $validated) {
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
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['cantidad' => $e->getMessage()]);
        }

        return back()->with('success', 'Retiro registrado correctamente.');
    }
}
