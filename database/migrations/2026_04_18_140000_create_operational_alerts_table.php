<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->create('operational_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_key', 160)->unique();
            $table->string('source', 64);
            $table->string('severity', 16);
            $table->string('status', 16);
            $table->string('title', 255);
            $table->text('message');
            $table->timestamp('first_triggered_at')->nullable();
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity']);
            $table->index('last_triggered_at');
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('operational_alerts');
    }
};
