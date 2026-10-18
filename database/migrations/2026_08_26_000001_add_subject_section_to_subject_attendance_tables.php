<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Additive only — every existing row keeps subject_section_id = NULL,
        // which is exactly how legacy (pre-refactor) sessions/records stay
        // identifiable forever: NULL + subject.type = 'madah' means "legacy
        // batch-wide session," NULL + subject.type = 'hafazan' means "always
        // was Halaqah-scoped," non-NULL means "new Madah section-scoped session."
        Schema::table('subject_attendance_sessions', function (Blueprint $table) {
            $table->foreignId('subject_section_id')->nullable()->after('batch_id')
                ->constrained('subject_sections')->nullOnDelete();
        });

        Schema::table('subject_attendance_records', function (Blueprint $table) {
            $table->foreignId('subject_section_id')->nullable()->after('batch_id')
                ->constrained('subject_sections')->nullOnDelete();
        });

        // batch_id stops being a required roster determinant for new sessions
        // (a Halaqah/Section roster is no longer guaranteed to be one batch),
        // but existing rows keep their historical value untouched.
        // Raw SQL: doctrine/dbal isn't installed, so ->change() isn't available.
        DB::statement('ALTER TABLE `subject_attendance_sessions` MODIFY `batch_id` BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `subject_attendance_sessions` MODIFY `batch_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('subject_attendance_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_section_id');
        });

        Schema::table('subject_attendance_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_section_id');
        });
    }
};
