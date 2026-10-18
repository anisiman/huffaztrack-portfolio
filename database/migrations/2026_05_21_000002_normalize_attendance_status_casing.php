<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize existing PascalCase / legacy status values to lowercase
        DB::table('attendance')
            ->whereRaw("LOWER(status) = 'present'")
            ->update(['status' => 'present']);

        DB::table('attendance')
            ->whereRaw("LOWER(status) = 'absent'")
            ->update(['status' => 'absent']);

        // 'Medical' was the old excused-absence value
        DB::table('attendance')
            ->whereRaw("LOWER(status) = 'medical'")
            ->update(['status' => 'absent_with_reason']);

        // Fix column default so new rows also use lowercase
        Schema::table('attendance', function (Blueprint $table) {
            $table->string('status')->default('present')->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->string('status')->default('Present')->change();
        });
    }
};
