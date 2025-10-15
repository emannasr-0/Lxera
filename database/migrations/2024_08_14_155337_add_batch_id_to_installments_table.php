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
        Schema::table('installments', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('batch_id')->nullable()->after('target_type');
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
        Schema::table('installments', function (Blueprint $table) {
            //
            $table->dropForeign('installments_class_id_foreign');
            $table->dropColumn('batch_id');
        });
    }
};
