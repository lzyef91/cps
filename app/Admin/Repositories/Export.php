<?php

namespace App\Admin\Repositories;

use App\Models\Export as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Export extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
