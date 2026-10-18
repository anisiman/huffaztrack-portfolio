<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tasmi')) {
            return;
        }

        Schema::table('tasmi', function (Blueprint $table) {
            if (! Schema::hasColumn('tasmi', 'tasmi_date')) {
                $table->date('tasmi_date')->nullable()->after('target_page');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tasmi') || ! Schema::hasColumn('tasmi', 'tasmi_date')) {
            return;
        }

        Schema::table('tasmi', function (Blueprint $table) {
            $table->dropColumn('tasmi_date');
        });
    }
};
