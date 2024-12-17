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
        Schema::create('st_permission_group', function (Blueprint $table) {
            $table->id();
            $table->string('name',30)->unique();
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
        Schema::dropIfExists('st_permission_group');
    }
};
