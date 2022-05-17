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
        Schema::table('youzan_shops', function (Blueprint $table) {
            $table->unsignedInteger('total_shops')->default(1)->index()->after('total_contacts');
            $table->json('other_shops')->nullable()->after('total_shops');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('youzan_shops', function (Blueprint $table) {
            //
        });
    }
};
