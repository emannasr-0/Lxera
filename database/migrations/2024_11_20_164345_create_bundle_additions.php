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
        Schema::create('bundle_additions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('bundle_id');
            $table->unsignedInteger('addition_bundle_id');
            $table->foreign('bundle_id')->references('id')->on('bundles')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('addition_bundle_id')->references('id')->on('bundles')->cascadeOnDelete()->cascadeOnUpdate();
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
        Schema::dropIfExists('bundle_additions');
    }
};
