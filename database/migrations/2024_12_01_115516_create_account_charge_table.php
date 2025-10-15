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
        Schema::create('account_charge', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('creator_id')->nullable();
            $table->unsignedInteger('order_id')->nullable();
            $table->decimal('amount');
            $table->enum('type', ['addiction', 'deduction']);
            $table->text('description')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('creator_id')->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnUpdate()->nullOnDelete();
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
        Schema::dropIfExists('account_charge');
    }
};
