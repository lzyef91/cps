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
        // 品牌信息
        // [home][brandCertStatus]
        // -1:未做品牌认证
        // 99:认证失效
        // 4:正常
        // [brand][brandCertClientDTOS]数组内元素
        Schema::create('youzan_shop_brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->index();
            // 品牌名称
            $table->string('brand_name')->nullable(); // [brandName]
            // 品牌英文名
            $table->string('brand_name_en')->nullable(); // [brandNameEN]
            // 品牌授权类型
            $table->string('brand_cert_type')->nullable(); // [brandCertType]
            // 授权等级
            $table->string('brand_auth_level')->nullable(); // [authorizationLevel]
            // 品牌类目
            $table->string('brand_category')->nullable(); // [tradeMarkCategory]
            // 品牌有效期
            $table->string('valid_time')->nullable(); // [validTime]
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
        Schema::dropIfExists('youzan_shop_brands');
    }
};
