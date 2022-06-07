<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Export;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Storage;
use Dcat\Admin\Admin;

class ExportController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Export(['adminuser']), function (Grid $grid) {
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->disableFilterButton();
            $grid->disableActions();

            if (!Admin::user()->isRole('administrator')) {
                $grid->model()->whereNull('failed_at');
            }

            $grid->model()->orderBy('start_at', 'desc');

            $grid->column('name');
            $grid->column('adminuser.name', '申请人');
            // $grid->column('exported_fields')->label()->width('10%');
            if (Admin::user()->isRole('administrator')) {
                $grid->fixColumns(1);
                $grid->column('uuid');
                $grid->column('batch_id');
                $grid->column('exception');
                $grid->column('exception_msg');
                $grid->column('failed_at');
                $grid->column('succeed_at');
            }
            //
            $grid->column('start_at');
            $grid->column('finished_at');
            $grid->column('status', '操作')->display(function(){
                if (!$this->finished_at) {
                    // 未完成
                    return "<a target=\"_blank\" class=\"btn btn-warning text-white\">生成中</a>";
                } elseif (!$this->failed_at) {
                    // 成功
                    $url = Storage::disk('excel')->url($this->path);
                    return "<a href=\"{$url}\" target=\"_blank\" class=\"btn btn-primary text-white\">下载</a>";
                } else {
                    // 失败
                    return "<button class=\"btn btn-danger text-white\">导出失败</button>";
                }
            });

        });
    }
}
