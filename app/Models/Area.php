<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Youzan\Shop;
use App\Models\Youzan\Enterprise;
use App\Models\FixedArea;

class Area extends Model
{
    use HasFactory;
    protected $table = 'china_areas';
    protected $guarded = [];
    public $timestamps = false;

    /**
     * 查找没有映射到地域表的企业数据
     */
    public static function fix()
    {
        $res = Enterprise::select('region_code','region')
            ->whereNotNull('region_code')->whereNull('region_province')
            ->groupBy('region_code','region')->orderBy('region_code')
            ->pluck('region','region_code');
        $data = [];
        foreach ($res as $c => $r) {
            $area = Area::where('region','like', "%$r%")->first();
            $code = isset($area) ? $area->code : NULL;
            $data[$c] = ['fixedCode'=>$code,'region'=>$r];
        }
        return ["count"=>count($data),"data"=>$data];
    }

    /**
     * 填充企业表中的地域数据
     */
    public static function entSeed()
    {
        Enterprise::whereNotNull('region_code')->chunkById(100, function($ents){
            foreach ($ents as $ent) {
                // 查找企业地域信息
                $area = Area::where('code', $ent->region_code)->first();
                if (!isset($area)) {
                    $fix = FixedArea::where('code', $ent->region_code)->first();
                    if (isset($fix)) {
                        $area = Area::where('code', $fix->fixed_code)->first();
                    }
                }

                if (!isset($area)) continue;

                $ent->region_code = $area->code;
                $ent->region = $area->region;
                $ent->region_province_code = $area->province_code;
                $ent->region_province = $area->province;
                $ent->region_city_code = $area->city_code;
                $ent->region_city = $area->city;
                $ent->region_district_code = $area->district_code;
                $ent->region_district = $area->district;

                $ent->save();
            }
        });
    }
}
