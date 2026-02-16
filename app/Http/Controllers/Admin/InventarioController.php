<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\InventoryWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $bodega = InventoryWarehouse::query()
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$bodega) {
            return redirect()->route('inventario.config.form');
        }

        $items = InventoryItem::query()
            ->where('bodega_id', $bodega->id)
            ->where('activo', true)
            ->orderBy('categoria')
            ->orderBy('titulo')
            ->orderBy('variante')
            ->get();

        return view('admin.inventario.index', [
            'bodega' => $bodega,
            'items' => $items,
        ]);
    }

    public function retiroForm(Request $request)
    {
        if (!$request->session()->get('inventario_retiro_acceso')) {
            return redirect()->route('inventario.dashboard');
        }

        $bodega = InventoryWarehouse::query()
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$bodega) {
            return redirect()->route('inventario.config.form');
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
        ]);
    }

    public function retiroStore(Request $request)
    {
        if (!$request->session()->get('inventario_retiro_acceso')) {
            return redirect()->route('inventario.dashboard');
        }

        $bodega = InventoryWarehouse::query()
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$bodega) {
            return redirect()->route('inventario.config.form');
        }

        $validated = $request->validate([
            'item_id' => ['required', 'integer'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'nota' => ['nullable', 'string', 'max:2000'],
        ]);

        $item = InventoryItem::query()
            ->where('id', (int) $validated['item_id'])
            ->where('bodega_id', $bodega->id)
            ->where('activo', true)
            ->firstOrFail();

        $cantidad = (int) $validated['cantidad'];

        DB::transaction(function () use ($request, $bodega, $item, $cantidad, $validated) {
            $item->refresh();

            if ($cantidad > (int) $item->stock) {
                abort(422, 'La cantidad supera el stock disponible.');
            }

            $item->update([
                'stock' => (int) $item->stock - $cantidad,
            ]);

            InventoryMovement::create([
                'bodega_id' => $bodega->id,
                'item_id' => $item->id,
                'tipo' => 'egreso',
                'cantidad' => $cantidad,
                'nota' => $validated['nota'] ?? null,
                'creado_por' => (int) $request->user()->id,
            ]);
        });

        $request->session()->forget('inventario_retiro_acceso');

        return redirect()->route('inventario.retiro.form')->with('success', 'Retiro registrado correctamente.');
    }

    public function retiroAccess(Request $request)
    {
        $request->session()->put('inventario_retiro_acceso', true);
        return redirect()->route('inventario.retiro.form');
    }

    public function configForm(Request $request)
    {
        $bodega = InventoryWarehouse::query()->orderBy('id')->first();
        if (!$bodega) {
            $bodega = InventoryWarehouse::create([
                'nombre' => 'Bodega Principal',
                'ubicacion' => null,
                'activo' => true,
            ]);
        }
        $items = collect();
        if ($bodega) {
            $items = InventoryItem::query()
                ->where('bodega_id', $bodega->id)
                ->orderBy('categoria')
                ->orderBy('titulo')
                ->orderBy('variante')
                ->get();
        }

        return view('admin.inventario.config', [
            'bodega' => $bodega,
            'items' => $items,
        ]);
    }

    public function bodegaStore(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
        ]);

        $bodega = InventoryWarehouse::query()->orderBy('id')->first();
        if ($bodega) {
            $bodega->update([
                'nombre' => $validated['nombre'],
                'ubicacion' => $validated['ubicacion'] ?? null,
                'activo' => true,
            ]);
        } else {
            InventoryWarehouse::create([
                'nombre' => $validated['nombre'],
                'ubicacion' => $validated['ubicacion'] ?? null,
                'activo' => true,
            ]);
        }

        return redirect()->route('inventario.config.form')->with('success', 'Bodega guardada correctamente.');
    }

    public function itemStore(Request $request)
    {
        $bodega = InventoryWarehouse::query()
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$bodega) {
            return redirect()->route('inventario.config.form');
        }

        $validated = $request->validate([
            'categoria' => ['nullable', 'string', 'max:255'],
            'titulo' => ['required', 'string', 'max:255'],
            'variante' => ['nullable', 'string', 'max:255'],
            'unidad' => ['nullable', 'string', 'max:50'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        InventoryItem::create([
            'bodega_id' => $bodega->id,
            'categoria' => $validated['categoria'] ?? null,
            'titulo' => $validated['titulo'],
            'variante' => $validated['variante'] ?? null,
            'unidad' => $validated['unidad'] ?? null,
            'stock' => (int) $validated['stock'],
            'activo' => true,
        ]);

        return redirect()->route('inventario.config.form')->with('success', 'Ítem agregado correctamente.');
    }

    public function itemDestroy(Request $request, int $itemId)
    {
        $bodega = InventoryWarehouse::query()
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$bodega) {
            return redirect()->route('inventario.config.form');
        }

        $item = InventoryItem::query()
            ->where('id', $itemId)
            ->where('bodega_id', $bodega->id)
            ->firstOrFail();

        $item->delete();

        return redirect()->route('inventario.config.form')->with('success', 'Ítem eliminado correctamente.');
    }
}
