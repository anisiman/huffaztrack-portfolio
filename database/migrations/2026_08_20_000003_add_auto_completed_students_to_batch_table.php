<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batch', function (Blueprint $table) {
            $table->json('auto_completed_students')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('batch', function (Blueprint $table) {
            $table->dropColumn('auto_completed_students');
        });
    }
};
