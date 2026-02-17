<?php

namespace App\Http\Controllers;

use App\Models\Bombero;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\InventoryQrLink;
use App\Models\InventoryWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class InventarioQrController extends Controller
{
    public function show(Request $request, string $token)
    {
        if (!$request->session()->get('inventario_qr_bombero_id')) {
            return redirect()->route('inventario.qr.identificar.form', ['token' => $token]);
        }

        $confirmedToken = (string) $request->session()->get('inventario_qr_confirmed_token', '');
        if ($confirmedToken !== $token) {
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

        return view('admin.inventario.retiro', [
            'bodega' => $bodega,
            'items' => $items,
            'token' => $token,
            'bombero' => $bombero,
        ]);
    }

    public function confirm(Request $request, string $token)
    {
        if (!$request->session()->get('inventario_qr_bombero_id')) {
            return redirect()->route('inventario.qr.identificar.form', ['token' => $token]);
        }

        $request->session()->put('inventario_qr_confirmed_token', $token);

        return redirect()->route('inventario.qr.show', ['token' => $token]);
    }

    public function identificarForm(Request $request, string $token)
    {
        if ($request->boolean('reset')) {
            $request->session()->forget('inventario_qr_bombero_id');
            $request->session()->forget('inventario_qr_confirmed_token');
        }

        $bombero = null;
        $bomberoId = $request->session()->get('inventario_qr_bombero_id');
        if ($bomberoId) {
            $bombero = Bombero::query()->where('id', (int) $bomberoId)->first();
            if (!$bombero) {
                $request->session()->forget('inventario_qr_bombero_id');
            }
        }

        return view('admin.inventario.identificar', [
            'token' => $token,
            'bombero' => $bombero,
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
        $request->session()->put('inventario_qr_confirmed_token', $token);

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
            'item_id' => [
                'required',
                'integer',
                Rule::exists('inventario_items', 'id')->where(fn ($q) => $q->where('bodega_id', $bodega->id)->where('activo', true)),
            ],
            'cantidad' => ['required', 'integer', 'min:1'],
            'nota' => ['nullable', 'string', 'max:1000'],
        ]);

        $cantidad = (int) $validated['cantidad'];

        try {
            DB::transaction(function () use ($bodega, $cantidad, $validated, $bomberoId) {
                $item = InventoryItem::query()
                    ->where('id', (int) $validated['item_id'])
                    ->where('bodega_id', $bodega->id)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $item->stock < $cantidad) {
                    throw new \RuntimeException('Stock insuficiente.');
                }

                $item->update([
                    'stock' => (int) $item->stock - $cantidad,
                ]);

                $movementData = [
                    'bodega_id' => $bodega->id,
                    'item_id' => $item->id,
                    'tipo' => 'egreso',
                    'cantidad' => $cantidad,
                    'nota' => $validated['nota'] ?? null,
                    'creado_por' => null,
                ];
                if (Schema::hasColumn('inventario_movimientos', 'bombero_id')) {
                    $movementData['bombero_id'] = (int) $bomberoId;
                }

                InventoryMovement::create($movementData);
            });
        } catch (\Throwable $e) {
            $msg = $e instanceof \RuntimeException ? $e->getMessage() : 'No se pudo registrar el retiro.';
            return back()->withInput()->withErrors(['cantidad' => $msg]);
        }

        $request->session()->forget('inventario_qr_bombero_id');
        $request->session()->forget('inventario_qr_confirmed_token');

        return redirect()->route('inventario.qr.identificar.form', ['token' => $token])
            ->with('success', 'Retiro registrado correctamente.');
    }
}
