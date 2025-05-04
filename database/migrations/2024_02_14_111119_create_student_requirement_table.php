<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_requirement', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("bundle_student_id")->unique();
            $table->string('identity_type');
            $table->string('identity_attachment');
            $table->string('admission_attachment');
            $table->string('status')->default("pending");
            $table->unsignedInteger('approved_by')->nullable()->default(null);

            $table->foreign('bundle_student_id')->references('id')->on('bundle_student')->onDelete("cascade")->onUpdate("cascade");
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
        Schema::dropIfExists('student_requirement');
    }
};
