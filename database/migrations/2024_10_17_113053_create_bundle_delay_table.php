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
        Schema::create('bundle_delay', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("user_id")->nullable();
            $table->unsignedInteger("from_bundle_id")->nullable();
            $table->unsignedInteger("to_bundle_id")->nullable();
            $table->unsignedBigInteger("service_request_id")->nullable();
            $table->unsignedInteger('approved_by')->nullable()->default(null);
            $table->string('status')->default("pending");
            $table->string('reason')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('from_bundle_id')->references('id')->on('bundles')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('to_bundle_id')->references('id')->on('bundles')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('service_request_id')->references('id')->on('service_user')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null')->onUpdate("cascade");
            
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
        Schema::dropIfExists('bundle_delay');
    }
};
