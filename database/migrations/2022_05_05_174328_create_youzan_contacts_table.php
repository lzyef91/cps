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
        Schema::create('youzan_contacts', function (Blueprint $table) {
            $table->id();
            // 有赞店铺ID
            $table->unsignedBigInteger('shop_id')->index();
            // 企客联系人ID Api:contact [contactId]
            $table->string('qike_contact_id')->index();
            // 联系方式 1:手机2:固话3:email
            $table->tinyInteger('contact_type')->index();
            // 联系方式 手机/固话/邮箱 Api:contact [contactNo]
            $table->string('contact_no')->index()->nullable();
            // 联系人 Api:contact [contactNo]
            $table->string('name')->nullable();
            // 联系人职位 Api:contact [duty]
            $table->string('duty')->nullable();
            // 联系人地区 Api:contact [location]
            $table->string('location')->nullable();
            // 来源类型 Api:contact [sources][0][source]
            $table->string('source_type')->nullable();
            // 来源网页 Api:contact [sources][0][url]
            $table->string('source_url')->nullable();

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
        Schema::dropIfExists('youzan_contacts');
    }
};
