<?php

namespace App\Admin\Actions\Grid;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Admin\Forms\ImportYouzanShopsForm;
use Dcat\Admin\Widgets\Modal;

class ImportYouzanShops extends AbstractTool
{
    /**
     * @return string
     */
	protected $title = '导入店铺';

    public function html()
   {
       $form = ImportYouzanShopsForm::make();

       $button = <<<HTML
       <button class="btn btn-primary btn-outline">{$this->title}</button>
HTML;
       $body = <<<HTML
       <div style="padding-left: 10px;" class="row form-field">
         <div class="col-md-2" style="text-align: right;padding: 0 5px 0 18px;">下载模板</div>
         <div class="col-md-8"> <i class="feather icon-download-cloud" style="/* padding-left: 14px; */color: #4c60a3;">
         <a href="/files/有赞店铺上传模板.xlsx" target="_blank">批量导入店铺模板</a></i></div>
       </div>
       $form
HTML;
       return Modal::make()->lg()
            ->title($this->title)
            ->body($body)
            ->button($button)->render();
   }
}
