<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        Schema::create('inventory_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('labrarian'); // Кто проверял
            $table->string('stor_loc'); // Место хранения
            $table->string('place_code'); // Расстановочный шифр
            $table->string('inv_num'); // Инвентарный номер
            $table->integer('copies_count'); // Количество экземпляров
            $table->text('book_descr'); // Описание книги
            $table->timestamps(); // created_at и updated_at
        });
    }
    public function down()
    {
        Schema::dropIfExists('inventory_approvals');
    }
};
