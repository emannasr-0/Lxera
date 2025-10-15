<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('ar_name');
            $table->string('en_name');
            $table->string('country');
            $table->string('area')->nullable();
            $table->string('city')->nullable();
            $table->string('email');
            $table->date('birthdate');
            $table->enum('gender', ['male', 'female']);
            $table->string('phone');
            $table->boolean('deaf');
            $table->boolean('healthy');
            $table->string('nationality');
            $table->string('job')->nullable();
            $table->string('job_type')->nullable();
            $table->string('referral_person');
            $table->string('relation');
            $table->string('referral_email');
            $table->string('referral_phone');
            $table->string('about_us');

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
        Schema::dropIfExists('students');
    }
}
