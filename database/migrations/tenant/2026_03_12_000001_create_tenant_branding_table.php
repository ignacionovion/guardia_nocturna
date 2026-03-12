<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_branding', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->unique();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('nombre_empresa')->nullable();
            $table->string('color_primario', 7)->nullable(); // #RRGGBB
            $table->string('color_secundario', 7)->nullable();
            $table->string('color_sidebar', 7)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_branding');
    }
};
