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
        Schema::create('agereport_gestao_rede', function (Blueprint $table) {
            $table->id();
            $table->string('olt_id');
            $table->string('olt_name')->nullable();
            $table->string('detail_slot')->nullable();
            $table->string('pon')->nullable();
            $table->string('serial')->nullable();
            $table->string('admin_status')->nullable();
            $table->string('oper_status')->nullable();
            $table->string('olt_rx_sig_level')->nullable();
            $table->string('ont_olt_distance')->nullable();
            $table->string('desc1')->nullable();
            $table->string('desc2')->nullable();
            $table->string('hostname')->nullable();
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
        Schema::dropIfExists('agereport_gestao_rede');
    }
};
