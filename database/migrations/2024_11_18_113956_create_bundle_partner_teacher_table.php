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
        Schema::create('bundle_partner_teacher', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("bundle_id");
            $table->foreign('bundle_id')->references('id')->on('bundles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger("teacher_id");
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
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
        Schema::dropIfExists('bundle_partner_teacher');
    }
};
