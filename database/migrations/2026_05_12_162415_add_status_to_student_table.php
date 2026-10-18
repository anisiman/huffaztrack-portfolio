<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student', function (Blueprint $col) {
            // Adding the status column after the lecturer_id column
            $col->string('status')->after('lecturer_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('student', function (Blueprint $col) {
            $col->dropColumn('status');
        });
    }
};