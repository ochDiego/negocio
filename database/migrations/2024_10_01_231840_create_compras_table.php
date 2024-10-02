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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();

            $table->decimal('gran_total', 10, 2)->nullable();
            $table->string('metodo_pago', 100)->nullable();
            $table->string('estado_pago', 100)->nullable();
            $table->text('notas', 500)->nullable();

            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
