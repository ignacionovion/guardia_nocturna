<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        if (Schema::hasColumn('payments', 'amount')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('payments', 'billing_id')) {
                $table->unsignedBigInteger('billing_id')->nullable()->after('tenant_id');
            }
            if (! Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 12, 2)->nullable()->after('billing_id');
            }
            if (! Schema::hasColumn('payments', 'currency')) {
                $table->string('currency', 8)->default('CLP')->after('amount');
            }
            if (! Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method', 64)->nullable()->after('currency');
            }
            if (! Schema::hasColumn('payments', 'status')) {
                $table->string('status', 32)->default('pending')->after('payment_method');
            }
            if (! Schema::hasColumn('payments', 'reference')) {
                $table->string('reference', 190)->nullable()->after('status');
            }
            if (! Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable()->after('reference');
            }
            if (! Schema::hasColumn('payments', 'paid_at')) {
                $table->date('paid_at')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('payments', 'created_by_central_admin_id')) {
                $table->unsignedBigInteger('created_by_central_admin_id')->nullable()->after('paid_at');
            }
        });

        if (Schema::hasColumn('payments', 'monto')) {
            DB::table('payments')->orderBy('id')->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('payments')->where('id', $row->id)->update([
                        'amount' => $row->monto,
                        'payment_method' => $row->metodo_pago !== null && $row->metodo_pago !== ''
                            ? (string) $row->metodo_pago
                            : 'transferencia',
                        'status' => 'paid',
                        'notes' => $row->observacion,
                        'paid_at' => $row->fecha_pago,
                    ]);
                }
            });
        }

        Schema::table('payments', function (Blueprint $table): void {
            if (Schema::hasColumn('payments', 'monto')) {
                $table->dropColumn(['monto', 'fecha_pago', 'metodo_pago', 'observacion']);
            }
        });

        Schema::table('payments', function (Blueprint $table): void {
            if (Schema::hasColumn('payments', 'billing_id')) {
                $table->foreign('billing_id')
                    ->references('id')
                    ->on('tenant_billing')
                    ->nullOnDelete();
            }
        });

        Schema::table('payments', function (Blueprint $table): void {
            if (Schema::hasColumn('payments', 'created_by_central_admin_id')) {
                $table->foreign('created_by_central_admin_id')
                    ->references('id')
                    ->on('central_admins')
                    ->nullOnDelete();
            }
        });

    }

    public function down(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        if (! Schema::hasColumn('payments', 'amount')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table): void {
            if (Schema::hasColumn('payments', 'billing_id')) {
                $table->dropForeign(['billing_id']);
            }
            if (Schema::hasColumn('payments', 'created_by_central_admin_id')) {
                $table->dropForeign(['created_by_central_admin_id']);
            }
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->decimal('monto', 10, 2)->nullable();
            $table->date('fecha_pago')->nullable();
            $table->string('metodo_pago')->nullable();
            $table->text('observacion')->nullable();
        });

        if (Schema::hasColumn('payments', 'amount')) {
            DB::table('payments')->orderBy('id')->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('payments')->where('id', $row->id)->update([
                        'monto' => $row->amount ?? 0,
                        'fecha_pago' => $row->paid_at ?? now()->toDateString(),
                        'metodo_pago' => $row->payment_method,
                        'observacion' => $row->notes,
                    ]);
                }
            });
        }

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn([
                'billing_id',
                'amount',
                'currency',
                'payment_method',
                'status',
                'reference',
                'notes',
                'paid_at',
                'created_by_central_admin_id',
            ]);
        });
    }
};
