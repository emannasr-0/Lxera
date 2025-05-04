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
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->integer('bundle_id')->unsigned()->nullable();
            $table->integer('webinar_id')->unsigned()->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('bundle_id')->on('bundles')->references('id')->cascadeOnDelete();
            $table->foreign('webinar_id')->on('webinars')->references('id')->cascadeOnDelete();
            $table->foreign('service_id')->on('services')->references('id')->cascadeOnDelete();
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
        Schema::dropIfExists('service_items');
    }
};
