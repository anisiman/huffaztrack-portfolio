<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('muqarrar', function (Blueprint $table) {
            if (!Schema::hasColumn('muqarrar', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('muqarrar', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('muqarrar', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        DB::statement('UPDATE muqarrar SET created_at = NOW(), updated_at = NOW() WHERE created_at IS NULL');
    }

    public function down(): void
    {
        Schema::table('muqarrar', function (Blueprint $table) {
            $table->dropColumn(['notes', 'created_at', 'updated_at']);
        });
    }
};
