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
        Schema::create('st_permission_user_detail', function (Blueprint $table) {
            $table->id();
            $table->integer("ordinal");
            $table->boolean('p_change')->default(false);
            $table->bigInteger('user_id');
            $table->foreign('user_id')->references('id_user')->on('is_users')->onDelete('restrict')->onUpdate('cascade');
            $table->string('st_permission_list_name',255);
            $table->bigInteger('created_user');
            $table->foreign('created_user')->references('id_user')->on('is_users')->onDelete('restrict')->onUpdate('cascade');
            $table->bigInteger('updated_user');
            $table->foreign('updated_user')->references('id_user')->on('is_users')->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('st_permission_user_detail');
    }
};
