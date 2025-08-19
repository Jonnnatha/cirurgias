<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgery_checklist_items', function (Blueprint $t) {
            $t->id();

            // Cada item pertence a um pedido específico
            $t->foreignId('surgery_request_id')->constrained()->cascadeOnDelete();

            // Texto do item (ex: "Consentimento assinado")
            $t->string('item_text');

            // Marcação de conferência
            $t->boolean('checked')->default(false)->index();
            $t->timestamp('checked_at')->nullable();
            $t->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();

            $t->timestamps();

            $t->index(['surgery_request_id', 'checked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgery_checklist_items');
    }
};
