<?php

namespace App\Admin\Repositories;

use App\Models\Youzan\Shop as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class YouzanShop extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
