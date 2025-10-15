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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('company_name')->unique();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('url_name')->unique()->nullable();
            $table->string('facebook')->nullable();
            $table->string('Instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();
            $table->string('other_link')->nullable();
            $table->boolean('independent_copyright')->default(false);
            $table->boolean('accepted')->default(false);
            $table->boolean('activated')->default(false);
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
        Schema::dropIfExists('organizations');
    }
};
