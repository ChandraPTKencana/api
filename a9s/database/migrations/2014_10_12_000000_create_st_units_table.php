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
        Schema::create('st_units', function (Blueprint $table) {
            $table->mediumInteger("id",true,true);

            $table->string('name',5)->unique();
            $table->timestamp('created_at')->useCurrent();

            $table->bigInteger('created_by');
            $table->foreign('created_by')->references('id_user')->on('is_users')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->bigInteger('updated_by');
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
        Schema::table('st_units', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });
        Schema::dropIfExists('st_units');
    }
};
