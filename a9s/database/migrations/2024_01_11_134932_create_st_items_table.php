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
        Schema::create('st_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->double('value')->default(0);
            $table->text('note')->nullable();

            $table->mediumInteger('st_unit_id',false,true);
            $table->foreign('st_unit_id')->references('id')->on('st_units')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamp('created_at')->useCurrent();

            $table->bigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id_user')->on('is_users')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->bigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id_user')->on('is_users')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('st_items', function (Blueprint $table) {
            $table->dropForeign(['st_unit_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });
        Schema::dropIfExists('st_items');
    }
};
