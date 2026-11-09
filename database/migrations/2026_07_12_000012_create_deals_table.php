<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->enum('stage', ['inquiry', 'visit_done', 'negotiation', 'deal_closed', 'cancelled'])->default('inquiry');
            $table->decimal('agreed_price', 12, 2)->nullable();
            $table->enum('commission_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('commission_value', 10, 2)->nullable();
            $table->decimal('commission_amount', 12, 2)->nullable();
            $table->date('deal_date')->nullable();
            $table->date('close_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->enum('status', ['active', 'closed', 'cancelled'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
