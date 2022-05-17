<?php

namespace App\Models\Youzan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'youzan_contacts';

    protected $guarded = [];

    /**
     * 没有联系方式
     */
    public static $NO_CONTACT = 'no_contact';
    /**
     * 待领取
     */
    public static $WAIT_TO_GRAB = 'wait_to_grab';
    /**
     * 没有权限须登录企客后台查看
     */
    public static $NO_AUTH = 'no_auth';
    /**
     * 企客没有收录
     */
    public static $NO_REPORT = 'no_report';
    /**
     * 已领取到本地
     */
    public static $CONTACT_READY = 'contact_ready';

    public static $STATUS = [
        'no_contact' => 0,
        'wait_to_grab' => 1,
        'no_auth' => 2,
        'no_report' => 3,
        'contact_ready' => 99
    ];
    /**
     * 手机类型
     */
    public static $TYPE_MOBILE = 1;
    /**
     * 固话类型
     */
    public static $TYPE_PHONE = 2;
    /**
     * 邮箱类型
     */
    public static $TYPE_EMAIL = 3;

    public static $CONTACT_TYPE_MAP = [
        1 => '手机',
        2 => '固话',
        3 => '邮箱'
    ];
}
