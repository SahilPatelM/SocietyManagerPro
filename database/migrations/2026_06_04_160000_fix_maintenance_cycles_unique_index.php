<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs MySQL error 1553 when dropping maintenance_cycles_society_id_month_year_unique
 * (FK on society_id depends on that composite unique index).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('maintenance_cycles')) {
            return;
        }

        if (! Schema::hasColumn('maintenance_cycles', 'cycle_type')) {
            Schema::table('maintenance_cycles', function (Blueprint $table) {
                $table->string('cycle_type', 20)->default('general')->after('month_year');
            });
        }

        $this->ensureSocietyIdIndex();

        if ($this->hasIndex('maintenance_cycles', 'maintenance_cycles_society_id_month_year_unique')) {
            Schema::table('maintenance_cycles', function (Blueprint $table) {
                $table->dropUnique(['society_id', 'month_year']);
            });
        }

        if (! $this->hasIndex('maintenance_cycles', 'maintenance_cycles_society_id_month_year_cycle_type_unique')) {
            Schema::table('maintenance_cycles', function (Blueprint $table) {
                $table->unique(['society_id', 'month_year', 'cycle_type']);
            });
        }

        if (Schema::hasTable('maintenance_bills') && ! Schema::hasColumn('maintenance_bills', 'bill_type')) {
            Schema::table('maintenance_bills', function (Blueprint $table) {
                $table->string('bill_type', 20)->default('general')->after('month_year');
            });
        }
    }

    public function down(): void
    {
        // Intentionally left empty — keeps the corrected schema in place.
    }

    protected function ensureSocietyIdIndex(): void
    {
        if ($this->hasIndex('maintenance_cycles', 'maintenance_cycles_society_id_index')
            || $this->hasIndex('maintenance_cycles', 'maintenance_cycles_society_id_foreign')) {
            return;
        }

        Schema::table('maintenance_cycles', function (Blueprint $table) {
            $table->index('society_id', 'maintenance_cycles_society_id_index');
        });
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        return in_array($indexName, Schema::getIndexListing($table), true);
    }
};
