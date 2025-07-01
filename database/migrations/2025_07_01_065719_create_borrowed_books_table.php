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
        Schema::create('borrowed_books', function (Blueprint $table) {
            $table->id();
            $table->string('labrarian'); // Кто проверял
            $table->string('reader'); // Место хранения
            $table->string('db');
            $table->string('inv_num'); // Инвентарный номер
            $table->string('giveDate'); // Дата выдачи
            $table->string('returnDate'); // Дата возврата       
            $table->text('book_descr'); // Описание книги
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowed_books');
    }
};
