<?php

namespace App\Models\Youzan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Youzan\Category;
use App\Models\Youzan\Brand;
use App\Models\Youzan\Enterprise;
use App\Models\Youzan\Contact;
use App\Models\Area;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'youzan_shops';

    protected $guarded = [];

    protected $casts = [
        'other_shops' => 'array'
    ];

    public static $PrincipalType = [
        1 => '个体',
        2 => '企业',
        3 => '个体工商户',
        4 => '其他组织',
        5 => '政府及事业单位'
    ];

    public static $TOKEN_CACHE_KEY = 'qike_jwt_token';

    public function categories()
    {
        return $this->hasMany(Category::class, 'shop_id');
    }

    public function brands()
    {
        return $this->hasMany(Brand::class, 'shop_id');
    }

    public function enterprise()
    {
        return $this->hasOne(Enterprise::class, 'shop_id');
    }

    public function area()
    {
        return $this->hasOneThrough(Area::class, Enterprise::class ,'shop_id', 'code', 'id', 'region_code');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'shop_id');
    }

}
