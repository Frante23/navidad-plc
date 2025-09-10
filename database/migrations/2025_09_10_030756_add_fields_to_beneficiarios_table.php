<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('beneficiarios', function (Blueprint $table) {
            $table->string('direccion')->nullable();
            $table->foreignId('organizacion_id')->constrained('organizaciones');
        });
    }

    public function down()
    {
        Schema::table('beneficiarios', function (Blueprint $table) {
            $table->dropColumn('direccion');
            $table->dropForeign(['organizacion_id']);
            $table->dropColumn('organizacion_id');
        });
    }

};
