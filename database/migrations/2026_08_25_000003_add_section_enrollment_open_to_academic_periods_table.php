<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            // Centralized flag gating student self-service section enrollment/changes
            // for this period. Admin overrides always work regardless of this flag.
            $table->boolean('section_enrollment_open')->default(true)->after('status_locked');
        });
    }

    public function down(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->dropColumn('section_enrollment_open');
        });
    }
};
