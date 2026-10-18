<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTA: Jika nama jadual asal anda ialah 'student_subjectt', sila tukar nama di bawah
        Schema::table('student_subject', function (Blueprint $table) {
            
            // 1. Buang foreign key & kolum semester_id (Sebab subjek dah link dengan semester)
            try {
                $table->dropForeign(['semester_id']);
            } catch (\Exception $e) {
                // Abaikan jika foreign key tiada
            }
            $table->dropColumn('semester_id');

            // 2. Buang foreign key lama untuk subject_id (jika wujud kekangan sebelum ini)
            try {
                $table->dropForeign(['subject_id']);
            } catch (\Exception $e) {
                // Abaikan jika foreign key tiada
            }
            $table->dropColumn('subject_id');

            // 3. Tambah kolum subjectT_id yang betul dan hubungkan (constrained) ke jadual subjectt
            $table->foreignId('subjectT_id')
                  ->after('student_id')
                  ->nullable() // letak nullable dahulu demi keselamatan data sedia ada
                  ->constrained('subjectt', 'subjectT_id') // merujuk ke jadual 'subjectt' dan primary key 'subjectT_id'
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('student_subject', function (Blueprint $table) {
            // Undo: Buang subjectT_id
            $table->dropForeign(['subjectT_id']);
            $table->dropColumn('subjectT_id');

            // Undo: Letak balik subject_id yang lama
            $table->unsignedBigInteger('subject_id')->nullable();

            // Undo: Letak balik semester_id
            $table->unsignedBigInteger('semester_id')->nullable();
        });
    }
};
