<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('parking_allocations', 'start_hour')) {
            Schema::table('parking_allocations', function (Blueprint $table) {
                $table->dropColumn(['start_hour', 'end_hour']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('parking_allocations', function (Blueprint $table) {
            $table->unsignedTinyInteger('start_hour')->default(6)->after('vehicle_number');
            $table->unsignedTinyInteger('end_hour')->default(22)->after('start_hour');
        });
    }
};
