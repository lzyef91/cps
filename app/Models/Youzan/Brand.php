<?php

namespace App\Models\Youzan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $table = 'youzan_shop_brands';
    protected $guarded = [];
}
