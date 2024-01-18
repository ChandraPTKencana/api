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
        Schema::table('st_transactions', function (Blueprint $table) {
            $table->date('input_at')->nullable();            
            $table->integer('input_ordinal')->default(0);            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('st_transactions', function (Blueprint $table) {
            $table->dropColumn('input_at');
            $table->dropColumn('input_ordinal');

            // $table->dropColumn('created_at');
        });

    }
};
