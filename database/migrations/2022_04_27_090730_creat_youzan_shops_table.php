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
        Schema::create('youzan_shops', function (Blueprint $table) {
            $table->id();
            // 店铺名称
            $table->string('name')->nullable(); // [home][shopName]
            $table->unsignedBigInteger('kdt_id')->nullable()->index();
            $table->string('address')->nullable();
            $table->string('mp_qrcode')->nullable();
            // 主体信息
            // [home][principalCertRecordResult][status]
            // -1:未做主题认证
            // 4:正常
            $table->string('principal_name')->nullable(); // [principal][principalName]
            $table->tinyInteger('principal_type')->nullable(); // [principal][subjectCertType] 1:个体 2:企业 3:个体工商户
            $table->string('principal_address')->nullable(); // [principal][address]

            // 0:没有联系方式 1:有联系方式未领取 2:无法领取 3:未收录 99:已领取
            $table->tinyInteger('has_contacts')->index()->nullable();
            $table->unsignedInteger('total_contacts')->nullable();

            // 开店时间
            $table->dateTime('open_at')->nullable(); // [home][applyTime]
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
        Schema::dropIfExists('youzan_shops');
    }
};
