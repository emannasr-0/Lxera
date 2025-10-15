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
        Schema::create('bundle_bridging', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('bridging_id');
            $table->unsignedInteger('from_bundle_id');
            $table->unsignedInteger('to_bundle_id')->nullable();
            $table->foreign('bridging_id')->references('id')->on('bundles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('from_bundle_id')->references('id')->on('bundles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('to_bundle_id')->references('id')->on('bundles')->cascadeOnUpdate()->cascadeOnDelete();
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
        Schema::dropIfExists('bundle_bridging');
    }
};
