<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgery_requests', function (Blueprint $t) {
            $t->id();

            // Quem solicitou (médico)
            $t->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();

            // Quem aprovou (enfermeiro/admin) — preenchido na aprovação
            $t->foreignId('nurse_id')->nullable()->constrained('users')->nullOnDelete();

            // Data e horário do procedimento
            $t->date('date')->index();
            $t->time('start_time');
            $t->time('end_time');

            // Dados mínimos do caso
            $t->string('patient_name');
            $t->string('procedure');

            // Estados: requested (laranja), approved (verde), rejected, cancelled
            $t->enum('status', ['requested', 'approved', 'rejected', 'cancelled'])
              ->default('requested')->index();

            // Campo livre para metadados (ex: confirm_docs=true)
            $t->json('meta')->nullable();

            $t->timestamps();

            // Evita o mesmo MÉDICO criar 2 pedidos com mesmo intervalo exato
            $t->unique(['doctor_id', 'date', 'start_time', 'end_time'], 'uq_doctor_date_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgery_requests');
    }
};
