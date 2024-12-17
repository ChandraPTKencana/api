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
        Schema::create('st_permission_group_user', function (Blueprint $table) {
            $table->id();
            $table->integer("ordinal");
            $table->boolean('p_change')->default(false);
            $table->foreignId('st_permission_group_id')->references('id')->on('st_permission_group')->onDelete('restrict')->onUpdate('cascade');
            $table->bigInteger('user_id');
            $table->foreign('user_id')->references('id_user')->on('is_users')->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('st_permission_group_user');
    }
};
