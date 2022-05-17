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
        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            // 管理员用户ID
            $table->string('admin_user_id')->index();
            // 批处理任务uuid
            $table->string('batch_id')->nullable();
            // 文件名
            $table->string('name');
            // 文件路径
            $table->string('path');
            // 文件存储
            $table->string('disk');
            // 错误的类
            $table->string('exception')->nullable();
            // 错误信息
            $table->longText('exception_msg')->nullable();
            //
            $table->json('exported_fields')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('succeed_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('start_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exports');
    }
};
