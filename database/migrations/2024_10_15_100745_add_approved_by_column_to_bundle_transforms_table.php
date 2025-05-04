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
        Schema::table('bundle_transforms', function (Blueprint $table) {
            //
            $table->unsignedInteger('approved_by')->nullable()->default(null);
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null')->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bundle_transforms', function (Blueprint $table) {
            //
            $table->dropForeign('bundle_transforms_approved_by_foreign');
            $table->dropColumn('approved_by');
        });
    }
};
