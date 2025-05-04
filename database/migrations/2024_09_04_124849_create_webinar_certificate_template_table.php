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
        Schema::create('certificate_template_webinar', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('certificate_template_id');
            $table->foreign('certificate_template_id')->references('id')->on('certificates_templates')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('webinar_id');
            $table->foreign('webinar_id')->references('id')->on('webinars')->cascadeOnDelete()->cascadeOnUpdate();
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
        Schema::dropIfExists('certificate_template_webinar');
    }
};
