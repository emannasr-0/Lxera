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
        Schema::table('bundles', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('batch_id')->nullable()->after('category_id');
            $table->foreign('batch_id')->references('id')->on('study_classes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bundles', function (Blueprint $table) {
            //
            $table->dropForeign('bundles_batch_id_foreign');
            $table->dropColumn('batch_id');
            
        });
    }
};
