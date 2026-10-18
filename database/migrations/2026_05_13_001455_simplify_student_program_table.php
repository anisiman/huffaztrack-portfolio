<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_programs', function (Blueprint $table) {
            // Drop the columns
            $table->dropColumn(['status', 'enrollment_id']);
        });
    }

    public function down(): void
    {
        Schema::table('student_programs', function (Blueprint $table) {
            // If you need to rollback, this adds them back
            $table->string('status')->nullable();
            $table->string('enrollment_id')->nullable();
        });
    }
};