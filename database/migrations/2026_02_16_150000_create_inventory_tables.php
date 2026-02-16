<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bodegas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ubicacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('inventario_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bodega_id')->constrained('bodegas')->cascadeOnDelete();
            $table->string('categoria')->nullable();
            $table->string('titulo');
            $table->string('variante')->nullable();
            $table->string('unidad')->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['bodega_id', 'titulo']);
            $table->index(['bodega_id', 'categoria']);
        });

        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bodega_id')->constrained('bodegas')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inventario_items')->cascadeOnDelete();
            $table->string('tipo');
            $table->integer('cantidad');
            $table->text('nota')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['bodega_id', 'created_at']);
            $table->index(['item_id', 'created_at']);
        });

        Schema::create('inventario_qr_links', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->string('tipo');
            $table->foreignId('bodega_id')->nullable()->constrained('bodegas')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tipo', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_qr_links');
        Schema::dropIfExists('inventario_movimientos');
        Schema::dropIfExists('inventario_items');
        Schema::dropIfExists('bodegas');
    }
};
