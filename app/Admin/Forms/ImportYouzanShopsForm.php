<?php

namespace App\Admin\Forms;

use App\Admin\Repositories\YouzanShop;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Storage;
use App\Imports\YouzanShopImport;

class ImportYouzanShopsForm extends Form
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
        $table = Storage::disk('admin')->path($input['upload_file']);
        (new YouzanShopImport())->import($table);

        return $this
				->response()
				->success('上传成功')
				->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->file('upload_file')
            ->uniqueName()
            ->autoUpload()
            ->accept('xlsx')
            ->autoSave(false)
            ->maxSize(2048)
            ->required();
    }
}
