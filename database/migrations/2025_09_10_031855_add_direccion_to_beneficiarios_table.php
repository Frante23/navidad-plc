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
            $table->string('direccion')->after('sexo')->nullable(); // se agrega después de 'sexo', opcional si quieres permitir nulos
        });
    }

    public function down()
    {
        Schema::table('beneficiarios', function (Blueprint $table) {
            $table->dropColumn('direccion');
        });
    }

};
