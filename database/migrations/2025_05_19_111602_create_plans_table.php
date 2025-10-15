<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('name');
            $table->string('name_ar');
            $table->text('description')->nullable();
            $table->enum('type', ['Basic', 'Pro', 'Enterprise']);
            $table->string('price')->nullable();
            $table->integer('max_users')->nullable();
            $table->integer('max_bundles')->nullable();
            $table->integer('max_webinars')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
};
