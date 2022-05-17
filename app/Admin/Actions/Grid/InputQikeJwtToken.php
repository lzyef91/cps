<?php

namespace App\Admin\Actions\Grid;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Admin\Forms\QikeJwtTokenForm;
use Dcat\Admin\Widgets\Modal;

class InputQikeJwtToken extends AbstractTool
{
    /**
     * @return string
     */
	protected $title = '录入令牌';

    public function html()
   {
       $form = QikeJwtTokenForm::make();

       $button = <<<HTML
       <button class="btn btn-primary btn-outline">{$this->title}</button>
HTML;
       $body = <<<HTML
       <div class="jumbotron">
            <p class="lead">获取方法</p>
            <p>1.使用Chrome浏览器登录企客后台</p>
            <p>2.右键选择“检查”按钮,在弹出的操作见面中选择Network</p>
            <p>4.任选一项在Hearders中复制Request Headers中的Authorization值粘贴</p>
        </div>
       $form
HTML;
       return Modal::make()->lg()
            ->title($this->title)
            ->body($body)
            ->button($button)->render();
   }

}
