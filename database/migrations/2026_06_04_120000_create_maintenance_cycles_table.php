<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->string('month_year', 7);
            $table->decimal('amount', 14, 2);
            $table->decimal('late_fee', 14, 2)->default(0);
            $table->date('due_date');
            $table->boolean('bills_generated')->default(false);
            $table->timestamp('notifications_sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['society_id', 'month_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_cycles');
    }
};
