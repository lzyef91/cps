<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Dcat\Admin\Models\Administrator;

class Export extends Model
{
    use HasFactory;

    protected $table = 'exports';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'exported_fields' => 'array'
    ];

    public function adminuser()
    {
        return $this->belongsTo(Administrator::class, 'admin_user_id');
    }
}
