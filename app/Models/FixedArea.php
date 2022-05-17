<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedArea extends Model
{
    use HasFactory;
    protected $table = "china_fixed_areas";
    protected $guarded = [];
    public $timestamps = false;
}
