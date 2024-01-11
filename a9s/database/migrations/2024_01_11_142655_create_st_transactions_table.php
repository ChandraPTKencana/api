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
        Schema::create('st_transactions', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('hrm_revisi_lokasi_id');
            $table->foreign('hrm_revisi_lokasi_id')->references('id')->on('hrm_revisi_lokasi')->onDelete('restrict')->onUpdate('cascade');

            $table->text('note')->nullable();
            $table->enum('status', array('pending','done'));
            $table->enum('type', array('in','used','transfer'));

            $table->timestamp('requested_at')->useCurrent();

            $table->bigInteger('requested_by');
            $table->foreign('requested_by')->references('id_user')->on('is_users')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamp("confirmed_at")->nullable();   
            
            $table->bigInteger('confirmed_by')->nullable();
            $table->foreign('confirmed_by')->nullable()->references('id_user')->on('is_users')->onDelete('restrict')->onUpdate('cascade');

            $table->bigInteger('hrm_revisi_lokasi_source_id')->nullable();
            $table->foreign('hrm_revisi_lokasi_source_id')->nullable()->references('id')->on('hrm_revisi_lokasi')->onDelete('restrict')->onUpdate('cascade');

            $table->bigInteger('hrm_revisi_lokasi_target_id');
            $table->foreign('hrm_revisi_lokasi_target_id')->references('id')->on('hrm_revisi_lokasi')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
 
            $table->foreignId('ref_id')->nullable()->references('id')->on('st_transactions')->onDelete('restrict')->onUpdate('cascade');

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
            $table->dropForeign(['hrm_revisi_lokasi_id']);
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['confirmed_by']);
            $table->dropForeign(['hrm_revisi_lokasi_source_id']);
            $table->dropForeign(['hrm_revisi_lokasi_target_id']);
            $table->dropForeign(['ref_id']);
        });
        Schema::dropIfExists('st_transactions');
    }
};
