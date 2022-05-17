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
        Schema::create('china_areas', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('region')->nullable();
            $table->string('province_code')->nullable();
            $table->string('province')->nullable();
            $table->string('city_code')->nullable();
            $table->string('city')->nullable();
            $table->string('district_code')->nullable();
            $table->string('district')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('china_areas');
    }
};
