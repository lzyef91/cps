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
        Schema::create('youzan_enterprises', function (Blueprint $table) {
            $table->id();

            // 有赞店铺ID
            $table->unsignedBigInteger('shop_id')->index();
            // 企客企业ID Api:query [entId]
            $table->string('qike_enterprise_id')->index();
            // 企业类型 Api:basic [entType]
            $table->string('enterprise_type')->fulltext()->nullable();
            // 法人 Api:basic [legalPersonName]
            $table->string('legal_person_name')->nullable();
            // 地区代码 Api:basic [regionCode]
            $table->string('region_code')->index()->nullable();
            // 地区 Api:basic [region]
            $table->string('region')->nullable();
            // 一级区划
            $table->string('region_province_code')->index()->nullable();
            $table->string('region_province')->nullable();
            // 二级区划
            $table->string('region_city_code')->index()->nullable();
            $table->string('region_city')->nullable();
            // 三级区划
            $table->string('region_district_code')->index()->nullable();
            $table->string('region_district')->nullable();
            // 公司规模 Api:basic [assTag]
            $table->string('size')->index()->nullable();
            // 公司建立时间 Api:basic [esDate]
            $table->dateTime('established_at')->nullable();

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
        Schema::dropIfExists('youzan_enterprises');
    }
};
