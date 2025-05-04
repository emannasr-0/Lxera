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
        Schema::table('students', function (Blueprint $table) {
            //
            $table->string('identifier_num')->nullable()->default(null)->change();
            $table->string('en_name')->nullable()->default(null)->change();
            $table->string('country')->nullable()->default(null)->change();
            $table->string('town')->nullable()->default(null)->change();
            $table->date('birthdate')->nullable()->default(null)->change();
            $table->string('phone')->nullable()->default(null)->change();
            $table->string('mobile')->nullable()->default(null)->change();
            $table->boolean('deaf')->nullable()->default(null)->change();
            $table->boolean('healthy')->nullable()->default(null)->change();
            $table->string('nationality')->nullable()->default(null)->change();
            $table->string('referral_person')->nullable()->default(null)->change();
            $table->string('relation')->nullable()->default(null)->change();
            $table->string('referral_email')->nullable()->default(null)->change();
            $table->string('referral_phone')->nullable()->default(null)->change();
            $table->string('about_us')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            //
            $table->string('en_name')->change();
            $table->string('country')->change();
            $table->string('town')->change();
            $table->date('birthdate')->change();
            $table->string('phone')->change();
            $table->string('mobile')->change();
            $table->boolean('deaf')->change();
            $table->boolean('healthy')->change();
            $table->string('nationality')->change();
            $table->string('referral_person')->change();
            $table->string('relation')->change();
            $table->string('referral_email')->change();
            $table->string('referral_phone')->change();
            $table->string('about_us')->change();
        });
    }
};
