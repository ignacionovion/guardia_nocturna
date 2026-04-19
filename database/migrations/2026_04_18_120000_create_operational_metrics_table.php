<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->create('operational_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key', 160)->unique();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('operational_metrics');
    }
};
