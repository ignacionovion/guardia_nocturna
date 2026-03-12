<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_billing', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('plan')->default('basico');
            $table->decimal('monto', 10, 2)->default(0);
            $table->enum('estado_pago', ['pagado', 'pendiente', 'vencido', 'suspendido'])->default('pendiente');
            $table->date('fecha_vencimiento')->nullable();
            $table->date('fecha_ultimo_pago')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id', 'estado_pago']);
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_billing');
    }
};
