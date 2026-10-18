<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjectt', function (Blueprint $table) {
            // 1. Tambah subjectT_code
            $table->string('subjectT_code')->after('subjectT_id')->nullable();

            // 2. Buang kolum lama lecturer_id (Gunakan try-catch jika risau foreign key kekang gagal dibuang)
            try {
                $table->dropForeign(['lecturer_id']);
            } catch (\Exception $e) {
                // Abaikan jika kekunci asing tiada
            }
            $table->dropColumn('lecturer_id');

            // 3. Tambah semester_id menggunakan format foreignId demi keselamatan jenis data
            // The actual table name is 'semester', not 'semesters'.
            $table->foreignId('semester_id')
                  ->after('credit_hours')
                  ->nullable()
                  ->constrained('semester', 'semester_id') // parameter: ('nama_jadual', 'nama_kolum_primary')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('subjectt', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
            $table->unsignedBigInteger('lecturer_id')->nullable();
            $table->dropColumn('subjectT_code');
        });
    }
};