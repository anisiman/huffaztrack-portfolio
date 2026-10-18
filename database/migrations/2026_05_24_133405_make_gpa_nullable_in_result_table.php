<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::table('result', function (Blueprint $table) {

            $table->decimal('gpa', 3, 2)->nullable()->change();
            $table->decimal('cgpa', 3, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('result', function (Blueprint $table) {

            $table->decimal('gpa', 3, 2)->nullable(false)->change();
            $table->decimal('cgpa', 3, 2)->nullable(false)->change();
        });
    }
};
