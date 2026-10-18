<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('student', function (Blueprint $table) {
            /* ADD NEW COLUMNS */
            $table->string('asasi_diploma_id')->nullable();
            $table->string('ijazah_id')->nullable();
            $table->text('remarks')->nullable();

            /* DROP UNUSED COLUMNS */
            $table->dropColumn([
                'postcode',
                'town',
                'race'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            /* RESTORE */
            $table->string('postcode')->nullable();
            $table->string('town')->nullable();
            $table->string('race')->nullable();

            /* REMOVE NEW */
            $table->dropColumn([
                'program_id',
                'asasi_diploma_id',
                'ijazah_id',
                'remarks'
            ]);

            $table->dropForeign(['program_id']);
        });
    }
};
