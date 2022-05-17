<?php

namespace App\Admin\Forms;

use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Cache;
use App\Models\Youzan\Shop;
use Dcat\Admin\Admin;

class QikeJwtTokenForm extends Form
{
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        // 权限判断
        if (!Admin::user()->isRole('administrator')) {
            return $this->response()->error('没有权限,请联系管理员操作');
        }
        // 缓存jwtToken
        Cache::put(Shop::$TOKEN_CACHE_KEY, $input['token'], 23 * 3600);


        return $this->response()
				->success("设置成功")
				->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('token', '企客令牌')->required();
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
            'token'  => Cache::get(Shop::$TOKEN_CACHE_KEY)
        ];
    }


}
