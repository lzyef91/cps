<?php

namespace App\Exports;

use App\Models\Youzan\Contact;
use App\Models\Youzan\Shop;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Queue\ShouldQueue;
use \Throwable;
use App\Jobs\NotifyExportResult;

class YouzanShopExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    use Exportable;

    protected $queryParams;

    public function __construct(array $queryParams)
    {
        $this->queryParams = $queryParams;
    }

    public function failed(Throwable $exception): void
    {
        // handle failed export
        $uuid = $this->queryParams['export_uuid'];
        NotifyExportResult::dispatch($uuid, false, get_class($exception), $exception->getMessage());
    }

    public function headings(): array
    {
        $res = [];
        foreach ($this->queryParams['export_fields'] as $f) {
            switch ((int)$f) {
                case 1:
                    $res[] = '主体名称';
                    break;
                case 2:
                    $res[] = '联系人(职位)';
                    $res[] = '手机';
                    break;
                case 3:
                    $res[] = '主营类目';
                    break;
                case 4:
                    $res[] = '副营类目';
                    break;
                case 5:
                    $res[] = '所在地区';
                    break;
                case 6:
                    $res[] = '公司规模';
                    break;
                case 7:
                    $res[] = '公司类型';
                    break;
                case 8:
                    $res[] = '公司成立时间';
                    break;
                case 9:
                    $res[] = '统一社会信用代码';
                    break;
                case 10:
                    $res[] = '旗下品牌';
                    break;
                case 11:
                    $res[] = '店铺名称';
                    break;
                case 12:
                    $res[] = '店铺地址';
                    break;
                case 13:
                    $res[] = '店铺公众号';
                    break;
            }
        }

        return $res;
    }

    public function map($shop): array
    {
        $data = $this->buildData($shop);
        return $this->buildRow($data);
    }


    public function query()
    {
        return $this->buildQuery();
    }

    protected function buildQuery()
    {
        $query = Shop::with([
            'enterprise:shop_id,region,size,enterprise_type,established_at,enterprise_uniscid',
            'categories:shop_id,major,category_name',
            'brands:shop_id,brand_name,brand_name_en',
            'contacts:shop_id,contact_type,contact_no,name,duty'
        ]);

        if (!$this->queryParams) return $query;

        // 主体名称 string
        $principalName = $this->queryParams['principal_name'] ?? NULL ?: NULL;
        if ($principalName) {
            $query->where('principal_name', 'like', "%{$principalName}%");
        }

        // 主体类型 array
        $principalType = $this->queryParams['principal_type'] ?? NULL ?: NULL;
        if ($principalType) {
            $query->whereIn('principal_type', $principalType);
        }

        // 主营类目 array
        $primaryCategory = $this->queryParams['primary_category'] ?? NULL ?: NULL;
        if ($primaryCategory) {
            $query->whereHas('categories', function ($query)use($primaryCategory) {
                $query->where('major', 1);
                $query->where(function($query)use($primaryCategory){
                    foreach ($primaryCategory as $k => $c) {
                        if ($k == 0) $query->where('category_name', $c);
                        else $query->orWhere('category_name', $c);
                    }
                });
            });
        }
        // 副营类目 array
        $secondaryCategory = $this->queryParams['secondary_category'] ?? NULL ?: NULL;
        if ($secondaryCategory) {
            $query->whereHas('categories', function ($query)use($secondaryCategory) {
                $query->where('major', 0);
                $query->where(function($query)use($secondaryCategory){
                    foreach ($secondaryCategory as $k => $c) {
                        if ($k == 0) $query->where('category_name', $c);
                        else $query->orWhere('category_name', $c);
                    }
                });
            });
        }
        // 品牌 array
        $ownBrand = $this->queryParams['own_brand'] ?? NULL ?: NULL;
        if ($ownBrand) {
            $query->whereHas('brands', function($query)use($ownBrand){
                $query->where('brand_name', 'like', "%{$ownBrand}%")
                    ->orWhere('brand_name_en', 'like', "%{$ownBrand}%");
            });
        }
        // 企业信息 array
        $enterprise = $this->queryParams['enterprise'] ?? NULL ?: NULL;
        if ($enterprise) {
            $query->whereHas('enterprise',function($query)use($enterprise){
                if (isset($enterprise['region_province'])) $query->where('region_province', 'like', "%{$enterprise['region_province']}%");
                if (isset($enterprise['region_city'])) $query->where('region_city', 'like', "%{$enterprise['region_city']}%");
                if (isset($enterprise['region_district'])) $query->where('region_district', 'like', "%{$enterprise['region_district']}%");
            });
        }
        // 店铺名称
        $name = $this->queryParams['name'] ?? NULL ?: NULL;
        if ($name) {
            $query->where('name', 'like', "%{$name}%");
        }
        // 开店时间
        $openAt = $this->queryParams['open_at'] ?? NULL ?: NULL;
        if ($openAt) {
            $query->where(function($query)use($openAt){
                if (isset($openAt['start'])) $query->where('open_at', '>=', $openAt['start']);
                if (isset($openAt['end'])) $query->where('open_at', '<', $openAt['end']);
            });
        }
        // 属性筛选器
        $selector = $this->queryParams['_selector'] ?? NULL ?: NULL;
        if ($selector) {
            // 主体类型
            if (isset($selector['principal_type'])) $query->where('principal_type', $selector['principal_type']);
            // 类目名称
            if (isset($selector['categories_category_name'])) $query->whereRelation('categories', 'category_name', $selector['categories_category_name']);
            // 所在城市
            if (isset($selector['enterprise_region_city_code'])) $query->whereRelation('enterprise', 'region_city_code', $selector['enterprise_region_city_code']);
        }

        return $query;
    }

    protected function buildData($shop)
    {
        $res = [];

        foreach ($this->queryParams['export_fields'] as $f) {
            switch ((int)$f) {
                case 1:
                    // 主体名称
                    $res['principal_name'] = $shop->principal_name;
                    break;
                case 2:
                    // 手机线索
                    $contacts = $shop->contacts->where('contact_type', Contact::$TYPE_MOBILE)->all();
                    $res['contacts'] = $contacts;
                    break;
                case 3:
                    // 主营类目
                    if ($cate = $shop->categories->firstWhere('major', 1)) $res['category_name1'] = $cate->category_name;
                    else $res['category_name1'] = '';
                    break;
                case 4:
                    // 副营类目
                    $cates = $shop->categories->where('major', 0)->all();
                    $cate = [];
                    foreach ($cates as $c) {
                        $cate[] = $c->category_name;
                    }
                    $res['category_name2'] = implode("|", $cate);
                    break;
                case 5:
                    // 所在地区
                    if ($ent = $shop->enterprise) $res['region'] = $ent->region;
                    else $res['region'] = '';
                    break;
                case 6:
                    // 公司规模
                    if ($ent = $shop->enterprise) $res['size'] = $ent->size;
                    else $res['size'] = '';
                    break;
                case 7:
                    // 公司类型
                    if ($ent = $shop->enterprise) $res['enterprise_type'] = $ent->enterprise_type;
                    else $res['enterprise_type'] = '';
                    break;
                case 8:
                    // 公司成立时间
                    if ($ent = $shop->enterprise) $res['established_at'] = $ent->established_at;
                    else $res['established_at'] = '';
                    break;
                case 9:
                    // 统一社会信用代码
                    if ($ent = $shop->enterprise) $res['enterprise_uniscid'] = $ent->enterprise_uniscid;
                    else $res['enterprise_uniscid'] = '';
                    break;
                case 10:
                    // 旗下品牌
                    $brands = [];
                    foreach ($shop->brands as $brand) {
                        if (empty($brand->brand_name)) {
                            $brands[] = $brand->brand_name_en;
                        } elseif (empty($brand->brand_name_en)) {
                            $brands[] = $brand->brand_name;
                        } else {
                            $brands[] = $brand->brand_name.'('.$brand->brand_name_en.')';
                        }
                    }
                    $res['brands'] = implode('|',$brands);
                    break;
                case 11:
                    // 店铺名称
                    $res['name'] = $shop->name;
                    break;
                case 12:
                    // 店铺地址
                    $res['address'] = $shop->address;
                    break;
                case 13:
                    // 店铺公众号
                    $res['mp_qrcode'] = $shop->mp_qrcode;
                    break;
            }
        }

        return $res;
    }

    protected function buildRow($data)
    {
        $orders = ['principal_name','contacts','category_name1','category_name2','region',
                   'size','enterprise_type','established_at','enterprise_uniscid','brands',
                   'name','address','mp_qrcode'];
        // 导出联系方式，且联系方式存在
        if (isset($data['contacts']) && !empty($data['contacts'])) {
            // 多行数据
            $rows = [];
            foreach ($data['contacts'] as $c) {
                // 按次序重整数据
                $row = [];
                foreach ($orders as $o) {
                    if ($o == 'contacts') {
                        // 联系人
                        $name = $c->name ?? '';
                        // 职位
                        $duty = $c->duty ?? '';
                        if (!empty($duty)) $name .= '('.$duty.')';
                        // 构建行数据
                        $row[] = $name;
                        $row[] = $c->contact_no;
                    } elseif (isset($data[$o])) {
                        // 其他数据
                        $row[] = $data[$o];
                    }
                }
                // 构建多行数据
                $rows[] = $row;
            }
            // 返回多行数据
            return $rows;
        }

        // 联系人不存在时构建单行数据
        $row = [];
        foreach ($orders as $o) {
            if (isset($data[$o])) {
                if ($o == 'contacts') {
                    // 联系人
                    $row[] = '';
                    // 手机
                    $row[] = '';
                }
                else $row[] = $data[$o];
            }
        }

        return $row;
    }
}
