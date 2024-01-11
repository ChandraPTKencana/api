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
        Schema::create('st_transaction_detailsx', function (Blueprint $table) {
            // $table->bigInteger("st_transaction_id");
            $table->foreignId('st_transaction_id')->references('id')->on('st_transactions')->onDelete('restrict')->onUpdate('cascade');

            $table->integer("ordinal");
            
            $table->foreignId('st_item_id')->references('id')->on('st_items')->onDelete('restrict')->onUpdate('cascade');

            $table->integer("qty_in")->nullable();;
            $table->integer("qty_out")->nullable();;
            $table->integer("qty_reminder")->nullable();;
            $table->text('note')->nullable();

            

            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('st_transaction_detailsx', function (Blueprint $table) {
            $table->dropForeign(['st_transaction_id']);
            $table->dropForeign(['st_item_id']);
        });
        Schema::dropIfExists('st_transaction_detailsx');
    }
};
