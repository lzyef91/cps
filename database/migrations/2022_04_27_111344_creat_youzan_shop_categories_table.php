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
        // 主体信息
        // [home][principalCertRecordResult][status]
        // -1:未做主题认证
        // 4:正常
        // [principal][categoryCertInfo]数组内元素
        Schema::create('youzan_shop_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->index();
            // 行业类目
            $table->string('category_name'); // [categoryName]
            // 行业类目代码 例：0100080012
            $table->string('category_code')->index(); // [categoryCode]
            // 1:主营 0:兼营
            $table->tinyInteger('major'); // [majar]
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
        Schema::dropIfExists('youzan_shop_categories');
    }
};
