<?php

namespace App\Models\Youzan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enterprise extends Model
{
    use HasFactory;

    protected $table = 'youzan_enterprises';

    protected $guarded = [];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
}
