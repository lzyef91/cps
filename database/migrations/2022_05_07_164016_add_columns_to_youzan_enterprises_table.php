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
        Schema::table('youzan_enterprises', function (Blueprint $table) {
            $table->string('enterprise_status')->nullable()->after('enterprise_type');
            $table->string('enterprise_uniscid')->index()->nullable()->after('enterprise_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('youzan_enterprises', function (Blueprint $table) {
            //
        });
    }
};
